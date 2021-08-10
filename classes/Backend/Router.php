<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend;

use MAKS\Velox\App;
use MAKS\Velox\Backend\Config;
use MAKS\Velox\Backend\Globals;
use MAKS\Velox\Frontend\HTML;

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
 * @method static self head(string $expression, callable $handler)
 * @method static self post(string $expression, callable $handler)
 * @method static self put(string $expression, callable $handler)
 * @method static self patch(string $expression, callable $handler)
 * @method static self delete(string $expression, callable $handler)
 * @method static self connect(string $expression, callable $handler)
 * @method static self options(string $expression, callable $handler)
 * @method static self trace(string $expression, callable $handler)
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
     * Supported HTTP methods.
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
     * The currently requested path.
     */
    protected static ?string $path = null;

    /**
     * The currently registered routes.
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
     * Registers a route.
     *
     * @param string $type
     * @param string $expression
     * @param callable $handler
     * @param array $arguments
     * @param string|array $method
     *
     * @return static
     */
    private static function registerRoute(string $type, string $expression, callable $handler, array $arguments, $method)
    {
        $route = [
            'type' => $type,
            'expression' => $expression,
            'handler' => $handler,
            'arguments' => $arguments,
            'method' => $method
        ];

        static::$routes[] = &$route;

        return new static();
    }

    /**
     * Registers a handler for a route.
     *
     * @param string $expression A route like `/page`, `/page/{id}` (`id` is required), or `/page/{id?}` (`id` is optional). For more flexibility, pass en expression like `/page/([\d]+|[0-9]*)` (regex capture group).
     * @param callable $handler A function to call if route has matched. It will be passed the current `$path`, the `$match` or `...$match` from the expression if there was any, and lastly the `$previous` result (the return of the last middleware or route with a matching expression) if `$allowMultiMatch` is set to `true`.
     * @param string|string[] $method [optional] Either a string or an array of the allowed method.
     *
     * @return static
     */
    public static function handle(string $expression, callable $handler, $method = 'GET')
    {
        return static::registerRoute('handler', $expression, $handler, [], $method);
    }

    /**
     * Registers a middleware for a route. This method has no effect if `$allowMultiMatch` is set to `false`.
     * Note that middlewares must be registered before routes in order to work correctly.
     * This method is just an alias for `self::handle()`.
     *
     * @param string $expression A route like `/page`, `/page/{id}` (`id` is required), or `/page/{id?}` (`id` is optional). For more flexibility, pass en expression like `/page/([\d]+|[0-9]*)` (regex capture group).
     * @param callable $handler A function to call if route has matched. It will be passed the current `$path`, the `$match` or `...$match` from the expression if there was any, and lastly the `$previous` result (the return of the last middleware or route with a matching expression) if `$allowMultiMatch` is set to `true`.
     * @param string|string[] $method [optional] Either a string or an array of the allowed method.
     *
     * @return static
     */
    public static function middleware(string $expression, callable $handler, $method = 'GET')
    {
        return static::registerRoute('middleware', $expression, $handler, [], $method);
    }

    /**
     * Redirects the request to another route.
     * Note that this function will exit the script (code that comes after it will not be executed).
     *
     * @param string $to A route like `/page` or a URL like `http://domain.tld`.
     *
     * @return void
     */
    public static function redirect(string $to): void
    {
        if (filter_var($to, FILTER_VALIDATE_URL)) {
            $header = sprintf('Location: %s', $to);
        } else {
            $scheme = Globals::getServer('HTTPS') == 'on' ? 'https' : 'http';
            $host   = Globals::getServer('HTTP_HOST');
            $path   = static::$base . '/' . $to;
            $path   = trim(preg_replace('/(\/+)/', '/', $path), '/');

            $header = sprintf('Location: %s://%s/%s', $scheme, $host, $path);
        }

        header($header, true, 302);

        exit; // @codeCoverageIgnore
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

        Globals::setServer('REQUEST_URI', $path);

        static::start(...self::$params);

        exit; // @codeCoverageIgnore
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

        [$base, $allowMultiMatch, $caseMatters, $slashMatters] = static::getValidParameters($base, $allowMultiMatch, $caseMatters, $slashMatters);

        static::$base = $base = '/' . trim($base, '/');
        static::$path = $path = static::getRoutePath($slashMatters);

        $routeMatchFound = false;
        $pathMatchFound  = false;
        $result = null;

        foreach (static::$routes as &$route) {
            $expression = $base === '/' ? $route['expression'] : sprintf('%s/%s', $base, ltrim($route['expression'], '/'));

            $regex = static::getRouteRegex($expression, $slashMatters, $caseMatters);
            if (preg_match($regex, $path, $matches, PREG_UNMATCHED_AS_NULL)) {
                $pathMatchFound = true;

                $currentMethod  = static::getRequestMethod();
                $allowedMethods = (array)$route['method'];
                foreach ($allowedMethods as $allowedMethod) {
                    if (strtoupper($currentMethod) !== strtoupper($allowedMethod)) {
                        continue;
                    }

                    $routeMatchFound = true;

                    $route['arguments'] = static::getRouteArguments($route['arguments'], $matches, $result);

                    $result = call_user_func_array($route['handler'], $route['arguments']);

                    if ($result === false) {
                        throw new \Exception("Something went wrong when trying to respond to '{$path}'! Check the handler for this route");
                    }
                }
            }

            if ($routeMatchFound && !$allowMultiMatch) {
                break;
            }
        }

        unset($route);

        static::echoResponse($routeMatchFound, $pathMatchFound, $result);
    }

    /**
     * Returns valid parameters for `self::start()` by validating the passed parameters and adding the deficiency from router config.
     *
     * @param string|null $base
     * @param bool|null $allowMultiMatch
     * @param bool|null $caseMatters
     * @param bool|null $slashMatters
     *
     * @return array
     */
    private static function getValidParameters(?string $base, ?bool $allowMultiMatch, ?bool $caseMatters, ?bool $slashMatters): array
    {
        $routerConfig = Config::get('router');

        $base            ??= $routerConfig['base'];
        $allowMultiMatch ??= $routerConfig['allowMultiMatch'];
        $caseMatters     ??= $routerConfig['caseMatters'];
        $slashMatters    ??= $routerConfig['slashMatters'];

        return [
            $base,
            $allowMultiMatch,
            $caseMatters,
            $slashMatters,
        ];
    }

    /**
     * Returns a valid decoded route path.
     *
     * @param string $base
     *
     * @return string
     */
    private static function getRoutePath(bool $slashMatters): string
    {
        $url = static::getParsedUrl();

        $path = '/';
        if (isset($url['path'])) {
            $path = $url['path'];
            $path = !$slashMatters && $path !== '/' ? rtrim($path, '/') : $path;
        }

        return urldecode($path);
    }

    /**
     * Returns a valid route regex.
     *
     * @param string $expression
     * @param bool $slashMatters
     * @param bool $caseMatters
     *
     * @return string
     */
    private static function getRouteRegex(string $expression, bool $slashMatters, bool $caseMatters): string
    {
        $routePlaceholderRegex = '/{([a-z0-9_\-\.?]+)}/i';
        if (preg_match($routePlaceholderRegex, $expression)) {
            $routeMatchRegex = strpos($expression, '?}') !== false ? '(.*)?' : '(.+)';
            $expression = preg_replace(
                $routePlaceholderRegex,
                $routeMatchRegex,
                $expression
            );
        }
        return sprintf(
            '<^%s$>%s',
            (!$slashMatters && $expression !== '/' ? rtrim($expression, '/') : $expression),
            (!$caseMatters ? 'iu' : 'u')
        );
    }

    /**
     * Returns valid arguments for route handler in the order that the handler expect.
     *
     * @param array $current
     * @param array $matches
     * @param mixed $result
     *
     * @return array
     */
    private static function getRouteArguments(array $current, array $matches, $result): array
    {
        $arguments = array_merge($current, $matches);
        $arguments = array_filter($arguments);
        if (count($arguments) > 1) {
            array_push($arguments, $result);
        } else {
            array_push($arguments, null, $result);
        }

        return $arguments;
    }

    /**
     * Echos the response according to the passed parameters.
     *
     * @param bool $routeMatchFound
     * @param bool $pathMatchFound
     * @param mixed $result
     *
     * @return void
     */
    private static function echoResponse(bool $routeMatchFound, bool $pathMatchFound, $result): void
    {
        $code = 200;

        if (!$routeMatchFound) {
            $code   = $pathMatchFound ? 405 : 404;
            $path   = static::$path;
            $method = static::getRequestMethod();

            $title = $code === 404
                ? sprintf('%d Not Found', $code)
                : sprintf('%d Not Allowed', $code);
            $message = $code === 404
                ? sprintf('The "%s" route is not found!', $path)
                : sprintf('The "%s" route is found, but the request method "%s" is not allowed!', $path, $method);

            $result = (new HTML())
                ->node('<!DOCTYPE html>')
                ->open('html', ['lang' => 'en'])
                    ->open('head')
                        ->title($title)
                        ->link(null, [
                            'href' => 'https://cdn.jsdelivr.net/npm/bulma@0.9.2/css/bulma.min.css',
                            'rel' => 'stylesheet'
                        ])
                    ->close()
                    ->open('body')
                        ->open('section', ['class' => 'section is-large has-text-centered'])
                            ->hr(null)
                            ->h1($title, ['class' => 'title is-1 is-spaced has-text-danger'])
                            ->h4($message, ['class' => 'subtitle'])
                            ->hr(null)
                            ->a('Home', ['class' => 'button is-success is-light', 'href' => '/'])
                            ->hr(null)
                        ->close()
                    ->close()
                ->close()
            ->return();

            if ($code === 404 && static::$routeNotFoundCallback !== null) {
                $result = (static::$routeNotFoundCallback)($path);
            }

            if ($code === 405 && static::$methodNotAllowedCallback !== null) {
                $result = (static::$methodNotAllowedCallback)($path, $method);
            }

            App::log("Responded with {$code} to the request for '{$path}' with method '{$method}'", null, 'system');
        }

        http_response_code($code);
        echo($result);
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
        $uri = Globals::getServer('REQUEST_URI');

        // remove double slashes as they make parse_url() fail
        $url = preg_replace('/(\/+)/', '/', $uri);
        $url = parse_url($url);

        return $url;
    }

    protected static function getRequestMethod(): string
    {
        $method = Globals::getPost('_method') ?? '';
        $methods = static::SUPPORTED_METHODS;
        $methodAllowed = in_array(
            strtoupper($method),
            array_map('strtoupper', $methods)
        );

        if ($methodAllowed) {
            Globals::setServer('REQUEST_METHOD', $method);
        }

        return Globals::getServer('REQUEST_METHOD');
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
     * Class constructor.
     */
    final public function __construct()
    {
        // prevent overwriting constructor in subclasses to allow to use
        // "return new static()" without caring about dependencies.
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
        return static::__callStatic($method, $arguments);
    }
}
