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
use MAKS\Velox\Backend\Session;
use MAKS\Velox\Backend\Database;
use MAKS\Velox\Frontend\Data;
use MAKS\Velox\Frontend\View;
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Frontend\Path;
use MAKS\Velox\Helper\Dumper;
use MAKS\Velox\Helper\Misc;

/**
 * A class that serves as a basic service-container for VELOX.
 * This class has most VELOX classes as public properties:
 * - `$event`: Instance of the `Event` class.
 * - `$config`: Instance of the `Config` class.
 * - `$router`: Instance of the `Router` class.
 * - `$globals`: Instance of the `Globals` class.
 * - `$session`: Instance of the `Session` class.
 * - `$database`: Instance of the `Database` class.
 * - `$data`: Instance of the `Data` class.
 * - `$view`: Instance of the `View` class.
 * - `$html`: Instance of the `HTML` class.
 * - `$path`: Instance of the `Path` class.
 * - `$dumper`: Instance of the `Dumper` class.
 * - `$misc`: Instance of the `Misc` class.
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
 * @method static void handleException(\Throwable $expression) This function is available only at shutdown.
 * @method static void handleError(int $code, string $message, string $file, int $line) This function is available only at shutdown.
 *
 * @since 1.0.0
 */
class App
{
    public Event $event;

    public Config $config;

    public Router $router;

    public Globals $globals;

    public Session $session;

    public Database $database;

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
        $this->event    = new Event();
        $this->config   = new Config();
        $this->router   = new Router();
        $this->globals  = new Globals();
        $this->session  = new Session();
        $this->database = Database::instance();
        $this->data     = new Data();
        $this->view     = new View();
        $this->html     = new HTML();
        $this->path     = new Path();
        $this->dumper   = new Dumper();
        $this->misc     = new Misc();
        $this->methods  = [];
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

    /**
     * Aborts the current request and sends a response with the specified HTTP status code, title, and message.
     * An HTML page will be rendered with the specified title and message.
     * The title for the most common HTTP status codes (`200`, `403`, `404`, `405`, `500`, `503`) is already configured.
     *
     * @param int $code The HTTP status code.
     * @param string|null $title [optional] The title of the HTML page.
     * @param string|null $message [optional] The message of the HTML page.
     *
     * @return void
     *
     * @since 1.2.5
     */
    public static function abort(int $code, ?string $title = null, ?string $message = null): void
    {
        $http = [
            200 => 'OK',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Not Allowed',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        ];

        http_response_code($code);

        $title    = htmlspecialchars($title ?? $code . ' ' . $http[$code] ?? '', ENT_QUOTES, 'UTF-8');
        $message  = htmlspecialchars($message ?? '', ENT_QUOTES, 'UTF-8');

        (new HTML(false))
            ->node('<!DOCTYPE html>')
            ->open('html', ['lang' => 'en'])
                ->open('head')
                    ->title((string)$code)
                    ->link(null, [
                        'href' => 'https://cdn.jsdelivr.net/npm/bulma@latest/css/bulma.min.css',
                        'rel' => 'stylesheet'
                    ])
                ->close()
                ->open('body')
                    ->open('section', ['class' => 'section is-large has-text-centered'])
                        ->hr(null)
                        ->h1($title, ['class' => 'title is-1 is-spaced has-text-danger'])
                        ->condition(strlen($message))
                        ->h4($message, ['class' => 'subtitle'])
                        ->hr(null)
                        ->a('Reload', ['class' => 'button is-warning is-light', 'href' => 'javascript:location.reload();'])
                        ->entity('nbsp')
                        ->entity('nbsp')
                        ->a('Home', ['class' => 'button is-success is-light', 'href' => '/'])
                        ->hr(null)
                    ->close()
                ->close()
            ->close()
        ->echo();

        static::terminate();
    }


    /**
     * Terminates (exits) the PHP script.
     * This function is used instead of PHP `exit` to allow for testing `exit` without breaking the unit tests.
     *
     * @param int|string|null $status The exit status code/message.
     * @param bool $noShutdown Whether to not execute the shutdown function or not.
     *
     * @return void This function never returns. It will terminate the script.
     * @throws \Exception If `EXIT_EXCEPTION` is defined and truthy.
     *
     * @since 1.2.5
     */
    public static function terminate($status = null, bool $noShutdown = true): void
    {
        if (defined('EXIT_EXCEPTION') && EXIT_EXCEPTION) {
            throw new \Exception(empty($status) ? 'Exit' : 'Exit: ' . $status);
        }

        // @codeCoverageIgnoreStart
        if ($noShutdown) {
            // app shutdown function checks for this variable
            // to determine if it should exit, see bootstrap/loader.php
            Misc::setArrayValueByKey($GLOBALS, '_VELOX.TERMINATE', true);
        }

        exit($status);
        // @codeCoverageIgnoreEnd
    }
}
