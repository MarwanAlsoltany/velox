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
use MAKS\Velox\Backend\Exception;
use MAKS\Velox\Backend\Event;
use MAKS\Velox\Backend\Config;
use MAKS\Velox\Backend\Globals;

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
 * Router::handle('/pages/{pageId}', function ($path, $match, $previous) {
 *      return sprintf('Hi from "%s" handler, Page ID is: %s, also the middleware said: %s', $path, $match, $previous ?? 'Nothing!');
 * }, ['GET', 'POST']);
 *
 * // register a route handler using an HTTP verb
 * Router::get('/another-page', function () {
 *      return View::render('another-page');
 * });
 *
 * // start the application
 * Router::start();
 * ```
 *
 * @package Velox\Backend
 * @since 1.0.0
 * @api
 *
 * @method static self get(string $expression, callable $handler) Handles a `GET` request method.
 * @method static self head(string $expression, callable $handler) Handles a `HEAD` request method.
 * @method static self post(string $expression, callable $handler) Handles a `POST` request method.
 * @method static self put(string $expression, callable $handler) Handles a `PUT` request method.
 * @method static self patch(string $expression, callable $handler) Handles a `PATCH` request method.
 * @method static self delete(string $expression, callable $handler) Handles a `DELETE` request method.
 * @method static self connect(string $expression, callable $handler) Handles a `CONNECT` request method.
 * @method static self options(string $expression, callable $handler) Handles a `OPTIONS` request method.
 * @method static self trace(string $expression, callable $handler) Handles a `TRACE` request method.
 * @method static self any(string $expression, callable $handler) Handles any request method.
 */
class Router
{
    /**
     * This event will be dispatched when a handler is registered.
     * This event will be passed a reference to the route config array.
     *
     * @var string
     */
    public const ON_REGISTER_HANDLER = 'router.on.registerHandler';

    /**
     * This event will be dispatched when a middleware is registered.
     * This event will be passed a reference to the route config array.
     *
     * @var string
     */
    public const ON_REGISTER_MIDDLEWARE = 'router.on.registerMiddleware';

    /**
     * This event will be dispatched when the router is started.
     * This event will be passed a reference to the router parameters.
     *
     * @var string
     */
    public const ON_START = 'router.on.start';

    /**
     * This event will be dispatched when a redirect is attempted.
     * This event will be passed the redirection path/URL and the status code.
     *
     * @var string
     */
    public const BEFORE_REDIRECT = 'router.before.redirect';

    /**
     * This event will be dispatched when a forward is attempted.
     * This event will be passed the forward path.
     *
     * @var string
     */
    public const BEFORE_FORWARD = 'router.before.forward';


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
        'allowAutoStart' => true,
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
     * Route type handler.
     *
     * @var string
     *
     * @since 1.5.2
     */
    protected const HANDLER_ROUTE = 'HANDLER';

    /**
     * Route type handler.
     *
     * @var string
     *
     * @since 1.5.2
     */
    protected const MIDDLEWARE_ROUTE = 'MIDDLEWARE';


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

        Event::dispatch('router.on.register' . ucfirst(strtolower($type)), [&$route]);

        return new static();
    }

    /**
     * Registers a handler for a route.
     *
     * @param string $expression A route like `/page`, `/page/{id}` (`id` is required), or `/page/{id?}` (`id` is optional), or `page*` (`*` is a wildcard for anything).
     *      For more flexibility, pass an expression like `/page/([\d]+|[0-9]*)` (regex capture group).
     * @param callable $handler A function to call if route has matched.
     *      It will be passed the current `$path`, the `$match` or `...$match` from the expression if there was any, and lastly the `$previous` result
     *      (the return of the last middleware or route with a matching expression) if `$allowMultiMatch` is set to `true`.
     * @param string|string[] $method [optional] Either a string or an array of the allowed method.
     *
     * @return static
     */
    public static function handle(string $expression, callable $handler, $method = 'GET')
    {
        return static::registerRoute(static::HANDLER_ROUTE, $expression, $handler, [], $method);
    }

    /**
     * Registers a middleware for a route. This method has no effect if `$allowMultiMatch` is set to `false`.
     *
     * @param string $expression A route like `/page`, `/page/{id}` (`id` is required), or `/page/{id?}` (`id` is optional), or `page*` (`*` is a wildcard for anything).
     *      For more flexibility, pass an expression like `/page/([\d]+|[0-9]*)` (regex capture group).
     * @param callable $handler A function to call if route has matched.
     *      It will be passed the current `$path`, the `$match` or `...$match` from the expression if there was any, and lastly the `$previous` result
     *      (the return of the last middleware or route with a matching expression) if `$allowMultiMatch` is set to `true`.
     * @param string|string[] $method [optional] Either a string or an array of the allowed method.
     *
     * @return static
     */
    public static function middleware(string $expression, callable $handler, $method = 'GET')
    {
        return static::registerRoute(static::MIDDLEWARE_ROUTE, $expression, $handler, [], $method);
    }

    /**
     * Redirects the request to another route.
     * Note that this function will exit the script (code that comes after it will not be executed).
     *
     * @param string $to A route like `/page` or a URL like `http://domain.tld`.
     * @param int $status [optional] The HTTP status code to send.
     *
     * @return void
     */
    public static function redirect(string $to, int $status = 302): void
    {
        Event::dispatch(self::BEFORE_REDIRECT, [$to, $status]);

        if (filter_var($to, FILTER_VALIDATE_URL)) {
            $header = sprintf('Location: %s', $to);
        } else {
            $scheme = Globals::getServer('HTTPS') == 'on' ? 'https' : 'http';
            $host   = Globals::getServer('HTTP_HOST');
            $path   = preg_replace('/(\/+)/', '/', static::$base . '/' . $to);
            $base   = Config::get('global.baseUrl', $scheme . '://' . $host);

            $header = sprintf('Location: %s/%s', trim($base, '/'), trim($path, '/'));
        }

        header($header, false, $status);

        App::terminate(); // @codeCoverageIgnore
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
        Event::dispatch(self::BEFORE_FORWARD, [$to]);

        $base = static::$base ?? '';
        $path = trim($base, '/') . '/' . ltrim($to, '/');

        Globals::setServer('REQUEST_URI', $path);

        static::start(...self::$params);

        App::terminate(); // @codeCoverageIgnore
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
     * @throws \LogicException If route handler failed or returned false.
     */
    public static function start(?string $base = null, ?bool $allowMultiMatch = null, ?bool $caseMatters = null, ?bool $slashMatters = null): void
    {
        self::$params = func_get_args();

        Event::dispatch(self::ON_START, [&self::$params]);

        Session::csrf()->check();

        [$base, $allowMultiMatch, $caseMatters, $slashMatters] = static::getValidParameters($base, $allowMultiMatch, $caseMatters, $slashMatters);

        static::$base = $base = '/' . trim($base, '/');
        static::$path = $path = static::getRoutePath($slashMatters);

        $routeMatchFound = false;
        $pathMatchFound  = false;
        $result = null;

        self::sort();

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
                        Exception::throw(
                            'InvalidResponseException:LogicException',
                            "Something went wrong when trying to respond to '{$path}'! " .
                            "Check the handler for this route, was expecting 'string' as a response but got 'false' instead"
                        );
                    }
                }
            }

            if ($routeMatchFound && !$allowMultiMatch) {
                break;
            }
        }

        unset($route);

        static::respond($result, $routeMatchFound, $pathMatchFound);
    }

    /**
     * Sorts registered routes to make middlewares come before handlers.
     *
     * @return void
     */
    private static function sort(): void
    {
        usort(static::$routes, function ($routeA, $routeB) {
            if ($routeA['type'] === static::MIDDLEWARE_ROUTE && $routeB['type'] !== static::MIDDLEWARE_ROUTE) {
                return -1;
            }

            if ($routeA['type'] !== static::MIDDLEWARE_ROUTE && $routeB['type'] === static::MIDDLEWARE_ROUTE) {
                return 1;
            }

            return 0;
        });
    }

    /**
     * Echos the response according to the passed parameters.
     *
     * @param mixed $result
     * @param bool $routeMatchFound
     * @param bool $pathMatchFound
     *
     * @return void
     */
    protected static function respond($result, bool $routeMatchFound, bool $pathMatchFound): void
    {
        $code = 200;

        if (!$routeMatchFound) {
            $code   = $pathMatchFound ? 405 : 404;
            $path   = static::$path;
            $method = static::getRequestMethod();

            $responses = [
                404 => [
                    'title'   => sprintf('%d Not Found', $code),
                    'message' => sprintf('The "%s" route is not found!', $path),
                ],
                405 => [
                    'title'   => sprintf('%d Not Allowed', $code),
                    'message' => sprintf('The "%s" route is found, but the request method "%s" is not allowed!', $path, $method),
                ],
            ];

            App::log("Responded with {$code} to the request for '{$path}' with method '{$method}'", null, 'system');

            // this function will exit the script
            App::abort(
                $code,
                $responses[$code]['title'],
                $responses[$code]['message']
            );
        }

        http_response_code() || http_response_code($code);
        echo $result;
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
    protected static function getValidParameters(?string $base, ?bool $allowMultiMatch, ?bool $caseMatters, ?bool $slashMatters): array
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
    protected static function getRoutePath(bool $slashMatters): string
    {
        $url = static::getParsedUrl();

        $path = $url['path'] ?? '/';
        $path = !$slashMatters && $path !== '/' ? rtrim($path, '/') : $path;

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
    protected static function getRouteRegex(string $expression, bool $slashMatters, bool $caseMatters): string
    {
        $asteriskRegex    = '/(?<!\()\*(?!\))/';
        $placeholderRegex = '/{([a-zA-Z0-9_\-\.?]+)}/';

        // replace asterisk only if it's not a part of a regex capturing group
        $expression = preg_replace($asteriskRegex, '.*?', $expression);

        // replace placeholders with their corresponding regex
        if (preg_match_all($placeholderRegex, $expression, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $placeholder = $match[0];
                $replacement = strpos($placeholder, '?') !== false ? '(.*)?' : '(.+)';
                $expression  = strtr($expression, [
                    $placeholder => $replacement
                ]);
            }
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
    protected static function getRouteArguments(array $current, array $matches, $result): array
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
     * Returns the current request method via `$_SERVER` or `$_POST['_method']`.
     *
     * @return string
     */
    protected static function getRequestMethod(): string
    {
        $method = Globals::cutPost('_method') ?? '';
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

        // remove double slashes as they make parse_url() behave unexpectedly
        $url = preg_replace('/(\/+)/', '/', $uri);
        $url = parse_url($url);

        return $url;
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

        static $isListening = false;

        // start the router if it's not started explicitly
        // @codeCoverageIgnoreStart
        if (Config::get('router.allowAutoStart') && !$isListening) {
            Event::listen(App::ON_SHUTDOWN, static function () {
                // $params should be an array if the router has been started
                if (self::$params === null && PHP_SAPI !== 'cli') {
                    try {
                        static::start();
                    } catch (\Throwable $exception) {
                        App::handleException($exception);
                    }
                }
            });

            $isListening = true;
        }
        // @codeCoverageIgnoreEnd
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
            Exception::throw(
                'UndefinedMethodException:BadMethodCallException',
                sprintf('Call to undefined method %s::%s()', static::class, $method)
            );
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
     * Allows static methods handled by `self::__callStatic()` to be accessible via object operator `->`.
     */
    public function __call(string $method, array $arguments)
    {
        return static::__callStatic($method, $arguments);
    }
}
