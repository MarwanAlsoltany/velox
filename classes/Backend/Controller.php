<?php

declare(strict_types=1);

namespace MAKS\Velox\Backend;

use MAKS\Velox\App;
use MAKS\Velox\Backend\Event;
use MAKS\Velox\Backend\Config;
use MAKS\Velox\Backend\Router;
use MAKS\Velox\Backend\Globals;
use MAKS\Velox\Backend\Session;
use MAKS\Velox\Backend\Database;
use MAKS\Velox\Backend\Auth;
use MAKS\Velox\Backend\Model;
use MAKS\Velox\Frontend\Data;
use MAKS\Velox\Frontend\View;
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Frontend\Path;
use MAKS\Velox\Helper\Dumper;
use MAKS\Velox\Helper\Misc;

/**
 * An abstract class that serves as a base Controller that can be extended to make handlers for application router.
 *
 * Example:
 * ```
 * // create a controller (alternatively, you can create it as a normal class in "/app/Controller/")
 * $additionalVars = [1, 2, 3];
 * $controller = new class($additionalVars) extends Controller {
 *      public function someAction(string $path, ?string $match, $previous) {
 *          $this->data->set('page.title', 'Some Page');
 *          $someVar = $this->config->get('filename.someVar');
 *          return $this->view->render('some-page', $this->vars);
 *      }
 * };
 *
 * // use the created action as a handler for a route
 * Router::handle('/some-route', [$controller, 'someAction'], ['GET', 'POST']);
 * ```
 *
 * @package Velox\Backend
 * @since 1.0.0
 * @api
 *
 * @property Event $event Instance of the `Event` class.
 * @property Config $config Instance of the `Config` class.
 * @property Router $router Instance of the `Router` class.
 * @property Globals $globals Instance of the `Globals` class.
 * @property Session $session Instance of the `Session` class.
 * @property Database $database Instance of the `Database` class.
 * @property Auth $auth Instance of the `Auth` class.
 * @property Data $data Instance of the `Data` class.
 * @property View $view Instance of the `View` class.
 * @property HTML $html Instance of the `HTML` class.
 * @property Path $path Instance of the `Path` class.
 * @property Dumper $dumper Instance of the `Dumper` class.
 * @property Misc $misc Instance of the `Misc` class.
 */
abstract class Controller
{
    /**
     * This event will be dispatched when a controller (or a subclass) is constructed.
     * This event will not be passed any arguments, but its listener callback will be bound to the object (the controller class).
     *
     * @var string
     */
    public const ON_CONSTRUCT = 'controller.on.construct';


    /**
     * Preconfigured CRUD routes.
     *
     * @since 1.3.0
     */
    private array $crudRoutes = [
        'index' => [
            'expression' => '/{controller}',
            'method' => 'GET',
        ],
        'create' => [
            'expression' => '/{controller}/create',
            'method' => 'GET',
        ],
        'store' => [
            'expression' => '/{controller}',
            'method' => 'POST',
        ],
        'show' => [
            'expression' => '/{controller}/([1-9][0-9]*)',
            'method' => 'GET',
        ],
        'edit' => [
            'expression' => '/{controller}/([1-9][0-9]*)/edit',
            'method' => 'GET',
        ],
        'update' => [
            'expression' => '/{controller}/([1-9][0-9]*)',
            'method' => ['PUT', 'PATCH'],
        ],
        'destroy' => [
            'expression' => '/{controller}/([1-9][0-9]*)',
            'method' => 'DELETE',
        ],
    ];

    /**
     * The passed variables array to the Controller.
     */
    protected array $vars;

    protected ?Model $model;


    /**
     * Class constructor.
     *
     * @param array $vars [optional] Additional variables to pass to the controller.
     */
    public function __construct(array $vars = [])
    {
        $this->vars  = $vars;
        $this->model = null;

        if ($this->associateModel()) {
            $this->doAssociateModel();
        }

        if ($this->registerRoutes()) {
            $this->doRegisterRoutes();
        }

        Event::dispatch(self::ON_CONSTRUCT, null, $this);
    }

