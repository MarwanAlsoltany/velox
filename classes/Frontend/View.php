<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Frontend;

use MAKS\Velox\App;
use MAKS\Velox\Backend\Event;
use MAKS\Velox\Backend\Config;
use MAKS\Velox\Frontend\Engine;
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Frontend\Path;
use MAKS\Velox\Helper\Misc;

/**
 * A class that renders view files (Layouts, Pages, and Partials) with the ability to include additional files and divide page content into sections and cache rendered views.
 *
 * Example:
 * ```
 * // render a view
 * $html = View::render('somePage', ['someVar' => 'someValue'], 'someLayout');
 *
 * // render a view, cache it and get it from cache on subsequent calls
 * $html = View::cache('somePage', ['someVar' => 'someValue'], 'someLayout');
 *
 * // delete cached views
 * View::clearCache();
 *
 * // set section value
 * View::section('name', $content);
 *
 * // start capturing section content
 * View::sectionStart('name');
 *
 * // end capturing section content
 * View::sectionEnd();
 *
 * // reset (empty) section content
 * View::sectionReset('name');
 *
 * // get section content
 * View::yield('name', 'fallback');
 *
 * // include a file
 * View::include('path/to/a/file');
 *
 * // render a layout from theme layouts
 * $html = View::layout('layoutName', $vars);
 *
 * // render a page from theme pages
 * $html = View::page('pageName', $vars);
 *
 * // render a partial from theme partials
 * $html = View::partial('partialName', $vars);
 * ```
 *
 * @since 1.0.0
 * @api
 */
class View
{
    /**
     * This event will be dispatched before rendering a view.
     * This event will be passed a reference to the array that will be passed to the view as variables.
     *
     * @var string
     */
    public const BEFORE_RENDER = 'view.before.render';

    /**
     * This event will be dispatched when a view is cached.
     * This event will not be passed any arguments.
     *
     * @var string
     */
    public const ON_CACHE = 'view.on.cache';

    /**
     * This event will be dispatched when views cache is cleared.
     * This event will not be passed any arguments.
     *
     * @var string
     */
    public const ON_CACHE_CLEAR = 'view.on.cacheClear';


    /**
     * The default values of class parameters.
     *
     * @var array
     */
    public const DEFAULTS = [
        'name' => '__default__',
        'variables' => [],
        'fileExtension' => '.phtml',
        'inherit' => true,
        'minify' => true,
        'cache' => false,
        'cacheExclude' => ['__default__'],
        'cacheAsIndex' => false,
        'cacheWithTimestamp' => true,
        'engine' => [
            'enabled' => true,
            'cache'   => true,
            'debug'   => false,
        ]
    ];


    /**
     * Sections buffer.
     */
    protected static array $sections = [];

    /**
     * Sections stack.
     */
    protected static array $stack = [];


    /**
     * Pushes content to the buffer of the section with the given name.
     * Note that a section will not be rendered unless it's yielded.
     *
     * @param string $name The name of the section.
     * @param string $content The content of the section.
     *
     * @return void
     */
    public static function section(string $name, string $content): void
    {
        if (!isset(static::$sections[$name])) {
            static::$sections[$name] = [];
        }

        static::$sections[$name][] = $content;
    }

    /**
     * Resets (empties) the buffer of the section with the given name.
     *
     * @param string $name The name of the section.
     *
     * @return void
     */
    public static function sectionReset(string $name): void
    {
        unset(static::$sections[$name]);
    }

    /**
     * Starts capturing buffer of the section with the given name. Works in conjunction with `self::sectionEnd()`.
     * Note that a section will not be rendered unless it's yielded.
     *
     * @param string $name The name of the section.
     *
     * @return void
     */
    public static function sectionStart(string $name): void
    {
        if (!isset(static::$sections[$name])) {
            static::$sections[$name] = [];
        }

        array_push(static::$stack, $name);

        ob_start();
    }

