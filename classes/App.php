<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox;

use MAKS\Velox\Backend\Event;
use MAKS\Velox\Backend\Config;
use MAKS\Velox\Backend\Router;
use MAKS\Velox\Backend\Globals;
use MAKS\Velox\Frontend\Data;
use MAKS\Velox\Frontend\View;
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Frontend\Path;
use MAKS\Velox\Helper\Dumper;
use MAKS\Velox\Helper\Misc;

/**
 * A class that serves as a basic service-container for VELOX.
 * This class has all VELOX classes as public properties:
 *      - `$event`   = `Event::class`
 *      - `$config`  = `Config::class`
 *      - `$router`  = `Router::class`
 *      - `$globals` = `Globals::class`
 *      - `$data`    = `Data::class`
 *      - `$view`    = `View::class`
 *      - `$html`    = `HTML::class`
 *      - `$path`    = `Path::class`
 *      - `$dumper`  = `Dumper::class`
 *      - `$misc`    = `Misc::class`
 *
 * Example:
 * ```
 * // create an instance
 * $app = new App();
 * // get an instance of the `Router` class via public property access notation
 * $app->router->handle('/dump', 'dd');
 * // or via calling a method with the same name
 * $app->router()->handle('/dump', 'dd');
 * ```
 *
 * @since 1.0.0
 */
class App
{
    public Event $event;

    public Config $config;

    public Router $router;

    public Globals $globals;

    public Data $data;

    public View $view;

    public HTML $html;

    public Path $path;

    public Dumper $dumper;

    public Misc $misc;

    protected array $methods;

    protected static array $staticMethods;


    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->event   = new Event();
        $this->config  = new Config();
        $this->router  = new Router();
        $this->globals = new Globals();
        $this->data    = new Data();
        $this->view    = new View();
        $this->html    = new HTML();
        $this->path    = new Path();
        $this->dumper  = new Dumper();
        $this->misc    = new Misc();
        $this->methods = [];
    }

    public function __get(string $property)
    {
        $class = static::class;

        throw new \Exception("Call to undefined property {$class}::${$property}");
    }

    public function __call(string $method, array $arguments)
    {
        $class = static::class;

        try {
            return isset($this->methods[$method]) ? $this->methods[$method](...$arguments) : $this->{$method};
        } catch (\Exception $error) {
            throw new \Exception(
                "Call to undefined method {$class}::{$method}()",
                (int)$error->getCode(),
                $error
            );
        }
    }

    public static function __callStatic(string $method, array $arguments)
    {
        $class = static::class;

        if (!isset(static::$staticMethods[$method])) {
            throw new \Exception("Call to undefined static method {$class}::{$method}()");
        }

        return static::$staticMethods[$method](...$arguments);
    }


    /**
     * Extends the class using the passed callback.
     *
     * @param string $name Method name.
     * @param callable $callback The callback to use as method body.
     *
     * @return callable The created bound closure.
     */
    public function extend(string $name, callable $callback): callable
    {
        $method = \Closure::fromCallable($callback);
        $method = \Closure::bind($method, $this, $this);

        return $this->methods[$name] = $method;
    }

    /**
     * Extends the class using the passed callback.
     *
     * @param string $name Method name.
     * @param callable $callback The callback to use as method body.
     *
     * @return callable The created closure.
     */
    public static function extendStatic(string $name, callable $callback): callable
    {
        $method = \Closure::fromCallable($callback);
        $method = \Closure::bind($method, null, static::class);

        return static::$staticMethods[$name] = $method;
    }


    /**
     * Logs a message to a file and generates it if it does not exist.
     *
     * @param string $message The message wished to be logged.
     * @param array|null $context An associative array of values where array key = {key} in the message (context).
     * @param string|null $filename [optional] The name wished to be given to the file. If not provided `{global.logging.defaultFilename}` will be used instead.
     * @param string|null $directory [optional] The directory where the log file should be written. If not provided `{global.logging.defaultDirectory}` will be used instead.
     *
     * @return bool True on success (if the message was written).
     */
    public static function log(string $message, ?array $context = [], ?string $filename = null, ?string $directory = null): bool
    {
        if (!Config::get('global.logging.enabled', true)) {
            return true;
        }

        $hasPassed = false;

        if (!$filename) {
            $filename = Config::get('global.logging.defaultFilename', sprintf('autogenerated-%s', date('Ymd')));
        }

        if (!$directory) {
            $directory = Config::get('global.logging.defaultDirectory', BASE_PATH);
        }

        $file = Path::normalize($directory, $filename, '.log');

        if (!file_exists($directory)) {
            mkdir($directory, 0744, true);
        }

        // create log file if it does not exist
        if (!is_file($file) && is_writable($directory)) {
            $signature = 'Created by ' . __METHOD__ . date('() \o\\n l jS \of F Y h:i:s A (Ymdhis)') . PHP_EOL . PHP_EOL;
            file_put_contents($file, $signature, 0);
            chmod($file, 0775);
        }

        // write in the log file
        if (is_writable($file)) {
            clearstatcache(true, $file);
            // empty the file if it exceeds the configured file size
            $maxFileSize = Config::get('global.logging.maxFileSize', 6.4e+7);
            if (filesize($file) > $maxFileSize) {
                $stream = fopen($file, 'r');
                if (is_resource($stream)) {
                    $signature = fgets($stream) . 'For exceeding the configured {global.logging.maxFileSize}, it was overwritten on ' . date('l jS \of F Y h:i:s A (Ymdhis)') . PHP_EOL . PHP_EOL;
                    fclose($stream);
                    file_put_contents($file, $signature, 0);
                    chmod($file, 0775);
                }
            }

            $timestamp = (new \DateTime())->format(DATE_ISO8601);
            $message   = Misc::interpolate($message, $context ?? []);

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
