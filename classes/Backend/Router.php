<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend;

use MAKS\Velox\Backend\Config;
use MAKS\Velox\Helper\Misc;

/**
 * A class that serves as a router and an entry point for the application.
 *
 * Example:
 * ```
 * // register a middleware
 * Router::middleware('/pages/{pageId}', function ($path, $match, $previous) {
 *      return 'I am working as expected!';
 * }, 'POST');
 *
 * // register a route handler
 * Router::route('/pages/{pageId}', function ($path, $match, $previous) {
 *      return sprintf('Hi from "%s" handler, Page ID is: %s, also the middleware said: %s', $path, $match, $previous ?? 'Nothing!');
 * }, ['GET', 'POST']);
 *
 * // register a route handler using an HTTP verb
 * Router::get('/another-page', function () {
 *      return View::render('another-page');
 * });
 *
 * // register handler for 404
 * Router::handleRouteNotFound(function ($path) {
 *      // forward the request to some route.
 *      Router::forward('/');
 * });
 *
 * // register handler for 405
 * Router::handleMethodNotAllowed(function ($path, $method) {
 *      // redirect the request to some URL.
 *      Router::redirect('/some-page');
 * });
 *
 * // start the application
 * Router::start();
 * ```
 *
 * @method static self get(string $expression, callable $handler)
 * @method static self post(string $expression, callable $handler)
 * @method static self put(string $expression, callable $handler)
 * @method static self patch(string $expression, callable $handler)
 * @method static self update(string $expression, callable $handler)
 * @method static self delete(string $expression, callable $handler)
 * @method static self any(string $expression, callable $handler)
 *
 * @since 1.0.0
 * @api
 */
class Router
{
    /**
     * The default values of class parameters.
     *
     * @var array
     */
    public const DEFAULTS = [
        'base' => '/',
        'allowMultiMatch' => true,
        'caseMatters' => false,
        'slashMatters' => true,
    ];

    /**
     * The default values of class parameters.
     *
     * @var array
     */
    public const SUPPORTED_METHODS = [
        'GET',
        'HEAD',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'CONNECT',
        'OPTIONS',
        'TRACE'
    ];


    /**
     * The parameters the application started with.
     */
    private static ?array $params = null;

    /**
     * The current base URL of the application.
     */
    protected static ?string $base = null;

    /**
     * The current registered routes.
     */
    protected static array $routes = [];

    /**
     * @var callable|null
     */
    protected static $routeNotFoundCallback = null;

    /**
     * @var callable|null
     */
    protected static $methodNotAllowedCallback = null;


    /**
     * Registers a handler for a route.
     *
     * @param string $expression A route like `/page`, `/page/{id}` (`id` is required), or `/page/{id?}` (`id` is optional). For more flexibility, pass en expression like `/page/([\d]+|[0-9]*)` (regex capture group).
     * @param callable $handler A function to call if route has matched. It will be passed the current `$path`, the `$match` or `...$match` from the expression if there was any, and lastly the `$previous` result (the return of the last middleware or route with a matching expression) if `$allowMultiMatch` is set to `true`.
     * @param string|string[] $method Either a string or an array of the allowed method.
     *
     * @return static
     */
    public static function handle(string $expression, callable $handler, $method = 'GET')
    {
        static::$routes[] = [
            'expression' => $expression,
            'handler' => $handler,
            'arguments' => [],
            'method' => $method
        ];

        return new static();
    }

    /**
     * Registers a middleware for a route. This method has no effect if `$allowMultiMatch` is set to `false`.
     * Note that middlewares must be registered before routes in order to work correctly.
     * This method is just an alias for `self::handle()`.
     *
     * @param string $expression A route like `/page`, `/page/{id}` (`id` is required), or `/page/{id?}` (`id` is optional). For more flexibility, pass en expression like `/page/([\d]+|[0-9]*)` (regex capture group).
     * @param callable $handler A function to call if route has matched. It will be passed the current `$path`, the `$match` or `...$match` from the expression if there was any, and lastly the `$previous` result (the return of the last middleware or route with a matching expression) if `$allowMultiMatch` is set to `true`.
     * @param string|string[] $method Either a string or an array of the allowed method.
     *
     * @return static
     */
    public static function middleware(string $expression, callable $handler, $method = 'GET')
    {
        return static::handle($expression, $handler, $method);
    }

