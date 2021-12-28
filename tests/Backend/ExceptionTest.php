<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Backend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Backend\Exception;

class ExceptionTest extends TestCase
{
    private Exception $exception;


    public function setUp(): void
    {
        parent::setUp();

        $this->exception = new Exception('Test exception');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->exception);
    }


    public function testExceptionObjectWhenCastingItToAString()
    {
        $exceptionString = (string)($this->exception);

        $this->assertIsString($exceptionString);
        $this->assertStringContainsString(Exception::class, $exceptionString);
        $this->assertStringContainsString('Test exception', $exceptionString);
    }

    public function testExceptionWhenThrown()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception');

        throw $this->exception;
    }

    public function testExceptionThrowMethod()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectException(\Tests\TestException::class);
        $this->expectExceptionMessage('Test exception');

        Exception::throw(
            'Tests\TestException:RuntimeException',
            'Test exception',
            0,
            $this->exception
        );
    }

    public function testExceptionThrowMethodWithoutMessage()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/' . preg_quote(__FILE__, '/') . '/');

        Exception::throw('Exception');
    }

    public function testExceptionThrowMethodWithAnExistingExceptionClass()
    {
        $this->expectException(Exception::class);

        Exception::throw(Exception::class . ':Exception');
    }

    public function testExceptionHandleMethod()
    {
        $text = __METHOD__;

        $this->expectOutputString($text);

        Exception::handle(function () use ($text) {
            echo($text);
        });

        $this->expectExceptionMessage('Test error');

        Exception::handle(function () {
            trigger_error('Test error', E_USER_NOTICE);
        });
    }

    public function testExceptionHandleMethodWithAUserError()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/(Error was converted to an exception)/');

        Exception::handle(function () {
            trigger_error('Test error', E_USER_ERROR);
        }, 'RuntimeException', 'Error was converted to an exception');
    }

    public function testExceptionTriggerMethod()
    {
        $this->expectError();
        $this->expectErrorMessageMatches('/(Test error)/');
        $this->expectErrorMessageMatches('/' . preg_quote(__FILE__, '/') . '/');
        $this->expectException(\RuntimeException::class);
        $this->expectException(\Tests\ErrorException::class);

        Exception::handle(function () {
            Exception::trigger('Test error');
        }, 'Tests\ErrorException:RuntimeException');
    }
}
