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
 * @method static mixed getGet(string $key = null)
 * @method static mixed getPost(string $key = null)
 * @method static mixed getFiles(string $key = null)
 * @method static mixed getCookie(string $key = null)
 * @method static mixed getSession(string $key = null)
 * @method static mixed getRequest(string $key = null)
 * @method static mixed getServer(string $key = null)
 * @method static mixed getEnv(string $key = null)
 * @method static static setGet(string $key, $value)
 * @method static static setPost(string $key, $value)
 * @method static static setFiles(string $key, $value)
 * @method static static setCookie(string $key, $value)
 * @method static static setSession(string $key, $value)
 * @method static static setRequest(string $key, $value)
 * @method static static setServer(string $key, $value)
 * @method static static setEnv(string $key, $value)
 *
 * @since 1.0.0
 */
class Globals
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


    private static array $_GET;
    private static array $_POST;
    private static array $_FILES;
    private static array $_COOKIE;
    private static array $_SESSION;
    private static array $_REQUEST;
    private static array $_SERVER;
    private static array $_ENV;


    /**
     * Initializes class internal state from superglobals.
     *
     * @return void
     */
    public static function initialize(): void
    {
        static $isInitialized = false;

        if (!$isInitialized) {
            foreach (self::GLOBALS as $global) {
                global $$global;
                self::$$global = isset($$global) ? self::$$global = &$$global : [];
            }

            $isInitialized = true;
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
            return Misc::getArrayValueByKey(static::$$name, $key, null);
        }

        return static::$$name;
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

        Misc::setArrayValueByKey(static::$$name, $key, $value);

        return new static();
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
    public static function __callStatic(string $name, array $arguments)
    {
        try {
            if (preg_match('/^([gs]et)([_]{0,1}[a-z0-9]+)$/i', $name, $matches)) {
                return forward_static_call_array(
                    [static::class, $matches[1]],
                    [static::getValidNameOrFail($matches[2]), ...$arguments]
                );
            }
        } catch (\Exception $error) {
            throw new \Exception(sprintf('Call to undefined method %s::%s', static::class, $name), 0, $error );
        }
    }

    /**
     * Allows static methods handled by self::__callStatic() to be accessible via object operator `->`.
     */
    public function __call(string $method, array $arguments)
    {
        return static::__callStatic($method, $arguments);
    }
}