    /**
     * Redirects the request to another route.
     * Note that this function will exit the script (code that comes after it will not be executed).
     *
     * @param string $to A route like `/page`.
     *
     * @return void
     */
    public static function redirect(string $to): void
    {
        if (filter_var($to, FILTER_VALIDATE_URL)) {
            $header = sprintf('Location: %s', $to);
        } else {
            $scheme = static::isHttpsCompliant() ? 'https' : 'http';
            $host   = static::getServerHost();
            $path   = static::$base . '/' . $to;
            $path   = trim(preg_replace('/(\/+)/', '/', $path), '/');

            $header = sprintf('Location: %s://%s/%s', $scheme, $host, $path);
        }

        header($header, true, 302);

        exit;
    }

    /**
     * Forwards the request to another route.
     * Note that this function will exit the script (code that comes after it will not be executed).
     *
     * @param string $to A route like `/page`.
     *
     * @return void
     */
    public static function forward(string $to): void
    {
        $base = static::$base ?? '';
        $path = trim($base, '/') . '/' . ltrim($to, '/');

        static::setRequestUri($path);
        static::start(...self::$params);

        exit;
    }

    /**
     * Registers 404 handler.
     *
     * @param callable $handler The handler to use. It will be passed the current `$path` and the current `$method`.
     *
     * @return static
     */
    public static function handleRouteNotFound(callable $handler)
    {
        static::$routeNotFoundCallback = $handler;

        return new static();
    }

    /**
     * Registers 405 handler.
     *
     * @param callable $handler The handler to use. It will be passed the current `$path`.
     *
     * @return static
     */
    public static function handleMethodNotAllowed(callable $handler)
    {
        static::$methodNotAllowedCallback = $handler;

        return new static();
    }

    /**
     * Starts the router.
     *
     * @param string|null [optional] $base App base path, this will prefix all routes.
     * @param bool|null [optional] $allowMultiMatch Whether the router should execute handlers of all matches. Useful to make middleware-like functionality, the first match will act as a middleware.
     * @param bool|null [optional] $caseMatters Whether the route matching should be case sensitive or not.
     * @param bool|null [optional] $slashMatters Whether trailing slash should be taken in consideration with route matching or not.
     *
     * @return void
     *
     * @throws \Exception If route handler failed or returned false.
     */
    public static function start(?string $base = null, ?bool $allowMultiMatch = null, ?bool $caseMatters = null, ?bool $slashMatters = null): void
    {
        self::$params = func_get_args();

        $routerConfig = Config::get('router');

        $base            ??= $routerConfig['base'];
        $allowMultiMatch ??= $routerConfig['allowMultiMatch'];
        $caseMatters     ??= $routerConfig['caseMatters'];
        $slashMatters    ??= $routerConfig['slashMatters'];

        $url  = static::getParsedUrl();
        $base = static::$base = trim($base, '/');
        $path = '/';

        if (isset($url['path'])) {
            $path = $url['path'];
            if (!$slashMatters && $path !== $base . '/') {
                $path = rtrim($path, '/');
            }
        }

        $path = urldecode($path);

        $pathMatchFound = false;
        $routeMatchFound = false;
        $result = null;

        foreach (static::$routes as &$route) {
            if ($base !== '' || $base !== '/') {
                $route['expression'] = $base . $route['expression'];
            }

            $routePlaceholderRegex = '/{([a-z0-9_\-\.?]+)}/i';
            if (preg_match($routePlaceholderRegex, $route['expression'])) {
                $routeMatchRegex = strpos($route['expression'], '?}') !== false ? '(.*)?' : '(.+)';
                $route['expression'] = preg_replace(
                    $routePlaceholderRegex,
                    $routeMatchRegex,
                    $route['expression']
                );
            }

            $routeMatchRegex = sprintf('<^%s$>%s', $route['expression'], ($caseMatters ? 'iu' : 'u'));
            if (preg_match($routeMatchRegex, $path, $matches, PREG_UNMATCHED_AS_NULL)) {
                $pathMatchFound = true;

                $allowedMethods = (array)$route['method'];
                foreach ($allowedMethods as $allowedMethod) {
                    $currentMethod = static::getRequestMethod();
                    if (strtoupper($currentMethod) !== strtoupper($allowedMethod)) {
                        continue;
                    }

                    $routeMatchFound = true;

                    $route['arguments'] = array_merge($route['arguments'], $matches ?? [$path]);
                    $route['arguments'] = array_filter($route['arguments']);
                    if (count($route['arguments']) > 1) {
                        array_push($route['arguments'], $result);
                    } else {
                        array_push($route['arguments'], null, $result);
                    }

                    $result = call_user_func_array($route['handler'], $route['arguments']);

                    if ($result === false) {
                        throw new \Exception("Something went wrong when trying to respond to '{$path}'! Check the handler for this route");
                    }

                    header(static::getServerProtocol() . ' 200 OK', false, 200);
                }
            }

            if ($routeMatchFound && !$allowMultiMatch) {
                break;
            }
        }

        unset($route);

        if (!$routeMatchFound) {
            $result = 'The route is not found, or the request method is not allowed!';

            if ($pathMatchFound) {
                if (static::$methodNotAllowedCallback) {
                    $result = call_user_func(static::$methodNotAllowedCallback, $path, static::getRequestMethod());

                    header(static::getServerProtocol() . ' 405 Method Not Allowed', true, 405);
                }

                Misc::log(
                    'Responded with 405 to the request for "{path}" with method "{method}"',
                    ['path' => $path, 'method' => static::getRequestMethod()],
                    'system'
                );
            } else {
                if (static::$routeNotFoundCallback) {
                    $result = call_user_func(static::$routeNotFoundCallback, $path);

                    header(static::getServerProtocol() . ' 404 Not Found', true, 404);
                }

                Misc::log(
                    'Responded with 404 to the request for "{path}"',
                    ['path' => $path],
                    'system'
                );
            }
        }

        echo $result;
    }

