<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend;

use MAKS\Velox\Backend\Session\Flash;
use MAKS\Velox\Backend\Session\CSRF;
use MAKS\Velox\Backend\Config;

/**
 * A class that offers a simple interface to work with sessions.
 *
 * Example:
 * ```
 * // start a session
 * Session::start();
 *
 * // check for variable availability
 * $someVarExists = Session::has('someVar');
 *
 * // set a session variable
 * Session::set('someVar', $value);
 *
 * // get a session variable
 * $someVar = Session::get('someVar');
 *
 * // destroy a session
 * Session::destroy();
 *
 * // get an instance of the Flash class
 * $flash = Session::flash();
 *
 * // get an instance of the CSRF class
 * $flash = Session::csrf();
 * ```
 *
 * @package Velox\Backend
 * @since 1.3.0
 * @api
 */
final class Session
{
    /**
     * Class constructor.
     *
     * @param int|null $expiration Session expiration time in minutes.
     * @param string|null $limiter Session limiter.
     * @param string|null $path Session save path.
     */
    public function __construct(?int $expiration = null, ?string $limiter = null, ?string $path = null)
    {
        $this->start($expiration, $limiter, $path);
    }

    /**
     * Starts the session if it is not already started.
     *
     * @param int|null [optional] $expiration Session expiration time in minutes.
     * @param string|null [optional] $limiter Session limiter.
     * @param string|null [optional] $path Session save path.
     *
     * @return bool True if the session was started, false otherwise.
     */
    public static function start(?int $expiration = null, ?string $limiter = null, ?string $path = null): bool
    {
        $path       ??= Config::get('session.path', Config::get('global.paths.storage') . '/sessions');
        $limiter    ??= Config::get('session.cache.limiter', 'nocache');
        $expiration ??= Config::get('session.cache.expiration', 180);

        file_exists($path) || mkdir($path, 0744, true);

        session_save_path() != $path && session_save_path($path);
        session_cache_expire() != $expiration && session_cache_expire($expiration);
        session_cache_limiter() != $limiter && session_cache_limiter($limiter);

        $status = session_status() != PHP_SESSION_NONE || session_start(['name' => 'VELOX']);

        return $status;
    }

    /**
     * Destroys all of the data associated with the current session.
     * This method does not unset any of the global variables associated with the session, or unset the session cookie.
     *
     * @return bool True if the session was destroyed, false otherwise.
     */
    public static function destroy(): bool
    {
        return session_destroy();
    }

    /**
     * Unsets the session superglobal
     * This method deletes (truncates) only the variables in the session, session still exists.
     *
     * @return bool True if the session was unset, false otherwise.
     */
    public static function unset(): bool
    {
        return session_unset();
    }

    /**
     * Clears the session entirely.
     * This method will unset the session, destroy the session, commit (close writing) to the session, and reset the session cookie (new expiration).
     *
     * @return bool True if the session was cleared, false otherwise.
     */
    public static function clear(): bool
    {
        $name   = session_name();
        $cookie = session_get_cookie_params();

        setcookie($name, '', 0, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly'] ?? false);
        // not testable in CLI, headers already sent
        // @codeCoverageIgnoreStart
        $unset   = session_unset();
        $destroy = session_destroy();
        $commit  = session_commit();

        return ($unset && $destroy && $commit);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Checks if a value exists in the session.
     *
     * @param string $key The key to check. Dot-notation can be used with nested arrays.
     *
     * @return bool True if the key exists, false otherwise.
     */
    public static function has(string $key): bool
    {
        return Globals::getSession($key) !== null;
    }

    /**
     * Gets a value from the session.
     *
     * @param string $key The key to get. Dot-notation can be used with nested arrays.
     *
     * @return mixed The value of the key, or null if the key does not exist.
     */
    public static function get(string $key)
    {
        return Globals::getSession($key);
    }

    /**
     * Sets a value in the session.
     *
     * @param string $key The key to set. Dot-notation can be used with nested arrays.
     * @param mixed $value The value to set.
     *
     * @return static The current instance.
     */
    public static function set(string $key, $value)
    {
        Globals::setSession($key, $value);

        return new static();
    }

    /**
     * Cuts a value from the session. The value will be returned and the key will be unset from the array.
     *
     * @param string $key The key to cut. Dot-notation can be used with nested arrays.
     *
     * @return mixed The value of the key, or null if the key does not exist.
     */
    public static function cut(string $key)
    {
        return Globals::cutSession($key);
    }


    /**
     * Writes a flash message to the session.
     * This method can be invoked without arguments, in that case a `Flash` object will be returned.
     *
     * @param string $type [optional] Message type.
     * @param string $text [optional] Message text.
     * @param bool $now [optional] Whether to write and make the message available for rendering immediately or wait for the next request.
     *
     * @return Flash
     */
    public static function flash(string $text = '', string $type = '', bool $now = false): Flash
    {
        static $flash = null;

        if ($flash === null) {
            $flash = new Flash();
        }

        if (strlen(trim($text))) {
            $flash($type, $text, $now);
        }

        return $flash;
    }

    /**
     * Returns an instance of the CSRF class.
     *
     * @param string $name [optional] The name of the CSRF token. Default to `{session.csrf.name}` configuration value.
     * If a token name other than the default is specified, validation of this token has to be implemented manually.
     *
     * @return CSRF
     */
    public static function csrf(?string $name = null): CSRF
    {
        return new CSRF($name);
    }
}
