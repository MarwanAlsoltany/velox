<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Backend\Controller;
use MAKS\Velox\Backend\Config;
use MAKS\Velox\Backend\Router;
use MAKS\Velox\Backend\Globals;
use MAKS\Velox\Frontend\Data;
use MAKS\Velox\Frontend\View;
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Frontend\Path;

class ControllerTest extends TestCase
{
    private Controller $controller;


    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new class(['key' => 'value']) extends Controller {};
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->controller);
    }


    public function testControllerPropertiesContainExpectedValues()
    {
        $config = $this->getTestObjectProperty($this->controller, 'config');
        $this->assertInstanceOf(Config::class, $config);

        $router = $this->getTestObjectProperty($this->controller, 'router');
        $this->assertInstanceOf(Router::class, $router);

        $globals = $this->getTestObjectProperty($this->controller, 'globals');
        $this->assertInstanceOf(Globals::class, $globals);

        $data = $this->getTestObjectProperty($this->controller, 'data');
        $this->assertInstanceOf(Data::class, $data);

        $view = $this->getTestObjectProperty($this->controller, 'view');
        $this->assertInstanceOf(View::class, $view);

        $html = $this->getTestObjectProperty($this->controller, 'html');
        $this->assertInstanceOf(HTML::class, $html);

        $path = $this->getTestObjectProperty($this->controller, 'path');
        $this->assertInstanceOf(Path::class, $path);

        $vars = $this->getTestObjectProperty($this->controller, 'vars');

        $this->assertIsArray($vars);
        $this->assertArrayHasKey('key', $vars);
        $this->assertEquals('value', $vars['key']);
    }
}
