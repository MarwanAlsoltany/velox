<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Backend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Backend\Router;
use MAKS\Velox\Backend\Globals;

class RouterTest extends TestCase
{
    private Router $router;


    public function setUp(): void
    {
        parent::setUp();

        $this->router = new Router();

        // Superglobals needed in order for the Router class to work
        Globals::setServer('HTTPS', 'on');
        Globals::setServer('HTTP_HOST', 'velox.test');
        Globals::setServer('SERVER_PROTOCOL', 'HTTP/1.1');
        Globals::setServer('REQUEST_URI', '/');
        Globals::setServer('REQUEST_METHOD', 'GET');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->router);
    }


    public function testRouterHandleAndMiddlewareMethodsRegisterRoutesAndReturnSelf()
    {
        $this->setTestObjectProperty($this->router, 'routes', []);

        Globals::setServer('REQUEST_URI', '/en/works');
        Globals::setServer('REQUEST_METHOD', 'GET');

        $router = $this->router->middleware('/{word}', fn ($path, $match, $previous) => $match, ['GET', 'POST']);
        $router = $this->router->handle('/{word}', fn ($path, $match, $previous) => sprintf('Test: %s === %s', $match, $previous), 'GET');
        $routes = $this->router->getRegisteredRoutes();

        $this->assertInstanceOf(Router::class, $router);
        $this->assertIsArray($routes);
        $this->assertIsArray($routes[0]);
        $this->assertEquals('/{word}', $routes[0]['expression']);
        $this->assertEquals('/{word}', $routes[1]['expression']);

        $this->expectOutputString('Test: works === works');

        $this->router->start('/en', true, false, false);
    }

    public function testRouterHandleMethodWorksOnlyOnceIfMoreThanOneHandlerIsRegisteredForTheSameRoute()
    {
        $this->setTestObjectProperty($this->router, 'routes', []);

        Globals::setServer('REQUEST_URI', '/multi-match');
        Globals::setServer('REQUEST_METHOD', 'GET');

        $this->router->handle('/multi-match', fn ($path, $match, $previous) => 'Executed!');
        $this->router->handle('/multi-match', fn ($path, $match, $previous) => 'Not Executed!');

        $this->expectOutputString('Executed!');

        $this->router->start('/', false, false, false);
    }

    public function testHttpVerbsMagicMethodsForRegersteringRoutes()
    {
        $this->setTestObjectProperty($this->router, 'routes', []);

        $this->router->get('/get', fn () => 'get');
        $this->router->head('/head', fn () => 'head');
        $this->router->post('/post', fn () => 'post');
        $this->router->put('/put', fn () => 'put');
        $this->router->patch('/patch', fn () => 'patch');
        $this->router->delete('/delete', fn () => 'delete');
        $this->router->connect('/connect', fn () => 'connect');
        $this->router->options('/options', fn () => 'options');
        $this->router->trace('/trace', fn () => 'trace');
        $this->router->any('/any', fn () => 'any');

        $routes = $this->router->getRegisteredRoutes();

        $this->assertEquals('get', $routes[0]['method']);
        $this->assertEquals('head', $routes[1]['method']);
        $this->assertEquals('post', $routes[2]['method']);
        $this->assertEquals('put', $routes[3]['method']);
        $this->assertEquals('patch', $routes[4]['method']);
        $this->assertEquals('delete', $routes[5]['method']);
        $this->assertEquals('connect', $routes[6]['method']);
        $this->assertEquals('options', $routes[7]['method']);
        $this->assertEquals('trace', $routes[8]['method']);
        $this->assertEquals(Router::SUPPORTED_METHODS, $routes[9]['method']);

        $this->router->get('/', fn () => 'Edge Case', 'Superfluous argument, used for code coverage!');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Call to undefined method)/');

        $this->router->unknown();
    }

    public function testRouterRedirectMethodRaisesAnExceptionWhenReirectingToAUrlBecauseHeadersAreSent()
    {
        $this->expectException(\ErrorOrWarningException::class);
        $this->expectExceptionMessageMatches('/(Cannot modify header information)/');

        $this->router->redirect('http://domain.tld/');
    }

    public function testRouterRedirectMethodRaisesAnExceptionWhenReirectingToARouteBecauseHeadersAreSent()
    {
        $this->expectException(\ErrorOrWarningException::class);
        $this->expectExceptionMessageMatches('/(Cannot modify header information)/');

        $this->router->redirect('/');
    }

    public function testRouterForwardMethodForwardsTheRequestToAnotherHandlerPlusMimicRouterFailure()
    {
        $this->setTestObjectProperty($this->router, 'routes', []);

        Globals::setServer('REQUEST_URI', '/');
        Globals::setServer('REQUEST_METHOD', 'GET');
        Globals::setPost('_method', 'GET'); // will be used instead of REQUEST_METHOD

        $route1 = [
            'expression' => '/',
            'handler' => function ($path, $match, $previous) {
                return Router::forward('/some-route');
            }
        ];
        $route2 = [
            'expression' => '/some-route',
            'handler' => function ($path, $match, $previous) {
                // to make the router fail
                return false;
            }
        ];

        $this->router->handle($route1['expression'], $route1['handler']);
        $this->router->handle($route2['expression'], $route2['handler']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches("/(Something went wrong when trying to respond to '\/some-route'!)/");

        $this->router->start('/', true, true, true);
    }

    public function testRouterHandleMethodNotAllowedMethod()
    {
        $this->setTestObjectProperty($this->router, 'routes', []);

        Globals::setServer('REQUEST_URI', '/405');
        Globals::setServer('REQUEST_METHOD', 'GET');

        $this->router->handleMethodNotAllowed(function () { return 'Test works!'; });
        $this->router->handle('/405', fn () => 'Test', 'POST');

        $callback = $this->getTestObjectProperty($this->router, 'methodNotAllowedCallback');

        $this->assertIsCallable($callback);
        $this->assertEquals('Test works!', $callback());

        $this->expectOutputString('Test works!');

        $this->router->start('/', true, true, false);
    }

    public function testRouterHandleRouteNotFoundMethod()
    {
        $this->setTestObjectProperty($this->router, 'routes', []);

        Globals::setServer('REQUEST_URI', '/404');
        Globals::setServer('REQUEST_METHOD', 'GET');

        $this->router->handleRouteNotFound(function () { return 'Test works!'; });
        $this->router->handle('/', fn () => 'Test', 'POST');

        $callback = $this->getTestObjectProperty($this->router, 'routeNotFoundCallback');

        $this->assertIsCallable($callback);
        $this->assertEquals('Test works!', $callback());

        $this->expectOutputString('Test works!');

        $this->router->start('/', false, false, true);
    }

    public function testRouter404And405FallbackPages()
    {
        $this->setTestObjectProperty($this->router, 'routes', []);
        $this->setTestObjectProperty($this->router, 'methodNotAllowedCallback', null);
        $this->setTestObjectProperty($this->router, 'routeNotFoundCallback', null);

        Globals::setServer('REQUEST_URI', $route = '/' . uniqid());
        Globals::setServer('REQUEST_METHOD', 'GET');

        $this->router->handle($route, fn () => 'This should not be executed!', 'POST');

        $this->expectOutputRegex('/(404 Not Found)/');

        $this->router->start('/', true, true, false);

        $this->expectOutputRegex('/(405 Not Allowed)/');

        $this->router->start('/', true, true, false);
    }

    public function testRouterGetParsedUrlMethod()
    {
        Globals::setServer('REQUEST_URI', '/?testing=true&lang=en');
        Globals::setServer('QUERY_STRING', 'testing=true&lang=en');

        $url = $this->router->getParsedUrl();

        $this->assertArrayHasKey('path', $url);
        $this->assertEquals('/', $url['path']);
        $this->assertArrayHasKey('query', $url);
        $this->assertEquals('testing=true&lang=en', $url['query']);
    }

    public function testRouterGetParsedQueryMethod()
    {
        Globals::setServer('REQUEST_URI', '/?testing=true&lang=en');
        Globals::setServer('QUERY_STRING', 'testing=true&lang=en');

        $query = $this->router->getParsedQuery();

        $this->assertArrayHasKey('testing', $query);
        $this->assertEquals('true', $query['testing']);
        $this->assertArrayHasKey('lang', $query);
        $this->assertEquals('en', $query['lang']);
    }
}