    /**
     * Returns query parameters.
     *
     * @return array
     */
    public static function getParsedQuery(): array
    {
        $url = static::getParsedUrl();

        parse_str($url['query'] ?? '', $query);

        return $query;
    }

    /**
     * Returns components of the current URL.
     *
     * @return array
     */
    public static function getParsedUrl(): array
    {
        $uri = static::getRequestUri();

        // remove double slashes as they make parse_url() fail
        $url = preg_replace('/(\/+)/', '/', $uri);
        $url = parse_url($url);

        return $url;
    }

    /**
     * Returns `php://input`.
     *
     * @return string
     */
    public static function getInput(): string
    {
        return file_get_contents('php://input') ?: '';
    }

    /**
     * Returns the currently requested route.
     *
     * @return string
     */
    public static function getCurrent(): string
    {
        return static::getRequestUri();
    }

    protected static function getServerHost(): string
    {
        return $_SERVER['HTTP_HOST'];
    }

    protected static function getServerProtocol(): string
    {
        return $_SERVER['SERVER_PROTOCOL'];
    }

    protected static function getRequestMethod(): string
    {
        $method = $_POST['_method'] ?? '';
        $methods = static::SUPPORTED_METHODS;
        $methodAllowed = in_array(
            strtoupper($method),
            array_map('strtoupper', $methods)
        );

        if ($methodAllowed) {
            static::setRequestMethod($method);
        }

        return $_SERVER['REQUEST_METHOD'];
    }

    protected static function setRequestMethod(string $method): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
    }

    protected static function getRequestUri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    protected static function setRequestUri(string $uri): void
    {
        $_SERVER['REQUEST_URI'] = $uri;
    }

    protected static function isHttpsCompliant(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    /**
     * Returns all registered routes with their `expression`, `handler`, `arguments`, and `method`.
     *
     * @return array
     */
    public static function getRegisteredRoutes(): array
    {
        return static::$routes;
    }

    /**
     * Aliases `self::handle()` method with common HTTP verbs.
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $methods = static::SUPPORTED_METHODS;
        $methodAllowed = in_array(
            strtoupper($method),
            array_map('strtoupper', ['ANY', ...$methods])
        );

        if (!$methodAllowed) {
            $class = static::class;
            throw new \Exception("Call to undefined method {$class}::{$method}()");
        }

        if (count($arguments) > 2) {
            $arguments = array_slice($arguments, 0, 2);
        }

        if (strtoupper($method) === 'ANY') {
            array_push($arguments, $methods);
        } else {
            array_push($arguments, $method);
        }

        return static::handle(...$arguments);
    }

    /**
     * Allows static methods handled by self::__callStatic() to be accessible via object operator `->`.
     */
    public function __call(string $method, array $arguments)
    {
        return self::__callStatic($method, $arguments);
    }
}
