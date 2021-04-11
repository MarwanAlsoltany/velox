<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox;

use MAKS\Velox\Backend\Router;
use MAKS\Velox\Backend\Config;
use MAKS\Velox\Frontend\Data;
use MAKS\Velox\Frontend\View;
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Frontend\Path;
use MAKS\Velox\Helper\Dumper;
use MAKS\Velox\Helper\Misc;

/**
 * A class that serves as a basic service-container for VELOX.
 * This class has all VELOX classes as public properties:
 *      - `$config` = `Config::class`
 *      - `$router` = `Router::class`
 *      - `$data`   = `Data::class`
 *      - `$view`   = `View::class`
 *      - `$html`   = `HTML::class`
 *      - `$path`   = `Path::class`
 *      - `$dumper` = `Dumper::class`
 *      - `$misc`   = `Misc::class`
 *
 * Example:
 * ```
 * // create an instance
 * $velox = new Velox();
 * // available properties are
 *
 * // get an instance of the `App` class via public property access notation
 * $velox->router->handle('/dump', 'dd');
 * // or via calling a method with the same name
 * $velox->router()->handle('/dump', 'dd');
 * ```
 *
 * @since 1.0.0
 */
class App
{
    public Config $config;

    public Router $router;

    public Data $data;

    public View $view;

    public HTML $html;

    public Path $path;

    public Dumper $dumper;

    public Misc $misc;


    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->config = new Config();
        $this->router = new Router();
        $this->data   = new Data();
        $this->view   = new View();
        $this->html   = new HTML();
        $this->path   = new Path();
        $this->dumper = new Dumper();
        $this->misc   = new Misc();
    }

    public function __call(string $method, array $arguments)
    {
        $class = static::class;

        try {
            return $this->{$method};
        } catch (\Throwable $error) {
            throw new \Exception(
                "Call to undefined method {$class}::{$method}()",
                $error->getCode(),
                $error
            );
        }
    }

    public function __get(string $property)
    {
        $class = static::class;

        throw new \Exception("Call to undefined property {$class}::${$property}");
    }
}
