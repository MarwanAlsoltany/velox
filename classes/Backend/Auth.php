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
use MAKS\Velox\Backend\Event;
use MAKS\Velox\Backend\Config;
use MAKS\Velox\Backend\Model;
use MAKS\Velox\Backend\Globals;
use MAKS\Velox\Backend\Session;
use MAKS\Velox\Frontend\View;

/**
 * A class that serves as an authentication system for users.
 *
 * Example:
 * ```
 * // register a new user
 * $auth = new Auth(); // or Auth::instance();
 * $status = $auth->register('username', 'password');
 *
 * // unregister a user
 * $status = Auth::instance()->unregister('username');
 *
 * // log in a user
 * $status = Auth::instance()->login('username', 'password');
 *
 * // log out a user
 * Auth::instance()->logout();
 *
 * // authenticate a user model
 * Auth::authenticate($user);
 *
 * // check if there is a logged in user
 * $status = Auth::check();
 *
 * // retrieve the current authenticated user
 * $user = Auth::user();
 *
 * // add HTTP basic auth
 * Auth::basic(['username' => 'password']);
 * ```
 *
 * @package Velox\Backend
 * @since 1.4.0
 * @api
 */
class Auth
{
    /**
     * This event will be dispatched when an auth user is registered.
     * This event will be passed the user model object and its listener callback will be bound to the object (the auth class).
     * This event is useful if the user model class has additional attributes other than the `username` and `password` that need to be set.
     *
     * @var string
     */
    public const ON_REGISTER = 'auth.on.register';

    /**
     * This event will be dispatched after an auth user is registered.
     * This event will be passed the user model object and its listener callback will be bound to the object (the auth class instance).
     *
     * @var string
     */
    public const AFTER_REGISTER = 'auth.after.register';

    /**
     * This event will be dispatched when an auth user is unregistered.
     * This event will be passed the user model object and its listener callback will be bound to the object (the auth class instance).
     *
     * @var string
     */
    public const ON_UNREGISTER = 'auth.on.unregister';

    /**
     * This event will be dispatched when an auth user is logged in.
     * This event will be passed the user model object and its listener callback will be bound to the object (the auth class instance).
     *
     * @var string
     */
    public const ON_LOGIN = 'auth.on.login';

    /**
     * This event will be dispatched when an auth user is logged out.
     * This event will be passed the user model object and its listener callback will be bound to the object (the auth class instance).
     *
     * @var string
     */
    public const ON_LOGOUT = 'auth.on.logout';


    /**
     * The class singleton instance.
     */
    protected static self $instance;


    /**
     * Auth user model.
     */
    protected Model $user;


    /**
     * Class constructor.
     *
     * @param string $model [optional] The auth user model class to use.
     */
    public function __construct(?string $model = null)
    {
        if (empty(static::$instance)) {
            static::$instance = $this;
        }

        $this->user = $this->getUserModel($model);

        $this->check();
    }


    /**
     * Returns the singleton instance of the class.
     *
     * NOTE: This method returns only the first instance of the class
     * which is normally the one that was created during application bootstrap.
     *
     * @return static
     */
    final public static function instance(): self
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Registers a new user.
     *
     * @param string $username Auth user username.
     * @param string $password Auth user password.
     *
     * @return bool True if the user was registered successfully, false if the user is already registered.
     */
    public function register(string $username, string $password): bool
    {
        $user = $this->user->one([
            'username' => $username,
        ]);

        if ($user instanceof Model) {
            return false;
        }

        $user = $this->user->create([
            'username' => $username,
            'password' => $this->hash($password),
        ]);

        Event::dispatch(self::ON_REGISTER, [$user], $this);

        $user->save();

        Event::dispatch(self::AFTER_REGISTER, [$user], $this);

        return true;
    }

    /**
     * Unregisters a user.
     *
     * @param string $username Auth user username.
     *
     * @return bool True if the user was unregistered successfully, false if the user is not registered.
     */
    public function unregister(string $username): bool
    {
        $user = $this->user->one([
            'username' => $username,
        ]);

        if (!$user) {
            return false;
        }

        if ($this->check()) {
            $this->logout();
        }

        Event::dispatch(self::ON_UNREGISTER, [$user], $this);

        $user->delete();

        return true;
    }