    /**
     * Ends capturing buffer of the section with the given name. Works in conjunction with `self::sectionStart()`.
     * Note that a section will not be rendered unless it's yielded.
     *
     * @return void
     *
     * @throws \Exception If no section has been started.
     */
    public static function sectionEnd(): void
    {
        if (!count(static::$stack) || !ob_get_level()) {
            $variables = ['class', 'function', 'file', 'line'];
            $backtrace = Misc::backtrace($variables, 1);
            $backtrace = is_array($backtrace) ? $backtrace : array_map('strtoupper', $variables);

            throw new \Exception(
                vsprintf('Not in a context to end a section! Call to %s::%s() in %s on line %s is superfluous', $backtrace)
            );
        }

        $buffer = ob_get_clean();

        $name = array_pop(static::$stack);

        static::$sections[$name][] = $buffer ?: '';
    }

    /**
     * Returns content of the section with the given name.
     *
     * @param string $name The name of the section.
     * @param string $default [optional] The default value to yield if the section has no content or is an empty string.
     *
     * @return string
     */
    public static function yield(string $name, string $default = ''): string
    {
        $section = '';

        if (isset(static::$sections[$name])) {
            foreach (static::$sections[$name] as $buffer) {
                // buffers are added in reverse order
                $section = $buffer . $section;
            }

            static::sectionReset($name);
        }

        return strlen(trim($section)) ? $section : $default;
    }

    /**
     * Includes a file from the active theme directory.
     * Can also be used as a mean of extending a layout if it was put at the end of it.
     *
     * @param string $file The path of the file starting from theme root.
     * @param array|null $variables [optional] An associative array of the variables to pass.
     *
     * @return void
     */
    public static function include(string $file, ?array $variables = null): void
    {
        $path = Config::get('theme.paths.root');

        $include = self::resolvePath($path, $file);

        self::require($include, $variables);
    }

    /**
     * Renders a theme layout with the passed variables.
     *
     * @param string $name The name of the layout.
     * @param array $variables An associative array of the variables to pass.
     *
     * @return string
     */
    public static function layout(string $name, array $variables = []): string
    {
        $path = Config::get('theme.paths.layouts');

        $variables['defaultLayoutVars'] = Config::get('view.defaultLayoutVars');

        $layout = self::resolvePath($path, $name);

        return static::compile($layout, __FUNCTION__, $variables);
    }

    /**
     * Renders a theme page with the passed variables.
     *
     * @param string $name The name of the page.
     * @param array $variables An associative array of the variables to pass.
     *
     * @return string
     */
    public static function page(string $name, array $variables = []): string
    {
        $path = Config::get('theme.paths.pages');

        $variables['defaultPageVars'] = Config::get('view.defaultPageVars');

        $page = self::resolvePath($path, $name);

        return static::compile($page, __FUNCTION__, $variables);
    }

    /**
     * Renders a theme partial with the passed variables.
     *
     * @param string $name The name of the partial.
     * @param array $variables An associative array of the variables to pass.
     *
     * @return string
     */
    public static function partial(string $name, array $variables = []): string
    {
        $path = Config::get('theme.paths.partials');

        $variables['defaultPartialVars'] = Config::get('view.defaultPartialVars');

        $partial = self::resolvePath($path, $name);

        return static::compile($partial, __FUNCTION__, $variables);
    }

    /**
     * Renders a view (a Page wrapped in a Layout) with the passed variables, the Page content will be sent to "{view.defaultSectionName}" section.
     *
     * @param string $page The name of the page.
     * @param array $variables [optional] An associative array of the variables to pass.
     * @param string|null $layout [optional] The name of the Layout to use.
     *
     * @return string
     */
    public static function render(string $page, array $variables = [], ?string $layout = null): string
    {
        $viewConfig = Config::get('view');
        $layout     = $layout ?? $viewConfig['defaultLayoutName'];
        $section    = $viewConfig['defaultSectionName'];
        $minify     = $viewConfig['minify'];
        $cache      = $viewConfig['cache'];

        if ($cache) {
            return static::cache($page, $variables, $layout);
        }

        Event::dispatch(self::BEFORE_RENDER, [&$variables]);

        static::section($section, static::page($page, $variables));

        $view = static::layout($layout, $variables);

        return $minify ? HTML::minify($view) : $view;
    }

