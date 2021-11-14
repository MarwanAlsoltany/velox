<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Frontend;

use MAKS\Velox\Backend\Event;
use MAKS\Velox\Backend\Config;
use MAKS\Velox\Helper\Misc;

/**
 * A class that serves as an abstracted data bag/store that is accessible via dot-notation.
 *
 * Example:
 * ```
 * // get all data
 * $allData = Data::getAll();
 *
 * // check for variable availability
 * $someVarExists = Data::has('someVar');
 *
 * // get a specific variable or fall back to a default value
 * $someVar = Data::get('someVar', 'fallbackValue');
 *
 * // set a specific variable value
 * Data::set('someNewVar.someKey', 'someValue');
 * ```
 *
 * @since 1.0.0
 * @api
 */
class Data
{
    /**
     * This event will be dispatched when the data is loaded.
     * This event will be passed a reference to the data array.
     *
     * @var string
     */
    public const ON_LOAD = 'data.on.load';


    /**
     * The currently loaded data.
     */
    protected static array $bag = [];


    /**
     * Loads the data from system configuration.
     *
     * @return void
     */
    protected static function load(): void
    {
        if (empty(static::$bag)) {
            static::$bag = (array)Config::get('data', static::$bag);

            // make `Config::$config['data']` points to `Data::$bag` (the same array in memory)
            $config = Config::getAll();
            $config['data'] = &static::$bag;
            Misc::setObjectProperty(new Config(), 'config', $config);

            Event::dispatch(self::ON_LOAD, [&static::$bag]);
        }
    }

    /**
     * Checks whether a value of a key exists in `self::$bag` via dot-notation.
     *
     * @param string $key The dotted key representation.
     *
     * @return bool
     */
    public static function has(string $key): bool
    {
        static::load();

        $value = Misc::getArrayValueByKey(self::$bag, $key, null);

        return isset($value);
    }

    /**
     * Gets a value of a key from `self::$bag` via dot-notation.
     *
     * @param string $key The dotted key representation.
     * @param mixed $default [optional] The default fallback value.
     *
     * @return mixed The requested value or null.
     */
    public static function get(string $key, $default = null)
    {
        static::load();

        return Misc::getArrayValueByKey(self::$bag, $key, $default);
    }

    /**
     * Sets a value of a key in `self::$bag` via dot-notation.
     *
     * @param string $key The dotted key representation.
     * @param mixed $value The value to set.
     *
     * @return void
     */
    public static function set(string $key, $value): void
    {
        static::load();

        Misc::setArrayValueByKey(self::$bag, $key, $value);
    }

    /**
     * Returns the currently loaded data.
     *
     * @return array
     */
    public static function getAll(): ?array
    {
        static::load();

        return static::$bag;
    }
}
