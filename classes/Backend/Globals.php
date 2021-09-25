<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend;

use MAKS\Velox\Helper\Misc;

/**
 * A class that serves as an abstraction/wrapper to work with superglobals.
 *
 * @method static mixed getGet(string $key = null) Gets a value from $_GET. Dot-notation can be used for nested values.
 * @method static mixed getPost(string $key = null) Gets a value from $_POST. Dot-notation can be used for nested values.
 * @method static mixed getFiles(string $key = null) Gets a value from $_FILES. Dot-notation can be used for nested values.
 * @method static mixed getCookie(string $key = null) Gets a value from $_COOKIE. Dot-notation can be used for nested values.
 * @method static mixed getSession(string $key = null) Gets a value from $_SESSION. Dot-notation can be used for nested values.
 * @method static mixed getRequest(string $key = null) Gets a value from $_REQUEST. Dot-notation can be used for nested values.
 * @method static mixed getServer(string $key = null) Gets a value from $_SERVER. Dot-notation can be used for nested values.
 * @method static mixed getEnv(string $key = null) Gets a value from $_ENV. Dot-notation can be used for nested values.
 * @method static static setGet(string $key, $value) Sets a value in $_GET. Dot-notation can be used for nested values.
 * @method static static setPost(string $key, $value) Sets a value in $_POST. Dot-notation can be used for nested values.
 * @method static static setFiles(string $key, $value) Sets a value in $_FILES. Dot-notation can be used for nested values.
 * @method static static setCookie(string $key, $value) Sets a value in $_COOKIE. Dot-notation can be used for nested values.
 * @method static static setSession(string $key, $value) Sets a value in $_SESSION. Dot-notation can be used for nested values.
 * @method static static setRequest(string $key, $value) Sets a value in $_REQUEST. Dot-notation can be used for nested values.
 * @method static static setServer(string $key, $value) Sets a value in $_SERVER. Dot-notation can be used for nested values.
 * @method static static setEnv(string $key, $value) Sets a value in $_ENV. Dot-notation can be used for nested values.
 * @method static mixed cutGet(string $key = null) Cuts a value from $_GET. Dot-notation can be used for nested values.
 * @method static mixed cutPost(string $key = null) Cuts a value from $_POST. Dot-notation can be used for nested values.
 * @method static mixed cutFiles(string $key = null) Cuts a value from $_FILES. Dot-notation can be used for nested values.
 * @method static mixed cutCookie(string $key = null) Cuts a value from $_COOKIE. Dot-notation can be used for nested values.
 * @method static mixed cutSession(string $key = null) Cuts a value from $_SESSION. Dot-notation can be used for nested values.
 * @method static mixed cutRequest(string $key = null) Cuts a value from $_REQUEST. Dot-notation can be used for nested values.
 * @method static mixed cutServer(string $key = null) Cuts a value from $_SERVER. Dot-notation can be used for nested values.
 * @method static mixed cutEnv(string $key = null) Cuts a value from $_ENV. Dot-notation can be used for nested values.
 *
 * @property object $get A class around the superglobal `$_GET` that has the methods `has($key)`, `get($key, $default)`, `set($key, $value)`, and `getAll()`.
 * @property object $post A class around the superglobal `$_POST` that has the methods `has($key)`, `get($key, $default)`, `set($key, $value)`, and `getAll()`.
 * @property object $files A class around the superglobal `$_FILES` that has the methods `has($key)`, `get($key, $default)`, `set($key, $value)`, and `getAll()`.
 * @property object $cookie A class around the superglobal `$_COOKIE` that has the methods `has($key)`, `get($key, $default)`, `set($key, $value)`, and `getAll()`.
 * @property object $session A class around the superglobal `$_SESSION` that has the methods `has($key)`, `get($key, $default)`, `set($key, $value)`, and `getAll()`.
 * @property object $request A class around the superglobal `$_REQUEST` that has the methods `has($key)`, `get($key, $default)`, `set($key, $value)`, and `getAll()`.
 * @property object $server A class around the superglobal `$_SERVER` that has the methods `has($key)`, `get($key, $default)`, `set($key, $value)`, and `getAll()`.
 * @property object $env A class around the superglobal `$_ENV` that has the methods `has($key)`, `get($key, $default)`, `set($key, $value)`, and `getAll()`.
 *
 * @since 1.0.0
 */
final class Globals
{
    public const GLOBALS = [
        '_GET'     => '_GET',
        '_POST'    => '_POST',
        '_FILES'   => '_FILES',
        '_COOKIE'  => '_COOKIE',
        '_SESSION' => '_SESSION',
        '_REQUEST' => '_REQUEST',
        '_SERVER'  => '_SERVER',
        '_ENV'     => '_ENV',
    ];


    /**
     * This array holds an anonymous class that acts as a wrapper for each superglobal.
     */
    protected static array $globals;

    protected static bool $isInitialized = false;

    private static array $_GET;
    private static array $_POST;
    private static array $_FILES;
    private static array $_COOKIE;
    private static array $_SESSION;
    private static array $_REQUEST;
    private static array $_SERVER;
    private static array $_ENV;


