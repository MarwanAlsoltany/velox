<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Helper;

use MAKS\Velox\Backend\Config;

/**
 * A class that serves as a holder for various miscellaneous utility function.
 */
final class Misc
{
    /**
     * Gets a value from an array via dot-notation.
     *
     * @param array &$array The array to get the value from.
     * @param string $key The dotted key representation.
     * @param mixed $default [optional] The default fallback value.
     *
     * @return mixed The requested value if found otherwise the default parameter.
     */
    public static function getArrayValueByKey(array &$array, string $key, $default = null)
    {
        if (!count($array)) {
            return $default;
        }

        $data = &$array;

        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);

            foreach ($parts as $part) {
                if (!array_key_exists($part, $data)) {
                    return $default;
                }

                $data = &$data[$part];
            }

            return $data;
        }

        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * Sets a value of an array via dot-notation.
     *
     * @param array $array The array to set the value in.
     * @param string $key The string key representation.
     * @param mixed $value The value to set.
     *
     * @return bool True on success.
     */
    public static function setArrayValueByKey(array &$array, string $key, $value): bool
    {
        if (!strlen($key)) {
            return false;
        }

        $parts = explode('.', $key);
        $lastPart = array_pop($parts);

        $data = &$array;

        if (!empty($parts)) {
            foreach ($parts as $part) {
                if (!isset($data[$part])) {
                    $data[$part] = [];
                }

                $data = &$data[$part];
            }
        }

        $data[$lastPart] = $value;

        return true;
    }


    /**
     * Gets a private, protected, or public property (default, static, or constant) of an object.
     *
     * @param object $object Class instance.
     * @param string $property Property name.
     *
     * @return mixed The property value.
     *
     * @throws \Exception On failure.
     */
    public static function getObjectProperty($object, string $property)
    {
        return \Closure::bind(function ($object, $property) {
            $return = null;

            try {
                $class = get_class($object);
                if (defined($class . '::' . $property)) {
                    $return = constant($class . '::' . $property);
                } elseif (isset($object::$$property)) {
                    $return = $object::$$property;
                } elseif (isset($object->{$property})) {
                    $return = $object->{$property};
                } else {
                    throw new \Exception("No default, static, or constant property with the name '{$property}' exists!");
                }
            } catch (\Exception $error) {
                throw new \Exception(sprintf('%s() failed!', __METHOD__), $error->getCode(), $error);
            }

            return $return;
        }, null, $object)($object, $property);
    }

    /**
     * Sets a private, protected, or public property (default or static) of an object.
     *
     * @param object $object Class instance.
     * @param string $property Property name.
     * @param string $value Property value.
     *
     * @return mixed The new property value.
     *
     * @throws \Exception On failure.
     */
    public static function setObjectProperty($object, string $property, $value)
    {
        return \Closure::bind(function ($object, $property, $value) {
            $return = null;

            try {
                if (isset($object::$$property)) {
                    $return = $object::$$property = $value;
                } elseif (isset($object->{$property})) {
                    $return = $object->{$property} = $value;
                } else {
                    throw new \Exception("No default, static, or constant property with the name '{$property}' exists!");
                }
            } catch (\Exception $error) {
                throw new \Exception(sprintf('%s() failed!', __METHOD__), $error->getCode(), $error);
            }

            return $return;
        }, null, $object)($object, $property, $value);
    }

    /**
     * Calls a private, protected, or public method on an object.
     *
     * @param object $object Class instance.
     * @param string $method Method name.
     * @param mixed ...$arguments
     *
     * @return mixed The function result, or false on error.
     *
     * @throws \Exception On failure or if the called function threw an exception.
     */
    public static function callObjectMethod($object, string $method, ...$arguments)
    {
        return \Closure::bind(function ($object, $method, $arguments) {
            try {
                return call_user_func_array([$object, $method], $arguments);
            } catch (\Exception $error) {
                throw new \Exception(sprintf('%s() failed!', __METHOD__), $error->getCode(), $error);
            }
        }, null, $object)($object, $method, $arguments);
    }

    /**
     * Interpolates context values into text placeholders.
     *
     * @param string $text The text to interpolate.
     * @param array $context An associative array like `['varName' => 'varValue']`.
     * @param string $placeholderIndicator The wrapper that indicate a variable. Max 2 chars, anything else will be ignored and "{}" will be used instead.
     *
     * @return string The interpolated string.
     */
    public static function interpolate(string $text, array $context = [], string $placeholderIndicator = '{}'): string
    {
        if (strlen($placeholderIndicator) !== 2) {
            $placeholderIndicator = '{}';
        }

        $replacements = [];
        foreach ($context as $key => $value) {
            // check that the value can be cast to string
            if (!is_array($value) && (!is_object($value) || method_exists($value, '__toString'))) {
                $replacements[$placeholderIndicator[0] . $key . $placeholderIndicator[1]] = $value;
            }
        }

        return strtr($text, $replacements);
    }

    /**
     * Returns the passed key(s) from the backtrace.
     *
     * @param null|string|string[] $pluck [optional] $pluck The key to to get as a string or an array of strings (keys) from this list `[file, line, function, class, type, args]`, passing `null` will return the entire backtrace.
     * @param int $offset [optional] The offset of the backtrace, passing `-1` will reference the last item in the backtrace.
     *
     * @return string|int|array|null A string or int if a string is passed, an array if an array or null is passed, and null if no match was found.
     */
    public static function backtrace($pluck = null, ?int $offset = 0)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
        $plucked = null;

        if ($pluck === null) {
            return $backtrace;
        }

        if ($offset === -1) {
            $offset = 0;
            $backtrace = array_reverse($backtrace);
        }

        if (count($backtrace) < $offset + 1) {
            return null;
        } elseif (is_string($pluck)) {
            $plucked = isset($backtrace[$offset][$pluck]) ? $backtrace[$offset][$pluck] : null;
        } elseif (is_array($pluck)) {
            $plucked = [];
            foreach ($pluck as $key) {
                !isset($backtrace[$offset][$key]) ?: $plucked[$key] = $backtrace[$offset][$key];
            }
        }

        return is_string($plucked) || is_array($plucked) && count($plucked, COUNT_RECURSIVE) ? $plucked : null;
    }
}
