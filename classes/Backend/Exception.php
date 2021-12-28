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
 * A class that serves as a base exception class with helpers to assist with errors/exceptions handling.
 *
 * Example:
 * ```
 * // throw an exception
 * $signature = 'YException:XException'; // YException extends YException and will get created if it does not exist.
 * Exception::throw($signature, $message, $code, $previous);
 *
 * // handle the passed callback in a safe context where errors get converted to exceptions
 * Exception::handle($callback, $signature, $message);
 *
 * // trigger an E_USER_* error, warning, notice, or deprecated with backtrace info
 * Exception::trigger($message, $severity);
 * ```
 *
 * @package Velox\Backend
 * @since 1.5.5
 * @api
 */
class Exception extends \Exception
{
    /**
     * Class constructor.
     * {@inheritDoc}
     *
     * @param string $message The Exception message.
     */
    public function __construct(string $message, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns a string representation of the exception object.
     *
     * @return string
     */
    public function __toString()
    {
        return Misc::interpolate('{class}: {message} [Code: {code}] {eol}{trace}{eol}', [
            'class'   => static::class,
            'code'    => $this->getCode(),
            'message' => $this->getMessage(),
            'trace'   => $this->getTraceAsString(),
            'eol'     => PHP_EOL,
        ]);
    }


    /**
     * Creates an exception class dynamically and returns its class FQN.
     *
     * @param string $signature
     *
     * @return string
     */
    final protected static function create(string $signature): string
    {
        if (class_exists($signature, false)) {
            return '\\' . $signature;
        }

        $namespace = static::class;
        $parent    = static::class;
        $class     = $signature = trim($signature, '\\');

        if (strpos($signature, ':') !== false) {
            [$class, $parent] = explode(':', $signature);

            if (strpos($class, '\\') !== false) {
                $namespace = implode('\\', explode('\\', $class, -1));
                $parts     = explode('\\', $class);
                $class     = $parts[count($parts) - 1];
            }

            $parent = class_exists($parent) && is_subclass_of($parent, \Exception::class)
                ? trim($parent, '\\')
                : static::class;
        }

        $content = Misc::interpolate(
            '<?php namespace {namespace}; class {class} extends \\{parent} { /* TEMP */ }',
            compact('namespace', 'class', 'parent')
        );

        $classFQN = Misc::interpolate(
            '\\{namespace}\\{class}',
            compact('namespace', 'class')
        );

        if (class_exists($classFQN, false)) {
            return $classFQN;
        }

        $file = tmpfile();
        $path = stream_get_meta_data($file)['uri'];

        fwrite($file, $content);

        require $path;

        fclose($file);

        return $classFQN;
    }

    /**
     * Throws an exception using the given signature.
     *
     * NOTE:
     * Exceptions thrown via this method will be created at runtime if they are not build-in or defined explicitly (actual classes).
     * This means that the catch block that catches them must type-hint a fully qualified class name, because the `use` statement
     * will trigger a call to the autoloader, and the autoloader may not know about the magic exception class at that point of time.
     *
     * @param string $signature Exception signature.
     *      This can be a class FQN like `SomeException` or `Namespace\SomeException` or a class FQN with a parent FQN like `Namespace\SomeException:RuntimeException`.
     *      Note that exception class will be created at runtime if it does not exist.
     * @param string $message [optional] Exception message. An auto-generated exception message will be created using backtrace if this is left empty.
     * @param int|string $code [optional] Exception code. The code will be casted into an integer.
     * @param \Exception|null $previous [optional] Previous exception.
     *
     * @return void
     *
     * @throws \Exception Throws the given or the created exception.
     */
    public static function throw(string $signature, ?string $message = null, $code = 0, \Exception $previous = null): void
    {
        $exception = static::create($signature);

        if ($message === null) {
            $trace   = Misc::backtrace(['file', 'line', 'class', 'function'], 1);
            $message = Misc::interpolate('{prefix}{suffix} in {file} on line {line} ', [
                'prefix'  => isset($trace['class']) ? "{$trace['class']}::" : '',
                'suffix'  => isset($trace['function']) ? "{$trace['function']}() failed!" : '',
                'file'    => $trace['file'],
                'line'    => $trace['line'],
            ]);
        }

        throw new $exception((string)$message, (int)$code, $previous);
    }

    /**
     * Handles the passed callback in a safe context where PHP errors (and exceptions) result in exceptions that can be caught.
     *
     * @param callable $callback The callback to be executed.
     * @param string $signature [optional] Exception signature.
     *      This can be a class FQN like `SomeException` or `Namespace\SomeException` or a class FQN with a parent FQN like `Namespace\SomeException:RuntimeException`.
     *      Note that exception class will be created at runtime if it does not exist.
     * @param string $message [optional] The exception message if the callback raised an error or throw an exception.
     *
     * @return void
     *
     * @throws \Exception
     */
    public static function handle(callable $callback, ?string $signature = null, ?string $message = null): void
    {
        static $handler = null;

        if ($handler === null) {
            $handler = function (int $code, string $message, string $file, int $line) {
                throw new \ErrorException($message, $code, E_ERROR, $file, $line);
            };
        }

        set_error_handler($handler, E_ALL);

        try {
            $callback();
        } catch (\Throwable $exception) {
            $message = $message ?? Misc::interpolate('{method}() failed in {file} on line {line}', [
                'method' => __METHOD__,
                'file'   => $exception->getFile(),
                'line'   => $exception->getLine(),
            ]);
            $message = $message . ': ' . $exception->getMessage();
            $code    = $exception->getCode();

            static::throw($signature ?? static::class, $message, $code, $exception);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Triggers a user-level error, warning, notice, or deprecation with backtrace info.
     *
     * @param string $message Error message.
     * @param int $severity Error severity (`E_USER_*` family error). Default and fallback is `E_USER_ERROR`.
     * - `E_USER_ERROR => 256`,
     * - `E_USER_WARNING => 512`,
     * - `E_USER_NOTICE => 1024`,
     * - `E_USER_DEPRECATED => 16384`.
     *
     * @return void
     */
    public static function trigger(string $message, int $severity = E_USER_ERROR): void
    {
        $trace = Misc::backtrace(['file', 'line'], 1);

        $error = Misc::interpolate('{message} in {file} on line {line} ', [
            'file'    => $trace['file'],
            'line'    => $trace['line'],
            'message' => $message,
        ]);

        $severities = [E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE, E_USER_DEPRECATED];

        trigger_error($error, in_array($severity, $severities, true) ? $severity : E_USER_ERROR);
    }
}
