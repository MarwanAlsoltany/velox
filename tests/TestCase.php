<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests;

use MAKS\Velox\Helper\Misc;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function __construct()
    {
        parent::__construct();

        defined('EXIT_EXCEPTION') || define('EXIT_EXCEPTION', 1);

        $this->prepareNeededSuperglobals();
    }


    /**
     * Prepares superglobals needed for testing.
     *
     * @return void
     */
    private function prepareNeededSuperglobals(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'velox.test';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }


    /**
     * Gets a private, protected, or public property (default, static, or constant) of an object.
     *
     * @param object $object Class instance.
     * @param string $property Property name.
     *
     * @return mixed The property value.
     *
     * @throws \Exception On failure.
     */
    public static function getTestObjectProperty($object, string $property)
    {
        return Misc::getObjectProperty($object, $property);
    }

    /**
     * Sets a private, protected, or public property (default or static) of an object.
     *
     * @param object $object Class instance.
     * @param string $property Property name.
     * @param string $value Property value.
     *
     * @return mixed The new property value.
     *
     * @throws \Exception On failure.
     */
    public static function setTestObjectProperty($object, string $property, $value)
    {
        return Misc::setObjectProperty($object, $property, $value);
    }

    /**
     * Calls a private, protected, or public method on an object.
     *
     * @param object $object Class instance.
     * @param string $method Method name.
     * @param mixed ...$arguments
     *
     * @return mixed The function result, or false on error.
     *
     * @throws \Exception On failure or if the called function threw an exception.
     */
    public static function callTestObjectMethod($object, string $method, ...$arguments)
    {
        return Misc::callObjectMethod($object, $method, $arguments);
    }
}
