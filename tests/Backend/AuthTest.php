<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Backend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Tests\Mocks\DatabaseMock;
use MAKS\Velox\Backend\Auth;
use MAKS\Velox\Backend\Config;

class AuthTest extends TestCase
{
    private Auth $auth;


    public function setUp(): void
    {
        parent::setUp();

        $this->cleanUp();

        $this->auth = $this->getAuthInstance();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->cleanUp();

        unset($this->auth);
    }


    public function testAuthInstanceMethod()
    {
        $this->assertInstanceOf(Auth::class, Auth::instance());
    }

    public function testAuthRegisterMethod()
    {
        $true = $this->auth->register('test', 'test');

        $this->assertTrue($true);

        $false = $this->auth->register('test', 'test');

        $this->assertFalse($false);
    }

    public function testAuthLoginMethod()
    {
        $this->auth->register('test', 'test');

        $true = $this->auth->login('test', 'test');

        $this->assertTrue($true);

        $false = $this->auth->login('test', 'test2');

        $this->assertFalse($false);
    }

    public function testAuthLogoutMethod()
    {
        $this->auth->register('test', 'test');

        $this->auth->login('test', 'test');

        $this->auth->logout();

        $this->assertFalse($this->auth->check());
    }

    public function testAuthCheckMethod()
    {
        $this->auth->register('test', 'test');

        $true = $this->auth->login('test', 'test');

        $this->assertTrue($true);

        $true = $this->auth->check();

        $this->assertTrue($true);

        $this->auth->logout();

        $false = $this->auth->check();

        $this->assertFalse($false);

        Config::set('auth.user.timeout', -60);

        $true = $this->auth->login('test', 'test');

        $this->assertTrue($true);

        // user will be logged out as the timeout is already expired
        $false = $this->auth->check();

        $this->assertFalse($false);

        $this->auth->unregister('test');

        Config::set('auth.user.timeout', 60);
    }

    public function testAuthUserMethod()
    {
        $this->auth->register('test', 'test');

        $true = $this->auth->login('test', 'test');

        $this->assertTrue($true);

        $user = $this->auth->user();

        $this->assertIsObject($user);

        $this->auth->logout();

        $null = $this->auth->user();

        $this->assertNull($null);
    }

    public function testAuthUnregisterMethod()
    {
        $this->auth->register('test', 'test');

        $this->auth->login('test', 'test');

        $true = $this->auth->unregister('test');

        $this->assertTrue($true);

        $false = $this->auth->unregister('test');

        $this->assertFalse($false);
    }

    public function testAuthAuthenticateMethod()
    {
        $this->auth->register('test', 'test');

        $this->auth->login('test', 'test');

        $user = $this->auth->user();

        $this->auth->logout();

        $null = $this->auth->authenticate($user);

        $this->assertNull($null);

        $this->auth->unregister('test');

        $model = Config::get('auth.user.model');
        $user  = new $model();
        $user->setId(100);
        $user->setUsername('unknown');
        $user->setPassword('unknown');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Could not authenticate auth user)/i');

        $this->auth->authenticate($user);
    }

    private function getAuthInstance(): Auth
    {
        $auth = new Auth();

        $model = Config::get('auth.user.model');
        $user  = new $model();

        $database = DatabaseMock::instance();

        $this->setTestObjectProperty($user, 'database', $database);
        $this->setTestObjectProperty($auth, 'user', $user);

        return $auth;
    }

    private function cleanUp(): void
    {
        $model = Config::get('auth.user.model');
        $table = (new $model())->getTable();

        DatabaseMock::instance()->exec(sprintf('DROP TABLE IF EXISTS `%s`', $table));
    }
}
