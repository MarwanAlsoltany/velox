<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Helper;

/**
 * A class that serves as a holder for various miscellaneous utility function.
 *
 * @package Velox\Helper
 * @since 1.0.0
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
                if (!isset($data[$part])) {
                    return $default;
                }

                $data = &$data[$part];
            }

            return $data ?? $default;
        }

        return $data[$key] ?? $default;
    }

    /**
     * Sets a value of an array via dot-notation.
     *
     * @param array $array The array to set the value in.
     * @param string $key The dotted key representation.
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
     * Cuts a value of an array via dot-notation, the value will be returned and the key will be unset from the array.
     *
     * @param array $array The array to cut the value from.
     * @param string $key The dotted key representation.
     * @param mixed $default [optional] The default fallback value.
     *
     * @return mixed The requested value if found otherwise the default parameter.
     */
    public static function cutArrayValueByKey(array &$array, string $key, $default = null)
    {
        $cut = function (&$array, $key) use ($default, &$cut) {
            if (!is_array($array)) {
                return;
            }

            if (count($parts = explode('.', $key)) > 1) {
                return $cut($array[$parts[0]], implode('.', array_slice($parts, 1)));
            }

            $value = $array[$key] ?? $default;

            unset($array[$key]);

            return $value;
        };

        return $cut($array, $key, $default);
    }


    /**
     * Gets a private, protected, or public property (default, static, or constant) of an object.
     *
     * @param object|string $object Class instance, or FQN if static.
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
     * @param object|string $object Class instance, or FQN if static.
     * @param string $property Property name.
     * @param mixed $value Property value.
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
     * @param object|string $object Class instance, or FQN if static.
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
     * Transforms the case/content of a string by applying a one or more of the 26 available transformations.
     * The transformations are applied in the order they are specified.
     * Available transformations:
     * - `clean`: discards all punctuations and meta-characters (@#%&$^*+-=_~:;,.?!(){}[]|/\\'"\`), separates concatenated words [`ExampleString-num.1`, `Example String num 1`].
     * - `alnum`: removes every thing other that english letters, numbers and spaces. [`Example@123` -> `Example123`]
     * - `alpha`: removes every thing other that english letters. [`Example123` -> `Example`]
     * - `numeric`: removes every thing other that numbers. [`Example123` -> `123`]
     * - `slug`: lowercase, all letters to their A-Z representation (transliteration), spaces to dashes, no special characters (URL-safe) [`Example (String)` -> `example-string`].
     * - `title`: titlecase [`example string` -> `Example String`].
     * - `sentence`: lowercase, first letter uppercase [`exampleString` -> `Example string`].
     * - `lower`: lowercase [`Example String` -> `example string`].
     * - `upper`: uppercase [`Example String` -> `EXAMPLE STRING`].
     * - `pascal`: titlecase, no spaces [`example string` -> `ExampleString`].
     * - `camel`: titlecase, no spaces, first letter lowercase [`example string` -> `exampleString`].
     * - `constant`: uppercase, spaces to underscores [`Example String` -> `EXAMPLE_STRING`].
     * - `cobol`: uppercase, spaces to dashes [`example string` -> `EXAMPLE-STRING`].
     * - `train`: titlecase, spaces to dashes [`example string` -> `Example-String`].
     * - `snake`: lowercase, spaces to underscores [`Example String` -> `example_string`].
     * - `kebab`: lowercase, spaces to dashes [`Example String` -> `example-string`].
     * - `dot`: lowercase, spaces to dots [`Example String` -> `example.string`].
     * - `spaceless`: removes any whitespaces [`Example String` -> `ExampleString`].
     * - A built-in function name from this list can also be used: `strtolower`, `strtoupper`, `lcfirst`, `ucfirst`, `ucwords`, `trim`, `ltrim`, `rtrim`.
     *
     * NOTE: Unknown transformations will be ignored silently.
     *
     * NOTE: The subject (string) loses some of its characteristics when a transformation is applied,
     * that means reversing the transformations will not guarantee getting the old subject back.
     *
     * @param string $subject The string to transform.
     * @param string ...$transformations One or more transformations to apply.
     *
     * @return string The transformed string.
     */
    public static function transform(string $subject, string ...$transformations): string
    {
        $transliterations = 'Any-Latin;Latin-ASCII;NFD;NFC;Lower();[:NonSpacing Mark:] Remove;[:Punctuation:] Remove;[:Other:] Remove;[\u0080-\u7fff] Remove;';

        static $cases = null;

        if ($cases === null) {
            $cases = [
                'clean'     => fn ($string) => static::transform(preg_replace(['/[^\p{L}\p{N}\s]+/', '/(?<!^)[A-Z]/', '/[\s]+/'], [' ', ' $0', ' '], $string), 'trim'),
                'alnum'     => fn ($string) => static::transform(preg_replace('/[^a-zA-Z0-9 ]+/', '', $string), 'trim'),
                'alpha'     => fn ($string) => static::transform(preg_replace('/[^a-zA-Z]+/', '', $string), 'trim'),
                'numeric'   => fn ($string) => static::transform(preg_replace('/[^0-9]+/', '', $string), 'trim'),
                'slug'      => fn ($string) => static::transform(transliterator_transliterate($transliterations, preg_replace('/-+/', ' ', $string)), 'kebab'),
                'sentence'  => fn ($string) => static::transform($string, 'clean', 'lower', 'ucfirst'),
                'title'     => fn ($string) => static::transform($string, 'clean', 'ucwords'),
                'pascal'    => fn ($string) => static::transform($string, 'title', 'spaceless'),
                'camel'     => fn ($string) => static::transform($string, 'pascal', 'lcfirst'),
                'constant'  => fn ($string) => static::transform($string, 'snake', 'upper'),
                'cobol'     => fn ($string) => strtr(static::transform($string, 'constant'), ['_' => '-']),
                'train'     => fn ($string) => strtr(static::transform($string, 'title'), [' ' => '-']),
                'snake'     => fn ($string) => strtr(static::transform($string, 'clean', 'lower'), [' ' => '_']),
                'kebab'     => fn ($string) => strtr(static::transform($string, 'clean', 'lower'), [' ' => '-']),
                'dot'       => fn ($string) => strtr(static::transform($string, 'clean', 'lower'), [' ' => '.']),
                'spaceless' => fn ($string) => preg_replace('/[\s]+/', '', $string),
                'lower'     => fn ($string) => mb_strtolower($string),
                'upper'     => fn ($string) => mb_strtoupper($string),
                '*'         => ['strtolower', 'strtoupper', 'lcfirst', 'ucfirst', 'ucwords', 'trim', 'ltrim', 'rtrim'],
            ];
        }

        $functions = array_flip((array)$cases['*']);

        foreach ($transformations as $name) {
            $name = strtolower($name);

            if (isset($cases[$name]) && $name !== '*') {
                $subject = $cases[$name]($subject);
            } else {
                $index = $functions[$name] ?? -1;
                if ($index >= 0 && function_exists($cases['*'][$index])) {
                    $subject = $cases['*'][$index]($subject);
                }
            }
        }

        return $subject;
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
