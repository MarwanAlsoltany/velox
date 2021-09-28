<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Backend\Config;
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


    public function testAppThrowsAnExceptionForCallsToUndefinedProperties()
    {
        $this->expectException(\Exception::class);

        $this->app->unknown;
    }

    public function testThrowsAnExceptionForCallsToUndefinedMethods()
    {
        $this->expectException(\Exception::class);

        $this->app->unknown();
    }

    public function testThrowsAnExceptionForCallsToUndefinedStaticMethods()
    {
        $this->expectException(\Exception::class);

        App::unknown();
    }

    public function testAppReturnsItsPropertyWhenCalledAsAMethod()
    {
        $this->assertEquals($this->app->data, $this->app->data());
    }

    public function testAppExtendMethodCanBeUsedToExtendTheClassInstanceWithNewMethods()
    {
        $returnParam = $this->app->extend('returnParam', fn ($param) => $param);
        $this->assertIsCallable($returnParam);
        $this->assertEquals($this->app->returnParam('param'), 'param');


        $returnThis = $this->app->extend('returnThis', function () {
            return $this;
        });
        $this->assertEquals($returnThis(), $this->app);
    }

    public function testAppExtendStaticMethodCanBeUsedToExtendTheClassWithNewMethods()
    {
        $returnParamStatic = App::extendStatic('returnParamStatic', fn ($param) => $param);
        $this->assertIsCallable($returnParamStatic);
        $this->assertEquals(App::returnParamStatic('param'), 'param');


        $returnStatic = App::extendStatic('returnStatic', function () {
            return static::class;
        });
        $this->assertEquals($returnStatic(), App::class);
    }

    public function testLogMethod()
    {
        $operation1 = $this->app->log('This is a {name} message!', ['name' => 'test'], 'test-1', __DIR__);
        $this->assertFileExists(__DIR__ . '/test-1.log');
        $this->assertTrue($operation1);
        $this->assertStringContainsString('This is a test message!', file_get_contents(__DIR__ . '/test-1.log'));

        $enabled = Config::get('global.logging.enabled');
        Config::set('global.logging.enabled', false);

        $operation2 = $this->app->log('This is a {name} message!', ['name' => 'test'], 'test-2', __DIR__);
        $this->assertFileDoesNotExist(__DIR__ . '/test-2.log');
        $this->assertTrue($operation2);

        Config::set('global.logging.enabled', $enabled);

        $operation3 = $this->app->log('This is a {name} message!', ['name' => 'test'], null, __DIR__ . '/logs');
        $this->assertTrue($operation3);

        $operation4 = $this->app->log('This is a {name} message!', ['name' => 'test'], null, null);
        $this->assertTrue($operation4);

        $maxFileSize = Config::get('global.logging.maxFileSize');
        Config::set('global.logging.maxFileSize', 256);

        foreach (range('A', 'Z') as $index => $letter) {
            $operation5 = $this->app->log('The letter "{letter}" is has number "{index}" in the alphabet!', ['letter' => $letter, 'index' => $index + 1], 'fs-test', __DIR__);
            $this->assertTrue($operation5);
        }
        $this->assertStringContainsString('For exceeding the configured {global.logging.maxFileSize}, it was overwritten on', file_get_contents(__DIR__ . '/fs-test.log'));

        Config::set('global.logging.maxFileSize', $maxFileSize);

        array_map(fn ($item) => is_dir($item) ? rmdir($item) : unlink($item), [
            ...glob(__DIR__ . '/*.log'),
            ...glob(BASE_PATH . '/storage/logs/autogenerated-*.log'),
            ...glob(__DIR__ . '/logs/*.log'),
            __DIR__ . '/logs',
        ]);
    }

    public function testAbortMethod()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(exit)/i');
        $this->expectOutputRegex('/(404 Not Found)/');
        $this->expectOutputRegex('/(This is a test message!)/');

        $this->app->abort(404, 'This is a test message!');
    }

    public function testTerminateMethod()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Exit: This is a test message!)/');

        $this->app->terminate('This is a test message!');
    }
}
