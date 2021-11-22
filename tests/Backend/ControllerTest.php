<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Backend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Tests\Mocks\ControllerMock;
use MAKS\Velox\Backend\Controller;
use MAKS\Velox\Backend\Auth;
use MAKS\Velox\Backend\Event;
use MAKS\Velox\Backend\Config;
use MAKS\Velox\Backend\Router;
use MAKS\Velox\Backend\Globals;
use MAKS\Velox\Backend\Session;
use MAKS\Velox\Backend\Database;
use MAKS\Velox\Backend\Model;
use MAKS\Velox\Frontend\Data;
use MAKS\Velox\Frontend\View;
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Frontend\Path;
use MAKS\Velox\Helper\Dumper;
use MAKS\Velox\Helper\Misc;

class ControllerTest extends TestCase
{
    private Controller $controller;


    public function setUp(): void
    {
        parent::setUp();

        $this->controller = $this->getControllerInstance();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->controller);
    }


    public function testControllerPropertiesContainExpectedValues()
    {
        $properties = [
            'auth'     => Auth::class,
            'event'    => Event::class,
            'config'   => Config::class,
            'router'   => Router::class,
            'globals'  => Globals::class,
            'session'  => Session::class,
            'database' => Database::class,
            'model'    => Model::class,
            'data'     => Data::class,
            'view'     => View::class,
            'html'     => HTML::class,
            'path'     => Path::class,
            'dumper'   => Dumper::class,
            'misc'     => Misc::class,
        ];

        foreach ($properties as $property => $class) {
            $instance = $this->getTestObjectProperty($this->controller, $property);
            $this->assertInstanceOf($class, $instance);
        }

        $vars = $this->getTestObjectProperty($this->controller, 'vars');

        $this->assertIsArray($vars);
        $this->assertArrayHasKey('key', $vars);
        $this->assertEquals('value', $vars['key']);
    }

    public function testControllerThrowsAnExceptionOnCallsToUndefinedProperties()
    {
        $this->expectException(\Exception::class);

        $this->controller->unknown;
    }


    private function getControllerInstance(): Controller
    {
        return new ControllerMock(['key' => 'value']);
    }
}