    public function __get(string $property)
    {
        if (isset(App::instance()->{$property})) {
            return App::instance()->{$property};
        }

        $class = static::class;

        throw new \Exception("Call to undefined property {$class}::${$property}");
    }

    public function __isset(string $property)
    {
        return isset(App::instance()->{$property});
    }


    /**
     * Controls which model should be used by current controller.
     *
     * This method should return a concrete class FQN of a model that extends `Model::class`.
     *
     * This method returns `null` by default.
     *
     * NOTE: If the model class does not exist, the controller will ignore it silently.
     *
     * @return string
     *
     * @since 1.3.0
     */
    protected function associateModel(): ?string
    {
        return null; // @codeCoverageIgnore
    }

    /**
     * Whether or not to automatically register controller routes.
     *
     * NOTE: The controller class has to be instantiated at least once for this to work.
     *
     * Only public methods suffixed with the word `Action` or `Middleware` will be registered.
     * The suffix will determine the route type (`*Action` => `handler`, `*Middleware` => `middleware`).
     * The route will look like `/controller-name/method-name` (names will be converted to slugs).
     * The method will be `GET` by default. See also `self::$crudRoutes`.
     * You can use the `@route` annotation to overrides the default Route and Method.
     * The `@route` annotation can be used in DocBlock on a class method with the following syntax:
     * - Pattern: `@route("<path>", {<http-verb>, ...})`
     * - Example: `@route("/some-route", {GET, POST})`
     *
     * This method returns `false` by default.
     *
     * @return bool
     *
     * @since 1.3.0
     */
    protected function registerRoutes(): bool
    {
        return false; // @codeCoverageIgnore
    }

    /**
     * Associates a model class to the controller.
     *
     * @return void
     *
     * @since 1.3.0
     */
    private function doAssociateModel(): void
    {
        $model = $this->associateModel();
        // to prevent \ReflectionClass from throwing an exception
        $model = class_exists($model) ? $model : Model::class;

        $reflection = new \ReflectionClass($model);

        if ($reflection->isSubclassOf(Model::class) && !$reflection->isAbstract()) {
            $this->model = $reflection->newInstance();
        }
    }

    /**
     * Registers all public methods which are suffixed with `Action` or `Middleware` as `handler` or `middleware` respectively.
     *
     * @return void
     *
     * @since 1.3.0
     */
    private function doRegisterRoutes(): void
    {
        $class   = new \ReflectionClass($this);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $className  = $class->getShortName();
            $methodName = $method->getName();
            $docBlock   = $method->getDocComment() ?: '';

            if (
                $method->isAbstract() ||
                $method->isStatic() ||
                preg_match('/(Action|Middleware)$/', $methodName) === 0
            ) {
                continue;
            }

            $controller  = Misc::transform(str_replace('Controller', '', $className), 'kebab', 'slug');
            $handler     = Misc::transform(str_replace(['Action', 'Middleware'], '', $methodName), 'kebab', 'slug');

            $routes = $this->crudRoutes;

            if (!in_array($handler, array_keys($routes))) {
                Misc::setArrayValueByKey(
                    $routes,
                    $handler . '.expression',
                    sprintf('/%s/%s', $controller, $handler)
                );
            }

            if (preg_match('/(@route[ ]*\(["\'](.+)["\']([ ,]*\{(.+)\})?\))/', $docBlock, $matches)) {
                $routeExpression = $matches[2] ?? '';
                $routeMethod     = $matches[4] ?? '';

                $routeMethod = array_filter(array_map('trim', explode(',', $routeMethod)));
                $routes[$handler] = [
                    'expression' => $routeExpression,
                    'method'     => $routeMethod,
                ];
            }

            $function   = preg_match('/(Middleware)$/', $methodName) ? 'middleware' : 'handle';
            $expression = Misc::interpolate($routes[$handler]['expression'], ['controller' => $controller]);
            $method     = $routes[$handler]['method'] ?? 'GET';

            $this->router->{$function}($expression, [$this, $methodName], $method);
        }
    }
}