    /**
     * Logs in a user.
     *
     * @param string $username Auth user username.
     * @param string $password Auth user password.
     *
     * @return bool True if the user was logged in successfully, false if the user is not registered or the password is incorrect.
     */
    public function login(string $username, string $password): bool
    {
        $user = $this->user->one([
            'username' => $username,
        ]);

        if (
            $user instanceof Model &&
            (
                $this->verify($password, $user->getPassword()) ||
                $password === $user->getPassword() // self::authenticate() will pass a hashed password
            )
        ) {
            Session::set('_auth.username', $username);
            Session::set('_auth.timeout', time() + Config::get('auth.user.timeout', 3600));

            Event::dispatch(self::ON_LOGIN, [$user], $this);

            return true;
        }

        return false;
    }

    /**
     * Logs out a user.
     *
     * @return void
     */
    public function logout(): void
    {
        $user = $this->user();

        Session::cut('_auth');

        Event::dispatch(self::ON_LOGOUT, [$user], $this);
    }

    /**
     * Authenticates an auth user model.
     *
     * @param Model $user The auth user model to authenticate.
     *
     * @return void
     *
     * @throws \Exception If the user could not be authenticated.
     */
    public static function authenticate(Model $user): void
    {
        $success = static::instance()->login(
            $user->getUsername(),
            $user->getPassword()
        );

        if (!$success) {
            throw new \Exception("Could not authenticate auth user with ID '{$user->getId()}'");
        }
    }

    /**
     * Checks if a user is logged in and logs the user out if the timeout has expired.
     *
     * @return bool
     */
    public static function check(): bool
    {
        if (Session::get('_auth.timeout') <= time()) {
            Session::cut('_auth');
        }

        if (Session::has('_auth')) {
            return true;
        }

        return false;
    }

    /**
     * Returns the authenticated user model instance.
     *
     * @return Model|null The authenticated user or null if no user has logged in.
     */
    public static function user(): ?Model
    {
        if ($username = Session::get('_auth.username')) {
            return static::getUserModel()->findByUsername($username)[0] ?? null;
        }

        return null;
    }

    /**
     * Serves as an HTTP Basic Authentication guard for the specified logins.
     *
     * @param array $logins The login data, an associative array where key is the `username` and value is the `password`.
     *
     * @return void
     *
     * @codeCoverageIgnore Can't test methods that send headers.
     */
    public static function basic(array $logins = [])
    {
        if (count($logins) === 0) {
            throw new \Exception('No login(s) provided');
        }

        $username = Globals::getServer('PHP_AUTH_USER');
        $password = Globals::getServer('PHP_AUTH_PW');

        $isAuthenticated = false;
        foreach ($logins as $user => $pass) {
            if ($username === $user && $password === $pass) {
                $isAuthenticated = true;

                break;
            }
        }

        header('Cache-Control: no-cache, must-revalidate, max-age=0');

        if (!$isAuthenticated) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');

            self::fail();
        }
    }

    /**
     * Renders 401 error page.
     *
     * @return void
     *
     * @codeCoverageIgnore Can't test methods that send headers.
     */
    public static function fail(): void
    {
        App::log('Responded with 401 to the request for "{uri}". Authentication failed. Client IP address {ip}', [
            'uri' => Globals::getServer('REQUEST_URI'),
            'ip'  => Globals::getServer('REMOTE_ADDR'),
        ], 'system');

        http_response_code(401);

        try {
            echo View::render((string)Config::get('global.errorPages.401'));
            App::terminate();
        } catch (\Throwable $e) {
            App::abort(401, null, 'Wrong username or password!');
        }
    }

    /**
     * Hashes a password.
     *
     * @param string $password
     *
     * @return string The hashed password.
     */
    protected function hash(string $password): string
    {
        $hashingConfig = Config::get('auth.hashing');

        return password_hash($password, $hashingConfig['algorithm'] ?? PASSWORD_DEFAULT, [
            'cost' => $hashingConfig['cost'] ?? 10,
        ]);
    }

    /**
     * Verifies a password.
     *
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
    protected function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Returns an instance of the user model class specified in the config or falls back to the default one.
     *
     * @param string $model [optional] The auth user model class to use.
     *
     * @return Model
     */
    protected static function getUserModel(?string $model = null): Model
    {
        $model = $model ?? Config::get('auth.user.model');

        $model = class_exists((string)$model)
            ? new $model()
            : new class () extends Model {
                public static ?string $table = 'users';
                public static ?string $primaryKey = 'id';
                public static ?array $columns = ['id', 'username', 'password'];
                public static function schema(): string
                {
                    return '
                        CREATE TABLE IF NOT EXISTS `users` (
                            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `username` VARCHAR(255) NOT NULL UNIQUE,
                            `password` VARCHAR(255) NOT NULL
                        );
                    ';
                }
            };

        Config::set('auth.user.model', get_class($model));

        return $model;
    }
}
