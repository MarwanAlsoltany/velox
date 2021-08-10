<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);



/**
 * Returns an instance of the `App` class.
 *
 * @return \MAKS\Velox\App
 */
function app() {
    static $app = null;

    if (!$app) {
        $app = new \MAKS\Velox\App();
    }

    return $app;
}


if (!function_exists('config')) {
    /**
     * Gets or sets a value of a key from the configuration via dot-notation (0 param -> the Config class instance, 1 param -> get by key, 2 params -> set by key).
     *
     * @param string $key [optional] The dotted key representation.
     * @param mixed $value [optional] The value to set.
     *
     * @return mixed The requested value or null.
     */
    function config() {
        if (func_num_args() == 0) {
            return app()->config;
        }

        if (func_num_args() == 1) {
            return app()->config()->get(...func_get_args());
        }

        return app()->config()->set(...func_get_args());
    }
}



if (!function_exists('event')) {
    /**
     * Returns an instance of the `Event` class.
     *
     * @return \MAKS\Velox\Backend\Router
     */
    function event() {
        return app()->event;
    }
}



if (!function_exists('router')) {
    /**
     * Returns an instance of the `Router` class.
     *
     * @return \MAKS\Velox\Backend\Router
     */
    function router() {
        return app()->router;
    }
}

if (!function_exists('handle')) {
    /**
     * Registers a handler for a route.
     *
     * @param string $expression A route like `/page`, `/page/{id}` (`id` is required), or `/page/{id?}` (`id` is optional). For more flexibility, pass en expression like `/page/([\d]+|[0-9]*)` (regex capture group).
     * @param callable $handler A function to call if route has matched. It will be passed the current `$path`, the `$match` or `...$match` from the expression if there was any, and lastly the `$previous` result (the return of the last middleware or route with a matching expression) if `$allowMultiMatch` is set to `true`.
     * @param string|string[] $method [optional] Either a string or an array of the allowed method.
     *
     * @return static
     */
    function handle() {
        return app()->router->handle(...func_get_args());
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirects the request to another route.
     * Note that this function will exit the script (code that comes after it will not be executed).
     *
     * @param string $to A route like `/page` or a URL like `http://domain.tld`.
     *
     * @return void
     */
    function redirect() {
        return app()->router->redirect(...func_get_args());
    }
}

if (!function_exists('forward')) {
    /**
     * Forwards the request to another route.
     * Note that this function will exit the script (code that comes after it will not be executed).
     *
     * @param string $to A route like `/page`.
     *
     * @return void
     */
    function forward() {
        return app()->router->forward(...func_get_args());
    }
}



if (!function_exists('globals')) {
    /**
     * Returns an instance of the `Globals` class.
     *
     * @return \MAKS\Velox\Backend\Globals
     */
    function globals() {
        return app()->globals;
    }
}



if (!function_exists('view')) {
    /**
     * Returns an instance of the `View` class.
     *
     * @return \MAKS\Velox\Frontend\View
     */
    function view() {
        return app()->view;
    }
}

if (!function_exists('render')) {
    /**
     * Renders a view (a Page wrapped in a Layout) with the passed variables, the Page content will be sent to "{view.defaultSectionName}" section.
     *
     * @param string $page The name of the page.
     * @param array|null $variables [optional] An associative array of the variables to pass.
     * @param string|null $layout [optional] The name of the Layout to use.
     *
     * @return string
     */
    function render() {
        return app()->view->render(...func_get_args());
    }
}

if (!function_exists('render_layout')) {
    /**
     * Renders a theme layout with the passed variables.
     *
     * @param string $name The name of the layout.
     * @param array|null $variables [optional] An associative array of the variables to pass.
     *
     * @return string
     */
    function render_layout() {
        return app()->view->layout(...func_get_args());
    }
}

if (!function_exists('render_page')) {
    /**
     * Renders a theme page with the passed variables.
     *
     * @param string $name The name of the page.
     * @param array|null $variables [optional] An associative array of the variables to pass.
     *
     * @return string
     */
    function render_page() {
        return app()->view->page(...func_get_args());
    }
}

if (!function_exists('render_partial')) {
    /**
     * Renders a theme partial with the passed variables.
     *
     * @param string $name The name of the partial.
     * @param array|null $variables [optional] An associative array of the variables to pass.
     *
     * @return string
     */
    function render_partial() {
        return app()->view->partial(...func_get_args());
    }
}

if (!function_exists('section_push')) {
    /**
     * Pushes content to the buffer of the section with the given name.
     * Note that a section will not be rendered unless it's yielded.
     *
     * @param string $name The name of the section.
     * @param string $content The content of the section.
     *
     * @return void
     */
    function section_push() {
        return app()->view->section(...func_get_args());
    }
}

if (!function_exists('section_reset')) {
    /**
     * Resets (empties) the buffer of the section with the given name.
     *
     * @param string|null $name The name of the section.
     *
     * @return void
     */
    function section_reset() {
        return app()->view->sectionReset(...func_get_args());
    }
}

if (!function_exists('section_start')) {
    /**
     * Starts capturing buffer of the section with the given name. Works in conjunction with `self::sectionEnd()`.
     * Note that a section will not be rendered unless it's yielded.
     *
     * @param string $name The name of the section.
     *
     * @return void
     */
    function section_start() {
        return app()->view->sectionStart(...func_get_args());
    }
}

if (!function_exists('section_end')) {
    /**
     * Ends capturing buffer of the section with the given name. Works in conjunction with `self::sectionStart()`.
     * Note that a section will not be rendered unless it's yielded.
     *
     * @return void
     *
     * @throws \Exception If no section has been started.
     */
    function section_end() {
        return View::sectionEnd();
    }
}

if (!function_exists('section_yield')) {
    /**
     * Returns content of the section with the given name.
     *
     * @param string $name The name of the section.
     * @param string $default [optional] The default value to yield if the section has no content or is an empty string.
     *
     * @return string
     */
    function section_yield() {
        return app()->view->yield(...func_get_args());
    }
}

if (!function_exists('include_file')) {
    /**
     * Includes a file from the active theme directory.
     * Can also be used as a mean of extending a layout if it was put at the end of it.
     *
     * @param string $file The path of the file starting from theme root.
     *
     * @return void
     */
    function include_file() {
        return app()->view->include(...func_get_args());
    }
}



if (!function_exists('data')) {
    /**
     * Returns an instance of the `Data` class.
     *
     * @return \MAKS\Velox\Frontend\Data
     */
    function data() {
        return app()->data;
    }
}

if (!function_exists('data_has')) {
    /**
     * Checks whether a value of a key exists in `self::$bag` via dot-notation.
     *
     * @param string $key The dotted key representation.
     *
     * @return bool
     */
    function data_has() {
        return app()->data->has(...func_get_args());
    }
}

if (!function_exists('data_get')) {
    /**
     * Gets a value of a key from `self::$bag` via dot-notation.
     *
     * @param string $key The dotted key representation.
     * @param mixed $default [optional] The default fallback value.
     *
     * @return mixed The requested value or null.
     */
    function data_get() {
        return app()->data->get(...func_get_args());
    }
}

if (!function_exists('data_set')) {
    /**
     * Sets a value of a key in `self::$bag` via dot-notation.
     *
     * @param string $key The dotted key representation.
     * @param mixed $value The value to set.
     *
     * @return void
     */
    function data_set() {
        return app()->data->set(...func_get_args());
    }
}



if (!function_exists('html')) {
    /**
     * Returns an instance of the `HTML` class.
     *
     * @return \MAKS\Velox\Frontend\HTML
     */
    function html() {
        return app()->html;
    }
}



if (!function_exists('path')) {
    /**
     * Returns an instance of the `Path` class.
     *
     * @return \MAKS\Velox\Frontend\Path
     */
    function path() {
        return app()->path;
    }
}

if (!function_exists('app_path_current')) {
    /**
     * Returns the current path, or compares it with the passed parameter.
     *
     * @param string|null $compareTo [optional] Some path on the server.
     *
     * @return string|bool If null is passed, the current path as string. Otherwise the result of comparing the current path with the passed parameter as boolean.
     */
    function app_path_current() {
        return app()->path->current(...func_get_args());
    }
}

if (!function_exists('app_url_current')) {
    /**
     * Returns the current URL, or compares it with the passed parameter.
     *
     * @param string|null $compareTo [optional] Some URL on the server.
     *
     * @return string|bool If null is passed, the current URL as string. Otherwise the result of comparing the current URL with the passed parameter as boolean.
     */
    function app_url_current() {
        return app()->path->currentUrl(...func_get_args());
    }
}

if (!function_exists('app_path')) {
    /**
     * Resolves the passed path to the app root path and returns it.
     *
     * @param string [optional] $path The path from app root.
     *
     * @return string An absolute path on the server starting from app root.
     */
    function app_path() {
        return app()->path->resolve(...func_get_args());
    }
}

if (!function_exists('app_url')) {
    /**
     * Resolves the passed path to the base URL (starting from app root) and returns it.
     *
     * @param string [optional] $path The path from app root.
     *
     * @return string An absolute path on the server (public URL) starting from app root.
     */
    function app_url() {
        return app()->path->resolveUrl(...func_get_args());
    }
}

if (!function_exists('theme_path')) {
    /**
     * Resolves the passed path to the theme root path and returns it.
     *
     * @param string [optional] $path The path from theme root.
     * @param string [optional] $prefix The prefix to prefix the returned path with (base URL for example).
     *
     * @return string A relative path starting from app root to the root of the active theme directory.
     */
    function theme_path() {
        return app()->path->resolveFromTheme(...func_get_args());
    }
}

if (!function_exists('theme_url')) {
    /**
     * Resolves the passed path to the base URL (starting from active theme root) and returns it.
     *
     * @param string [optional] $path The path from theme root.
     *
     * @return string An absolute path on the server (public URL) starting from active theme root.
     */
    function theme_url() {
        return app()->path->resolveUrlFromTheme(...func_get_args());
    }
}

if (!function_exists('assets_path')) {
    /**
     * Resolves the passed path to the assets directory and returns it.
     *
     * @param string [optional] $path The path from theme root assets root.
     * @param string [optional] $prefix The prefix to prefix the returned path with (base URL for example).
     *
     * @return string A relative path starting from app root to the root of the assets directory of the active theme directory.
     */
    function assets_path() {
        return app()->path->resolveFromAssets(...func_get_args());
    }
}

if (!function_exists('assets_url')) {
    /**
     * Resolves the passed path to the base URL (starting from active theme assets root) and returns it.
     *
     * @param string [optional] $path The path from theme root assets root.
     *
     * @return string An absolute path on the server (public URL) starting from active theme root.
     */
    function assets_url() {
        return app()->path->resolveUrlFromAssets(...func_get_args());
    }
}



if (!function_exists('dd')) {
    /**
     * Dumps a variable and dies.
     *
     * @param mixed ...$variable
     *
     * @return void The result will simply get echoed.
     */
    function dd(...$variable) {
        app()->dumper->dd(...func_get_args());
    }
}

if (!function_exists('dump')) {
    /**
     * Dumps a variable in a nice HTML block with syntax highlighting.
     *
     * @param mixed ...$variable
     *
     * @return void The result will simply get echoed.
     */
    function dump(...$variable) {
        app()->dumper->dump(...func_get_args());
    }
}

if (!function_exists('dump_exception')) {
    /**
     * Dumps an exception in a nice HTML page or as string and exits the script.
     *
     * @param \Throwable $exception
     *
     * @return void The result will be echoed as HTML page or a string representation of the exception if the interface is CLI.
     */
    function dump_exception(Throwable $exception) {
        app()->dumper->dumpException(...func_get_args());
    }
}
