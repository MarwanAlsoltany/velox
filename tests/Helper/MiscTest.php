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

        $this->testArray = [
            'prop1' => 'test',
            'prop2' => 123,
            'prop3' => ['sub1' => true, 'sub2' => false, 'sub3' => ['nested' => null]]
        ];

        $this->testObject = new class {
            // ...
            public const CONST_PROP = 'CONST';
            public static $staticProp = 'STATIC';
            private $privateProp = 'PRIVATE';
            protected $protectedProp = 'PROTECTED';
            public $publicProp = 'PUBLIC';

            public function __construct($staticProp = 'STATIC', $privateProp = 'PRIVATE', $protectedProp = 'PROTECTED', $publicProp = 'PUBLIC')
            {
                $this::$staticProp = $staticProp;
                $this->privateProp = $privateProp;
                $this->protectedProp = $protectedProp;
                $this->publicProp = $publicProp;
            }

            private function privateMethod($string = '')
            {
                return 'PRIVATE: ' . $string;
            }

            protected function protectedMethod($string = '')
            {
                return 'PROTECTED: ' . $string;
            }

            public function publicMethod($string = '')
            {
                return 'PUBLIC: ' . $string;
            }

            public function exception()
            {
                throw new \Exception('Test!');
            }
        };
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->misc);
        unset($this->testObject);
    }


    public function testMiscGetArrayValueByKeyMethodGetsExpectedValues()
    {
        $fallback = $this->misc->getArrayValueByKey($this->testArray, 'not.found', 'fallback');
        $none     = $this->misc->getArrayValueByKey($this->testArray, 'prop0', 'none');
        $test     = $this->misc->getArrayValueByKey($this->testArray, 'prop1');
        $num123   = $this->misc->getArrayValueByKey($this->testArray, 'prop2');
        $true     = $this->misc->getArrayValueByKey($this->testArray, 'prop3.sub1');
        $false    = $this->misc->getArrayValueByKey($this->testArray, 'prop3.sub2');
        $null     = $this->misc->getArrayValueByKey($this->testArray, 'prop3.sub3.nested');

        $this->assertEquals('fallback', $fallback);
        $this->assertEquals('none', $none);
        $this->assertEquals('test', $test);
        $this->assertEquals(123, $num123);
        $this->assertTrue($true);
        $this->assertFalse($false);
        $this->assertNull($null);

        $arr = [];
        $str = '';
        $this->assertEquals($str, $this->misc->getArrayValueByKey($arr, $str, $str));
    }

    public function testMiscSetArrayValueByKeyMethodSetsExpectedValues()
    {
        $this->misc->setArrayValueByKey($this->testArray, 'prop4', 'abc');
        $this->misc->setArrayValueByKey($this->testArray, 'prop5.sub1.nested', 'xyz');

        $this->assertArrayHasKey('prop4', $this->testArray);
        $this->assertArrayHasKey('nested', $this->testArray['prop5']['sub1']);
        $this->assertEquals('abc', $this->testArray['prop4']);
        $this->assertEquals('xyz', $this->testArray['prop5']['sub1']['nested']);

        $arr = [];
        $str = '';
        $this->assertFalse($this->misc->setArrayValueByKey($arr, $str, $str));
    }

    public function testMiscCallMethodMethodExecutesAMethodOnTheGivenObject()
    {
        $privateMethod   = Misc::callObjectMethod($this->testObject, 'privateMethod', 'is not so private!');
        $protectedMethod = Misc::callObjectMethod($this->testObject, 'protectedMethod', 'is not so protected!');
        $publicMethod    = Misc::callObjectMethod($this->testObject, 'publicMethod', 'is simply public!');

        $this->assertEquals('PRIVATE: is not so private!', $privateMethod);
        $this->assertEquals('PROTECTED: is not so protected!', $protectedMethod);
        $this->assertEquals('PUBLIC: is simply public!', $publicMethod);

        $this->expectException(\Exception::class);
        Misc::callObjectMethod($this->testObject, 'exception');
    }

    public function testMiscGetPropertyMethodGetsValuesAsExpected()
    {
        $privateProp   = Misc::getObjectProperty($this->testObject, 'privateProp');
        $protectedProp = Misc::getObjectProperty($this->testObject, 'protectedProp');
        $publicProp    = Misc::getObjectProperty($this->testObject, 'publicProp');
        $staticProp    = Misc::getObjectProperty($this->testObject, 'staticProp');
        $constProp     = Misc::getObjectProperty($this->testObject, 'CONST_PROP');

        $this->assertEquals('PRIVATE', $privateProp);
        $this->assertEquals('PROTECTED', $protectedProp);
        $this->assertEquals('PUBLIC', $publicProp);
        $this->assertEquals('STATIC', $staticProp);
        $this->assertEquals('CONST', $constProp);

        $this->expectException(\Exception::class);
        Misc::getObjectProperty($this->testObject, 'UNKNOWN');
    }

    public function testMiscSetPropertyMethodSetsValuesAsExpected()
    {
        Misc::setObjectProperty($this->testObject, 'privateProp', 'private');
        Misc::setObjectProperty($this->testObject, 'protectedProp', 'protected');
        Misc::setObjectProperty($this->testObject, 'publicProp', 'public');
        Misc::setObjectProperty($this->testObject, 'staticProp', 'static');

        $this->assertEquals('private', $this->getTestObjectProperty($this->testObject, 'privateProp'));
        $this->assertEquals('protected', $this->getTestObjectProperty($this->testObject, 'protectedProp'));
        $this->assertEquals('public', $this->getTestObjectProperty($this->testObject, 'publicProp'));
        $this->assertEquals('static', $this->getTestObjectProperty($this->testObject, 'staticProp'));

        $this->expectException(\Exception::class);
        Misc::setObjectProperty($this->testObject, 'UNKNOWN', 'UNKNOWN');
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
