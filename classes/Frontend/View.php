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
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Frontend\Path;
use MAKS\Velox\Frontend\View\Compiler;
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
 * @package Velox\Frontend\View
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
     * Can also be used as a mean of extending a layout if it was put at the end of it because the compilation is done
     * from top to bottom and from the deepest nested element to the upper most (imperative approach, there is no preprocessing).
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

        Compiler::require($include, $variables);
    }

    /**
     * Renders a theme layout with the passed variables.
     *
     * @param string $name The name of the layout.
     * @param array $variables [optional] An associative array of the variables to pass.
     *
     * @return string
     */
    public static function layout(string $name, array $variables = []): string
    {
        $path = Config::get('theme.paths.layouts');

        $variables['defaultLayoutVars'] = Config::get('view.defaultLayoutVars');

        $layout = self::resolvePath($path, $name);

        return Compiler::compile($layout, __FUNCTION__, $variables);
    }

    /**
     * Renders a theme page with the passed variables.
     *
     * @param string $name The name of the page.
     * @param array $variables [optional] An associative array of the variables to pass.
     *
     * @return string
     */
    public static function page(string $name, array $variables = []): string
    {
        $path = Config::get('theme.paths.pages');

        $variables['defaultPageVars'] = Config::get('view.defaultPageVars');

        $page = self::resolvePath($path, $name);

        return Compiler::compile($page, __FUNCTION__, $variables);
    }

    /**
     * Renders a theme partial with the passed variables.
     *
     * @param string $name The name of the partial.
     * @param array $variables [optional] An associative array of the variables to pass.
     *
     * @return string
     */
    public static function partial(string $name, array $variables = []): string
    {
        $path = Config::get('theme.paths.partials');

        $variables['defaultPartialVars'] = Config::get('view.defaultPartialVars');

        $partial = self::resolvePath($path, $name);

        return Compiler::compile($partial, __FUNCTION__, $variables);
    }

    /**
     * Renders a view (a Page wrapped in a Layout) with the passed variables, the Page content will be sent to `{view.defaultSectionName}` section.
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
