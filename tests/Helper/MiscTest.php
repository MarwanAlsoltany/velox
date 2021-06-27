<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Helper\Misc;
use MAKS\Velox\Backend\Config;

class MiscTest extends TestCase
{
    private Misc $misc;


    public function setUp(): void
    {
        parent::setUp();

        $this->misc = new Misc();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->misc);
    }


    public function testMiscGetArrayValueByKeyMethodGetsExpectedValues()
    {
        $array = [
            'prop1' => 'test',
            'prop2' => 123,
            'prop3' => ['sub1' => true, 'sub2' => false, 'sub3' => ['nested' => null]]
        ];

        $this->assertEquals('fallback', $this->misc->getArrayValueByKey($array, 'not.found', 'fallback'));
        $this->assertEquals('none', $this->misc->getArrayValueByKey($array, 'prop0', 'none'));
        $this->assertEquals('test', $this->misc->getArrayValueByKey($array, 'prop1'));
        $this->assertEquals(123, $this->misc->getArrayValueByKey($array, 'prop2'));
        $this->assertTrue($this->misc->getArrayValueByKey($array, 'prop3.sub1'));
        $this->assertFalse($this->misc->getArrayValueByKey($array, 'prop3.sub2'));
        $this->assertNull($this->misc->getArrayValueByKey($array, 'prop3.sub3.nested'));

        $arr = [];
        $str = '';
        $this->assertEquals($str, $this->misc->getArrayValueByKey($arr, $str, $str));
    }

    public function testMiscSetArrayValueByKeyMethodSetsExpectedValues()
    {
        $array = [
            'prop1' => 'test',
            'prop2' => 123,
            'prop3' => ['sub1' => true, 'sub2' => ['nested' => null]]
        ];

        $this->misc->setArrayValueByKey($array, 'prop4', 'abc');
        $this->misc->setArrayValueByKey($array, 'prop5.sub1.nested', 'xyz');

        $this->assertArrayHasKey('prop4', $array);
        $this->assertArrayHasKey('nested', $array['prop5']['sub1']);
        $this->assertEquals('abc', $array['prop4']);
        $this->assertEquals('xyz', $array['prop5']['sub1']['nested']);

        $arr = [];
        $str = '';
        $this->assertFalse($this->misc->setArrayValueByKey($arr, $str, $str));
    }

    public function testInterpolateMethod()
    {
        $text1 = $this->misc->interpolate('This is {text} ...', ['text' => 'an interpolated text']);
        $text2 = $this->misc->interpolate('This is <text> with a different placeholder ...', ['text' => 'an interpolated text'], '<>');
        $text3 = $this->misc->interpolate('This is [text] with a different placeholder ...', ['text' => 'an interpolated text'], '[|]');

        $this->assertEquals('This is an interpolated text ...', $text1);
        $this->assertEquals('This is an interpolated text with a different placeholder ...', $text2);
        $this->assertEquals('This is [text] with a different placeholder ...', $text3);
    }

    public function testBacktraceMethod()
    {
        $backtrace = $this->misc->backtrace();
        $fileAndLine = $this->misc->backtrace(['file', 'line']);
        $fileLast = $this->misc->backtrace('file');
        $fileFirst = $this->misc->backtrace('file', -1);
        $none = $this->misc->backtrace('file', 99999);

        $this->assertIsArray($backtrace);
        $this->assertArrayHasKey('file', $backtrace[0]);
        $this->assertArrayHasKey('line', $backtrace[0]);
        $this->assertArrayHasKey('function', $backtrace[0]);
        $this->assertIsArray($fileAndLine);
        $this->assertArrayHasKey('file', $fileAndLine);
        $this->assertArrayHasKey('line', $fileAndLine);
        $this->assertArrayNotHasKey('function', $fileAndLine);
        $this->assertIsString($fileLast);
        $this->assertEquals(__FILE__, $fileLast);
        $this->assertIsString($fileFirst);
        $this->assertNotEquals(__FILE__, $fileFirst);
        $this->assertEmpty($none);
    }

    public function testLogMethod()
    {
        $message = 'This is a test message!';

        $operation1 = $this->misc->log('This is a {name} message!', ['name' => 'test'], 'test-1', __DIR__);

        $this->assertFileExists(__DIR__ . '/test-1.log');
        $this->assertTrue($operation1);

        $log = file_get_contents(__DIR__ . '/test-1.log');

        $this->assertStringContainsString($message, $log);

        Config::set('global.loggingEnabled', false);

        $operation2 = $this->misc->log('This is a {name} message!', ['name' => 'test'], 'test-2', __DIR__);

        $this->assertFileDoesNotExist(__DIR__ . '/test-2.log');
        $this->assertTrue($operation2);

        Config::set('global.loggingEnabled', true);

        $operation3 = $this->misc->log('This is a {name} message!', ['name' => 'test'], null, __DIR__ . '/logs');

        $this->assertTrue($operation3);

        $operation4 = $this->misc->log('This is a {name} message!', ['name' => 'test'], null, null);

        $this->assertTrue($operation4);

        array_map(fn ($item) => is_dir($item) ? rmdir($item) : unlink($item), [
            ...glob(__DIR__ . '/*.log'),
            ...glob(BASE_PATH . '/storage/logs/autogenerated-*.log'),
            ...glob(__DIR__ . '/logs/*.log'),
            __DIR__ . '/logs',
        ]);
    }
}