    /**
     * Renders a view with the passed variables and cache it as HTML, subsequent calls to this function will return the cached version.
     * This function is exactly like `self::render()` but with caching capabilities.
     *
     * @param string $page The name of the page.
     * @param array $variables [optional] An associative array of the variables to pass.
     * @param string|null $layout [optional] The name of the Layout to use.
     *
     * @return string
     */
    public static function cache(string $page, array $variables = [], ?string $layout = null)
    {
        $viewConfig         = Config::get('view');
        $cacheEnabled       = $viewConfig['cache'];
        $cacheExclude       = $viewConfig['cacheExclude'];
        $cacheAsIndex       = $viewConfig['cacheAsIndex'];
        $cacheWithTimestamp = $viewConfig['cacheWithTimestamp'];
        $cacheDir           = Config::get('global.paths.storage') . '/cache/views/';

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0744, true);
        }

        $cacheFile          = static::resolveCachePath($page);
        $cacheFileDirectory = dirname($cacheFile);
        $fileExists         = file_exists($cacheFile);

        $content = null;

        if (!$cacheEnabled || !$fileExists) {
            Config::set('view.cache', false);
            $view = static::render($page, $variables, $layout);
            Config::set('view.cache', true);

            if (in_array($page, (array)$cacheExclude)) {
                return $view;
            }

            if ($cacheAsIndex) {
                if (!file_exists($cacheFileDirectory)) {
                    mkdir($cacheFileDirectory, 0744, true);
                }
            } else {
                $cacheFile = preg_replace('/\/+|\\+/', '___', $page);
                $cacheFile = static::resolvePath($cacheDir, $cacheFile, '.html');
            }

            $comment = '';
            if ($cacheWithTimestamp) {
                $timestamp = date('l jS \of F Y h:i:s A (Ymdhis)');
                $comment   = sprintf('<!-- [CACHE] Generated on %s -->', $timestamp);
            }

            $content = preg_replace(
                '/<!DOCTYPE html>/i',
                '$0' . $comment,
                $view
            );

            file_put_contents($cacheFile, $content, LOCK_EX);

            Event::dispatch(self::ON_CACHE);

            App::log('Generated cache for the "{page}" page', ['page' => $page], 'system');
        }

        $content = $content ?? file_get_contents($cacheFile);

        return $content;
    }

    /**
     * Deletes all cached views generated by `self::cache()`.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        $clear = static function ($path) use (&$clear) {
            static $base = null;
            if (!$base) {
                $base = $path;
            }

            $items = glob($path . '/*');
            foreach ($items as $item) {
                is_dir($item) ? $clear($item) : unlink($item);
            }

            if ($path !== $base) {
                file_exists($path) && rmdir($path);
            }
        };

        $clear(Config::get('global.paths.storage') . '/cache/views/');
        $clear(Config::get('global.paths.storage') . '/temp/views/');

        Event::dispatch(self::ON_CACHE_CLEAR);

        App::log('Cleared views cache', null, 'system');
    }

    /**
     * Compiles a PHP file with the passed variables.
     *
     * @param string $file An absolute path to the file that should be compiled.
     * @param string $type The type of the file (just a name to make for friendly exceptions).
     * @param array|null [optional] An associative array of the variables to pass.
     *
     * @return string
     *
     * @throws \Exception If failed to compile the file.
     */
    protected static function compile(string $file, string $type, ?array $variables = null): string
    {
        ob_start();

        try {
            self::require($file, $variables);
        } catch (\Exception $error) {
            // clean started buffer before throwing the exception
            ob_end_clean();

            throw $error;
        }

        $buffer = ob_get_contents();
        ob_end_clean();

        if ($buffer === false) {
            $name = basename($file, Config::get('view.fileExtension'));
            throw new \Exception("Something went wrong when trying to compile the {$type} with the name '{$name}' in {$file}");
        }

        return trim($buffer);
    }

    /**
     * Requires a PHP file and pass it the passed variables.
     *
     * @param string $file An absolute path to the file that should be compiled.
     * @param array|null $variables [optional] An associative array of the variables to pass.
     *
     * @return void
     *
     * @throws \Exception If the file could not be loaded.
     */
    private static function require(string $file, ?array $variables = null): void
    {
        $file = self::findOrInherit($file);

        if (!file_exists($file)) {
            throw new \Exception(
                "Could not load the file with the path '{$file}' nor fall back to a parent. Check if the file exists!"
            );
        }

        $_file = static::parse($file);
        unset($file);

        if ($variables !== null) {
            extract($variables, EXTR_OVERWRITE);
            unset($variables);
        }

        require($_file);
        unset($_file);
    }

    /**
     * Parses a file through the templating engine and returns a path to the compiled file.
     *
     * @param string $file The file to parse.
     *
     * @return string
     */
    private static function parse(string $file): string
    {
        if (!Config::get('view.engine.enabled', true)) {
            return $file;
        }

        static $engine = null;

        if ($engine === null) {
            $engine = new Engine(
                Config::get('global.paths.themes') . '/',
                Config::get('view.fileExtension') ?? self::DEFAULTS['fileExtension'],
                Config::get('global.paths.storage') . '/temp/views/',
                Config::get('view.engine.cache') ?? self::DEFAULTS['engine']['cache'],
                Config::get('view.engine.debug') ?? self::DEFAULTS['engine']['debug']
            );
        }

        $file = $engine->getCompiledFile(strtr($file, [
            Path::normalize(Config::get('global.paths.themes'), '') => ''
        ]));

        return $file;
    }

    /**
     * Finds a file in the active theme or inherit it from parent theme.
     *
     * @param string $file
     *
     * @return string
     */
    private static function findOrInherit(string $file): string
    {
        if (file_exists($file)) {
            return $file;
        }

        if (Config::get('view.inherit')) {
            $active = Config::get('theme.active');
            $parent = Config::get('theme.parent');
            $themes = Config::get('global.paths.themes');
            $nameWrapper = basename($themes) . DIRECTORY_SEPARATOR . '%s';

            foreach ((array)$parent as $substitute) {
                $fallbackFile = strtr($file, [
                    sprintf($nameWrapper, $active) => sprintf($nameWrapper, $substitute)
                ]);

                if (file_exists($fallbackFile)) {
                    $file = $fallbackFile;
                    break;
                }
            }
        }

        return $file;
    }

    /**
     * Returns a normalized path of a page from the cache directory.
     *
     * @param string $pageName
     *
     * @return string
     */
    private static function resolveCachePath(string $pageName): string
    {
        static $cacheDir = null;

        if ($cacheDir === null) {
            $cacheDir = Config::get('global.paths.storage') . '/cache/views/';
        }

        $cacheName = sprintf(
            '%s/%s',
            $pageName,
            'index'
        );

        return static::resolvePath($cacheDir, $cacheName, '.html');
    }

    /**
     * Returns a normalized path to a file based on OS.
     *
     * @param string $directory
     * @param string $filename
     * @param string|null $extension
     *
     * @return string
     */
    private static function resolvePath(string $directory, string $filename, ?string $extension = null): string
    {
        $extension = $extension ?? Config::get('view.fileExtension') ?? self::DEFAULTS['fileExtension'];

        return Path::normalize(
            $directory,
            $filename,
            $extension
        );
    }
}
