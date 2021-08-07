<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Frontend;

use MAKS\Velox\Backend\Config;
use MAKS\Velox\Backend\Globals;

/**
 * A class that serves as a path resolver for different paths/URLs of the app.
 *
 * Example:
 * ```
 * // NOTE: all methods that have "resolve" as prefix
 * // can also be called without the resolve prefix
 * // Path::resolve*() -> Path:*() like Path::resolveUrl() -> Path::url()
 *
 * // get an absolute path from app root
 * $path = Path::resolve('some/path');
 *
 * // get a public URL from app root
 * $url = Path::resolveUrl('some/route');
 *
 *
 * // get a relative path from theme root
 * $path = Path::resolveFromTheme('some/file.ext');
 *
 * // get a public URL from theme root
 * $url = Path::resolveUrlFromTheme('some/file.ext');
 *
 *
 * // get a relative path from theme assets root
 * $path = Path::resolveFromAssets('some/file.ext');
 *
 * // get a public URL from theme assets root
 * $url = Path::resolveUrlFromAssets('some/file.ext');
 * ```
 *
 * @method static string url(string $path = '/')
 * @method static string fromTheme(string $path = '/', string $prefix = '')
 * @method static string urlFromTheme(string $path = '/')
 * @method static string fromAssets(string $path = '/', string $prefix = '')
 * @method static string urlFromAssets(string $path = '/')
 *
 * @since 1.0.0
 * @api
 */
final class Path
{
    /**
     * Returns the current path, or compares it with the passed parameter.
     *
     * @param string|null $compareTo [optional] Some path on the server.
     *
     * @return string|bool If null is passed, the current path as string. Otherwise the result of comparing the current path with the passed parameter as boolean.
     */
    public static function current(?string $compareTo = null)
    {
        $path = Globals::getServer('REQUEST_URI');

        if ($compareTo) {
            return $path === $compareTo;
        }

        return $path;
    }

    /**
     * Returns the current URL, or compares it with the passed parameter.
     *
     * @param string|null $compareTo [optional] Some URL on the server.
     *
     * @return string|bool If null is passed, the current URL as string. Otherwise the result of comparing the current URL with the passed parameter as boolean.
     */
    public static function currentUrl(?string $compareTo = null)
    {
        $url = static::resolveUrl((string)static::current());

        if ($compareTo) {
            return $url === $compareTo;
        }

        return $url;
    }

    /**
     * Resolves the passed path to the app root path and returns it.
     *
     * @param string [optional] $path The path from app root.
     *
     * @return string An absolute path on the server starting from app root.
     */
    public static function resolve(string $path = '/'): string
    {
        static $root = null;

        if ($root === null) {
            $root = Config::get('global.paths.root');
        }

        $absolutePath = sprintf(
            '%s/%s',
            rtrim($root, '/'),
            ltrim($path, '/')
        );

        $canonicalPath = realpath($absolutePath);

        return $canonicalPath ? $canonicalPath : $absolutePath;
    }

    /**
     * Resolves the passed path to the base URL (starting from app root) and returns it.
     *
     * @param string [optional] $path The path from app root.
     *
     * @return string An absolute path on the server (public URL) starting from app root.
     */
    public static function resolveUrl(string $path = '/'): string
    {
        static $url = null;

        if ($url === null) {
            $url = (Globals::getServer('HTTPS') === 'on' ? 'https' : 'http') . '://' . Globals::getServer('HTTP_HOST');
        }

        return sprintf(
            '%s/%s',
            rtrim($url, '/'),
            ltrim($path, '/')
        );
    }

    /**
     * Resolves the passed path to the theme root path and returns it.
     *
     * @param string [optional] $path The path from theme root.
     * @param string [optional] $prefix The prefix to prefix the returned path with (base URL for example).
     *
     * @return string A relative path starting from app root to the root of the active theme directory.
     */
    public static function resolveFromTheme(string $path = '/', string $prefix = ''): string
    {
        static $theme = null;

        if ($theme === null) {
            $theme = str_replace(
                Config::get('global.paths.root'),
                '',
                Config::get('theme.paths.root')
            );
        }

        return sprintf(
            '%s/%s/%s',
            rtrim($prefix, '/'),
            trim($theme, '/'),
            ltrim($path, '/')
        );
    }

    /**
     * Resolves the passed path to the base URL (starting from active theme root) and returns it.
     *
     * @param string [optional] $path The path from theme root.
     *
     * @return string An absolute path on the server (public URL) starting from active theme root.
     */
    public static function resolveUrlFromTheme(string $path = '/'): string
    {
        return static::resolveFromTheme($path, static::resolveUrl());
    }

    /**
     * Resolves the passed path to the assets directory and returns it.
     *
     * @param string [optional] $path The path from theme root assets root.
     * @param string [optional] $prefix The prefix to prefix the returned path with (base URL for example).
     *
     * @return string A relative path starting from app root to the root of the assets directory of the active theme directory.
     */
    public static function resolveFromAssets(string $path = '/', string $prefix = ''): string
    {
        static $assets = null;

        if (!$assets) {
            $assets = str_replace(
                Config::get('theme.paths.root'),
                '',
                Config::get('theme.paths.assets')
            );

            $assets = static::resolveFromTheme($assets);
        }

        return sprintf(
            '%s/%s/%s',
            rtrim($prefix, '/'),
            trim($assets, '/'),
            ltrim($path, '/')
        );
    }

    /**
     * Resolves the passed path to the base URL (starting from active theme assets root) and returns it.
     *
     * @param string [optional] $path The path from theme root assets root.
     *
     * @return string An absolute path on the server (public URL) starting from active theme root.
     */
    public static function resolveUrlFromAssets(string $path = '/'): string
    {
        return static::resolveFromAssets($path, static::resolveUrl());
    }

    /**
     * Returns a normalized path based on OS.
     *
     * @param string $directory
     * @param string $filename
     * @param string $extension
     *
     * @return string
     */
    public static function normalize(string $directory, string $filename, string $extension = ''): string
    {
        $filename = substr($filename, -strlen($extension)) === $extension ? $filename : $filename . $extension;
        $directory = $directory . '/';

        return preg_replace('/\/+|\\+/', DIRECTORY_SEPARATOR, $directory . $filename);
    }


    /**
     * Aliases `self::resolve*()` with a function of the same name without the "resolve" prefix.
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $class  = static::class;
        $method = sprintf('resolve%s', ucfirst($method));

        if (!method_exists($class, $method)) {
            throw new \Exception("Call to undefined method {$class}::{$method}()");
        }

        return static::$method(...$arguments);
    }

    /**
     * Allows static methods handled by self::__callStatic() to be accessible via object operator `->`.
     */
    public function __call(string $method, array $arguments)
    {
        return static::__callStatic($method, $arguments);
    }
}
