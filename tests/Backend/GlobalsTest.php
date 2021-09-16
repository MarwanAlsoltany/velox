<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Backend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Backend\Globals;

class GlobalsTest extends TestCase
{
    private Globals $globals;


    public function setUp(): void
    {
        parent::setUp();

        $this->globals = new Globals();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->globals);
    }


    public function testGlobalsInstanceMethodReturnsAnInstance()
    {
        $this->assertInstanceOf(Globals::class, Globals::instance());
    }

    public function testGlobalsGetMethodWithDiffrentWaysOfWritingSuperglobalName()
    {
        $server1 = $this->globals->get('server');
        $server2 = $this->globals->get('SERVER');
        $server3 = $this->globals->get('_SERVER');

        $this->assertEquals($_SERVER, $server1);
        $this->assertEquals($_SERVER, $server2);
        $this->assertEquals($_SERVER, $server3);
    }

    public function testGlobalsGetMethodWithASpecificKey()
    {
        $path1 = $this->globals->get('server', 'PATH');
        $path2 = getenv('PATH');

        $this->assertEquals($path1, $path2);
    }

    public function testGlobalsGetMethodThrowsAnExceptionIfThePassedNameIsNotASuperglobal()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(There is no PHP superglobal with the name)/');

        $this->globals->get('unknown');
    }

    public function testGlobalsSetMethodSetsASuperglobalAndReturnsSelf()
    {
        $oldPath = $_SERVER['PATH'];
        $newPath = explode(PATH_SEPARATOR, $oldPath)[0];

        $globals = $this->globals->set('server', 'PATH', $newPath);

        $this->assertInstanceOf(Globals::class, $globals);

        $currentGlobalPath1 = $_SERVER['PATH'];
        $currentGlobalPath2 = $this->globals->get('server', 'PATH');

        $this->assertNotEquals($oldPath, $newPath);
        $this->assertEquals($currentGlobalPath1, $newPath);
        $this->assertEquals($currentGlobalPath2, $newPath);

        // reset original PATH
        $this->globals->set('server', 'PATH', $oldPath);
    }

    public function testGlobalsSetMethodThrowsAnExceptionIfThePassedNameIsNotASuperglobal()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(There is no PHP superglobal with the name)/');

        $this->globals->set('unknown', 'key', 'value');
    }

    public function testGlobalsMagicGetMethod()
    {
        $path1 = $this->globals->getServer('PATH');
        $path2 = getenv('PATH');

        $this->assertEquals($path1, $path2);
    }

    public function testGlobalsMagicGetMethodThrowsAnExceptionIfMethodNameDoesNotMatchASuperglobalName()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Call to undefined method)/');

        $this->globals->getUnknown();
    }

    public function testGlobalsMagicSetMethod()
    {
        $oldPath = $_SERVER['PATH'];
        $newPath = explode(PATH_SEPARATOR, $oldPath)[0];

        $globals = $this->globals->setServer('PATH', $newPath);

        $this->assertInstanceOf(Globals::class, $globals);

        $currentGlobalPath1 = $_SERVER['PATH'];
        $currentGlobalPath2 = $this->globals->getServer('PATH');

        $this->assertNotEquals($oldPath, $newPath);
        $this->assertEquals($currentGlobalPath1, $newPath);
        $this->assertEquals($currentGlobalPath2, $newPath);

        // reset original PATH
        $this->globals->setServer('PATH', $oldPath);
    }

    public function testGlobalsMagicSetMethodThrowsAnExceptionIfMethodNameDoesNotMatchASuperglobalName()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Call to undefined method)/');

        $this->globals->setUnknown('key', 'value');
    }

    public function testGetAllMethodReturnsAllSuperglobals()
    {
        $availableGlobals = $this->globals->getAll();
        $existingGlobals  = Globals::GLOBALS;

        $zero = count(array_diff_key(
            array_keys($availableGlobals),
            array_keys($existingGlobals)
        ));

        $this->assertEquals($zero, 0);
    }

    public function testGlobalsMagicGetReturnASuperglobalAsClass()
    {
        $this->setTestObjectProperty($this->globals, 'isInitialized', false);

        $newA1 = $this->globals->setServer('NEW', 123);
        $newA2 = $this->globals->server->set('NEW', 123);
        $this->assertNotEquals($newA1, $newA2);

        $newB1 = $this->globals->getServer('NEW');
        $newB2 = $this->globals->server->get('NEW');
        $this->assertEquals($newB1, $newB2);

        $this->assertTrue($this->globals->server->has('PATH'));
        $this->assertFalse($this->globals->server->has('UNKNOWN'));

        $globals1 = $this->globals->getServer();
        $globals2 = $this->globals->server->getAll();

        $zero = count(array_diff_key(
            array_keys($globals1),
            array_keys($globals2)
        ));

        $this->assertEquals($zero, 0);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Call to undefined property)/');

        $this->globals->unknown;
    }
}
