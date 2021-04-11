<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);



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
