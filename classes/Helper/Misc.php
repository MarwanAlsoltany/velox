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
     * Returns a normalized path based on OS.
     *
     * @param string $directory
     * @param string $filename
     * @param string $extension
     *
     * @return string
     */
    public static function getNormalizedPath(string $directory, string $filename, string $extension = ''): string
    {
        $filename = substr($filename, -strlen($extension)) === $extension ? $filename : $filename . $extension;
        $directory = $directory . '/';

        return preg_replace('/\/+|\\+/', DIRECTORY_SEPARATOR, $directory . $filename);
    }

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
        if (!strlen($key) || !count($array)) {
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
     * Interpolates context values into the message placeholders.
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

    /**
     * Logs a message to a file and generates it if it does not exist.
     *
     * @param string $message The message wished to be logged.
     * @param array|null $context An associative array of values where key = {key} in the message.
     * @param string|null $filename [optional] The name wished to be given to the file. If not provided the message will be logged in "autogenerated-{Ymd}.log".
     * @param string|null $directory [optional] The directory where the log file should be written. If not the file will be written in "/storage/logs/".
     *
     * @return bool True if message was written.
     */
    public static function log(string $message, ?array $context = [], ?string $filename = null, ?string $directory = null): bool
    {
        if (!Config::get('global.loggingEnabled')) {
            return true;
        }

        $hasPassed = false;

        if (!$filename) {
            $filename = 'autogenerated-' . date('Ymd');
        }

        if (!$directory) {
            $directory = BASE_PATH . '/storage/logs/';
        }

        $file = self::getNormalizedPath($directory, $filename, '.log');

        if (!file_exists($directory)) {
            mkdir($directory, 0744, true);
        }

        // create log file if it does not exist
        if (!is_file($file) && is_writable($directory)) {
            $signature = 'Created by ' . __METHOD__ . date('() \o\\n l jS \of F Y h:i:s A (Ymdhis)') . PHP_EOL . PHP_EOL;
            file_put_contents($file, $signature, 0, stream_context_create());
            chmod($file, 0775);
        }

        // write in the log file
        if (is_writable($file)) {
            clearstatcache(true, $file);
            // empty the file if it exceeds 64MB
            if (filesize($file) > 6.4e+7) {
                $stream = fopen($file, 'r');
                if (is_resource($stream)) {
                    $signature = fgets($stream) . 'For exceeding 64MB, it was overwritten on ' . date('l jS \of F Y h:i:s A (Ymdhis)') . PHP_EOL . PHP_EOL;
                    fclose($stream);
                    file_put_contents($file, $signature, 0, stream_context_create());
                    chmod($file, 0775);
                }
            }

            $timestamp = (new \DateTime())->format(DATE_ISO8601);
            $message   = self::interpolate($message, $context ?? []);

            $log = "$timestamp\t$message\n";

            $stream = fopen($file, 'a+');
            if (is_resource($stream)) {
                fwrite($stream, $log);
                fclose($stream);
                $hasPassed = true;
            }
        }

        return $hasPassed;
    }
}
