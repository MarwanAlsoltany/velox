<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Helper;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Tests\Mocks\TestObjectMock;
use MAKS\Velox\Helper\Misc;

class MiscTest extends TestCase
{
    private Misc $misc;
    private array $testArray;
    private object $testObject;


    public function setUp(): void
    {
        parent::setUp();

        $this->misc = new Misc();

        $this->testArray  = $this->getTestArray();
        $this->testObject = $this->getTestObjectInstance();
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

    public function testMiscCutArrayValueByKeyMethodCutsExpectedValues()
    {
        $prop1   = $this->misc->cutArrayValueByKey($this->testArray, 'prop1');
        $nested  = $this->misc->cutArrayValueByKey($this->testArray, 'prop3.sub3.nested', 'test');
        $unknown = $this->misc->cutArrayValueByKey($this->testArray, 'array.unknown');

        $this->assertEquals('test', $prop1);
        $this->assertEquals('test', $nested);
        $this->assertArrayNotHasKey('prop1', $this->testArray);
        $this->assertArrayNotHasKey('nested', $this->testArray['prop3']['sub3']);
        $this->assertNull($unknown);
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

    public function testMiscInterpolateMethod()
    {
        $text1 = $this->misc->interpolate('This is {text} ...', ['text' => 'an interpolated text']);
        $text2 = $this->misc->interpolate('This is <text> with a different placeholder ...', ['text' => 'an interpolated text'], '<>');
        $text3 = $this->misc->interpolate('This is [text] with a different placeholder ...', ['text' => 'an interpolated text'], '[|]');

        $this->assertEquals('This is an interpolated text ...', $text1);
        $this->assertEquals('This is an interpolated text with a different placeholder ...', $text2);
        $this->assertEquals('This is [text] with a different placeholder ...', $text3);
    }

    public function testMiscTransformMethod()
    {
        $clean      = $this->misc->transform('TestString-num.1', 'clean');
        $alnum      = $this->misc->transform('Test@123', 'alnum');
        $alpha      = $this->misc->transform('Test123', 'alpha');
        $numeric    = $this->misc->transform('Test123', 'numeric');
        $slug       = $this->misc->transform('Test+String', 'slug');
        $sentence   = $this->misc->transform('Test+String', 'sentence');
        $title      = $this->misc->transform('test string', 'title');
        $pascal     = $this->misc->transform('test string', 'pascal');
        $camel      = $this->misc->transform('test string', 'camel');
        $constant   = $this->misc->transform('Test String', 'constant');
        $cobol      = $this->misc->transform('Test String', 'cobol');
        $train      = $this->misc->transform('Test String', 'train');
        $snake      = $this->misc->transform('Test String', 'snake');
        $kebab      = $this->misc->transform('Test String', 'kebab');
        $dot        = $this->misc->transform('Test String', 'dot');
        $spaceless  = $this->misc->transform('Test String', 'spaceless');
        $lower      = $this->misc->transform('Test String', 'lower');
        $upper      = $this->misc->transform('Test String', 'upper');
        $strtolower = $this->misc->transform('Test String', 'strtolower');

        $this->assertEquals('Test String num 1', $clean);
        $this->assertEquals('Test123', $alnum);
        $this->assertEquals('Test', $alpha);
        $this->assertEquals('123', $numeric);
        $this->assertEquals('test-string', $slug);
        $this->assertEquals('Test string', $sentence);
        $this->assertEquals('Test String', $title);
        $this->assertEquals('TestString', $pascal);
        $this->assertEquals('testString', $camel);
        $this->assertEquals('TEST_STRING', $constant);
        $this->assertEquals('TEST-STRING', $cobol);
        $this->assertEquals('Test-String', $train);
        $this->assertEquals('test_string', $snake);
        $this->assertEquals('test-string', $kebab);
        $this->assertEquals('test.string', $dot);
        $this->assertEquals('TestString', $spaceless);
        $this->assertEquals('test string', $lower);
        $this->assertEquals('TEST STRING', $upper);
        $this->assertEquals('test string', $strtolower);
    }

    public function testMiscBacktraceMethod()
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


    private function getTestArray(): array
    {
        return [
            'prop1' => 'test',
            'prop2' => 123,
            'prop3' => [
                'sub1' => true,
                'sub2' => false,
                'sub3' => [
                    'nested' => null
                ],
            ],
        ];
    }

    private function getTestObjectInstance(): object
    {
        return new TestObjectMock();
    }
}
