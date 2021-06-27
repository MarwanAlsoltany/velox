<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\App;

class AppTest extends TestCase
{
    private App $app;


    public function setUp(): void
    {
        parent::setUp();

        $this->app = new App();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->app);
    }


    public function testAppThrowsAnExceptionForCallesToUndefinedProperties()
    {
        $this->expectException(\Exception::class);

        $this->app->unknown;
    }

    public function testThrowsAnExceptionForCallesToUndefinedMethods()
    {
        $this->expectException(\Exception::class);

        $this->app->unknown();
    }

    public function testAppReturnsItsPropertyWhenCalledAsAMethod()
    {
        $this->assertEquals($this->app->data, $this->app->data());
    }
}