    /**
     * Initializes class internal state from superglobals and returns an instance o it.
     *
     * @return static
     */
    public static function instance()
    {
        static::initialize();

        return new static();
    }

    /**
     * Initializes class internal state from superglobals.
     *
     * @return void
     */
    public static function initialize(): void
    {
        if (!static::$isInitialized) {
            foreach (self::GLOBALS as $global) {
                global $$global;
                self::$$global = isset($$global) ? self::$$global = &$$global : [];
            }

            static::$isInitialized = true;
        }
    }

    /**
     * Gets a value from the specified superglobal.
     *
     * @param string $name The superglobal name to get the value from. Can be written in any case with or without the leading underscore.
     * @param string $key The array element to get from the superglobal. Dot-notation can be used with nested arrays.
     *
     * @return mixed
     *
     * @throws \Exception If the passed name is not a superglobal.
     */
    public static function get(string $name, string $key = null)
    {
        static::initialize();

        $name = static::getValidNameOrFail($name);

        if ($key !== null) {
            return Misc::getArrayValueByKey(self::$$name, $key, null);
        }

        return self::$$name;
    }

    /**
     * Sets a value in the specified superglobal.
     *
     * @param string $name The superglobal name to set the value in. Can be written in any case with or without the leading underscore.
     * @param string $key The array element to set in the superglobal. Dot-notation can be used with nested arrays.
     * @param mixed $value The value to set.
     *
     * @return static
     *
     * @throws \Exception If the passed name is not a superglobal.
     */
    public static function set(string $name, string $key, $value)
    {
        static::initialize();

        $name = static::getValidNameOrFail($name);

        Misc::setArrayValueByKey(self::$$name, $key, $value);

        return new static();
    }

    /**
     * Cuts a value from the specified superglobal. The value will be returned and the key will be unset from the superglobal.
     *
     * @param string $name The superglobal name to get the value from. Can be written in any case with or without the leading underscore.
     * @param string $key The array element to get from the superglobal. Dot-notation can be used with nested arrays.
     *
     * @return static
     *
     * @throws \Exception If the passed name is not a superglobal.
     */
    public static function cut(string $name, string $key)
    {
        static::initialize();

        $name = static::getValidNameOrFail($name);

        return Misc::cutArrayValueByKey(self::$$name, $key, null);
    }

    /**
     * Returns all superglobals.
     *
     * @return array
     */
    public static function getAll(): array
    {
        static::initialize();

        $globals = [];
        foreach (self::GLOBALS as $global) {
            $globals[$global] = self::$$global;
        }

        return $globals;
    }

    /**
     * Returns a valid superglobal name from the passed name.
     *
     * @param string $name
     *
     * @return string
     *
     * @throws \Exception
     */
    private static function getValidNameOrFail(string $name): string
    {
        $variable = '_' . trim(strtoupper($name), '_');

        if (!in_array($variable, self::GLOBALS)) {
            $available = implode(', ', self::GLOBALS);

            throw new \Exception("There is no PHP superglobal with the name '{$name}'. Available superglobals are: [{$available}]");
        }

        return $variable;
    }


    /**
     * Class constructor.
     */
    final public function __construct()
    {
        // the constructor is final to allow to the use of
        // "return new static()" without caring about class dependencies.

        $this->initialize();
    }

    /**
     * Aliases getters and setter for class members.
     */
    public function __get(string $name)
    {
        try {
            $name   = static::getValidNameOrFail($name);
            $global = &self::$$name;

            if (isset(static::$globals[$name])) {
                return static::$globals[$name];
            }

            return static::$globals[$name] = new class ($global) {
                private $self;
                public function __construct(&$self)
                {
                    $this->self = &$self;
                }
                public function has(string $key)
                {
                    $value = Misc::getArrayValueByKey($this->self, $key, null);
                    return isset($value);
                }
                public function get(string $key, $default = null)
                {
                    return Misc::getArrayValueByKey($this->self, $key, $default);
                }
                public function set(string $key, $value)
                {
                    Misc::setArrayValueByKey($this->self, $key, $value);
                    return $this;
                }
                public function getAll(): array
                {
                    return $this->self;
                }
            };
        } catch (\Exception $error) {
            throw new \Exception(sprintf('Call to undefined property %s::$%s', static::class, $name), 0, $error);
        }
    }

    /**
     * Allows static methods handled by `self::__callStatic()` to be accessible via object operator `->`.
     */
    public function __call(string $method, array $arguments)
    {
        return static::__callStatic($method, $arguments);
    }

    /**
     * Aliases getters and setter for class members.
     */
    public static function __callStatic(string $name, array $arguments)
    {
        try {
            if (preg_match('/^([gs]et|cut)([_]{0,1}[a-z0-9]+)$/i', $name, $matches)) {
                return forward_static_call_array(
                    [static::class, $matches[1]],
                    [static::getValidNameOrFail($matches[2]), ...$arguments]
                );
            }
        } catch (\Exception $error) {
            throw new \Exception(sprintf('Call to undefined method %s::%s', static::class, $name), 0, $error);
        }
    }
}
