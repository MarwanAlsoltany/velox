<?php

declare(strict_types=1);

namespace MAKS\Velox\Backend;

use MAKS\Velox\Backend\Config;
use MAKS\Velox\Backend\Router;
use MAKS\Velox\Frontend\Data;
use MAKS\Velox\Frontend\View;
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Frontend\Path;

/**
 * An abstract class that serves as a base Controller that can be extended to make handlers for application router.
 * This class has these properties: `$config` = `Config`, `$router` = `Router`, `$data` = `Data`, `$view` = `View`, `$html` = `HTML`, `$path` = `Path`, and the passed array as `$vars`.
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
 * @since 1.0.0
 */
abstract class Controller
{
    /**
     * The passed variables array to the Controller.
     */
    protected array $vars;

    protected Config $config;

    protected Router $router;

    protected Data $data;

    protected View $view;

    protected HTML $html;

    protected Path $path;


    /**
     * Class constructor.
     *
     * @param array $vars [optional] Additional variables to pass to the controller.
     */
    public function __construct(array $vars = [])
    {
        $this->vars   = $vars;
        $this->config = new Config();
        $this->router = new Router();
        $this->data   = new Data();
        $this->view   = new View();
        $this->html   = new HTML();
        $this->path   = new Path();
    }
}
