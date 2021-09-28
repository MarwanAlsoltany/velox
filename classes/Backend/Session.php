<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend;

use MAKS\Velox\App;
use MAKS\Velox\Frontend\View;
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Helper\Misc;

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
 * ```
 *
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
    public function __construct(?string $expiration = null, ?string $limiter = null, ?string $path = null)
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

        return (bool)($unset & $destroy & $commit);
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
     *
     * This method can be invoked without arguments, in that case a `Flash` object will be returned.
     * The `Flash` object has the following methods:
     * - `message(string $type, string $text, bool $now = false): static`: Writes a flash message to the session.
     * - `render(?callable $callback = null): ?string`: Renders the flash messages using the default callback or the passed one (callback will be passed: `$text`, `$type`).
     * The `render()` method will be called automatically if the object is casted to a string.
     *
     * The `Flash` object consists also of magic methods with the following signature:
     * - `{type}(string $text, bool $now = false)`
     * Where `{type}` is a message type like [`success`, `info`, `warning`, `error`] or any other value.
     * The `{type}` will also be used as a CSS class if the default rendering callback is used (camelCase will be changed to kebab-case automatically).
     *
     * @param string $type [optional] Message type.
     * @param string $text [optional] Message text.
     * @param bool $now [optional] Whether to write and make the message available for rendering immediately or wait for the next request.
     *
     * @return object
     */
    public static function flash(string $text = '', string $type = '', bool $now = false): object
    {
        self::start();

        static $flash = null;

        if ($flash === null) {
            $flash = new class {
                private string $name = '_flash';
                private array $messages = [];
                public function __construct()
                {
                    $this->messages = Globals::getSession($this->name) ?? [];

                    Globals::setSession($this->name, []);
                }
                public function __invoke()
                {
                    return $this->message(...func_get_args());
                }
                public function __call(string $method, array $arguments)
                {
                    return $this->message(
                        Misc::transform($method, 'kebab'),
                        $arguments[0] ?? '',
                        $arguments[1] ?? false
                    );
                }
                public function __toString()
                {
                    return $this->render();
                }
                public function message(string $type, string $text, bool $now = false)
                {
                    if ($now) {
                        $this->messages[md5(uniqid($text))] = [
                            'type' => $type,
                            'text' => $text
                        ];

                        return $this;
                    }

                    Globals::setSession($this->name . '.' . md5(uniqid($text)), [
                        'type' => $type,
                        'text' => $text
                    ]);

                    return $this;
                }
                public function render(?callable $callback = null): string
                {
                    $callback = $callback ?? function ($text, $type) {
                        return HTML::div($text, [
                            'class' => 'flash-message ' . $type
                        ]);
                    };

                    $html = '';

                    foreach ($this->messages as $message) {
                        $html .= $callback($message['text'], $message['type']);
                    }

                    return $html;
                }
            };
        }

        if (strlen(trim($text))) {
            $flash($type, $text, $now);
        }

        return $flash;
    }

    /**
     * Generates and checks CSRF tokens.
     *
     * This method will return a `CSRF` object.
     * The `CSRF` object has the following methods:
     * - `isValid(): bool`: Validate the request token with the token stored in the session.
     * - `token(): string`: Generates a CSRF token, stores it in the session and returns it.
     * - `html(): string`: Returns an HTML input element containing a CSRF token after storing it in the session.
     * The `html()` method will be called automatically if the object is casted to a string.
     *
     * @param string $name [optional] The name of the CSRF token. Default to "{session.csrf.name}" configuration value.
     * If a token name other than the default is specified, validation of this token has to be implemented manually.
     *
     * @return object
     */
    public static function csrf(?string $name = null): object
    {
        self::start();

        return new class ($name) {
            private string $name;
            private string $token;
            public function __construct(?string $name = null)
            {
                $this->name  = $name ?? Config::get('session.csrf.name', '_token');
                $this->token = Globals::getSession($this->name) ?? '';
            }
            public function __toString()
            {
                return $this->html();
            }
            public function token(): string
            {
                $this->token = empty($this->token) ? bin2hex(random_bytes(64)) : $this->token;

                Globals::setSession($this->name, $this->token);

                return $this->token;
            }
            public function html(): string
            {
                return HTML::input(null, [
                    'type'  => 'hidden',
                    'name'  => $this->name,
                    'value' => $this->token()
                ]);
            }
            public function check(): void
            {
                if ($this->isValid()) {
                    return;
                }

                App::log('Responded with 403 to the request for "{path}". CSRF is detected. Client IP address {ip}', [
                    'uri' => Globals::getServer('REQUEST_URI'),
                    'ip'  => Globals::getServer('REMOTE_ADDR'),
                ], 'system');

                http_response_code(403);

                try {
                    echo View::render(Config::get('global.errorPages.403'));
                    App::terminate();
                } catch (\Throwable $e) {
                    App::abort(403, null, 'Invalid CSRF token!');
                }
            }
            public function isValid(): bool
            {
                if ($this->isWhitelisted() || $this->isIdentical()) {
                    return true;
                }

                Globals::cutSession($this->name);

                return false;
            }
            private function isWhitelisted(): bool
            {
                $method = Globals::getServer('REQUEST_METHOD');
                $client = Globals::getServer('REMOTE_HOST', Globals::getServer('REMOTE_ADDR'));

                return (
                    in_array($client, Config::get('session.csrf.whitelisted', [])) ||
                    !in_array($method, Config::get('session.csrf.methods', []))
                );
            }
            private function isIdentical(): bool
            {
                $token = Globals::cutPost($this->name, Globals::cutGet($this->name)) ?? '';

                return empty($this->token) || hash_equals($this->token, $token);
            }
        };
    }
}
