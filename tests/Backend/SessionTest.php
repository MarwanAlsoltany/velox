<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Backend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Backend\Session;
use MAKS\Velox\Backend\Config;

class SessionTest extends TestCase
{
    private Session $session;


    public function setUp(): void
    {
        parent::setUp();

        $this->session = new Session();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->session);
    }


    public function testSessionFlashMethodAndObject(): void
    {
        $_SESSION = [];

        $flash = $this->session->flash();

        $this->assertIsObject($flash);

        $flash = $this->session->flash('notification', 'Test', true);
        $html  = $flash->render();

        $this->assertStringContainsString('notification', $html);
        $this->assertStringContainsString('Test', $html);
        $this->assertNotEquals(strip_tags($html), $html);

        $_SESSION = [];

        $flash = $this->session->flash()->message('notification', 'Test', true);
        $html  = $flash->render();

        $this->assertStringContainsString('Test', $html);
        $this->assertStringContainsString('notification', $html);

        $_SESSION = [];

        $flash = $this->session->flash()->isSuccess('Test 1', true); // this will render now
        $flash = $this->session->flash()->isDanger('Test 2'); // this will render later
        $html  = $flash->render();

        $this->assertStringContainsString('Test 1', $html);
        $this->assertStringContainsString('is-success', $html);
        $this->assertStringNotContainsString('Test 2', $html);
        $this->assertStringNotContainsString('is-danger', $html);

        $_SESSION = [];

        $flash = $this->session->flash()->success('Test 1', true); // this will render now
        $html  = (string)$flash;

        $this->assertStringContainsString('Test 1', $html);
        $this->assertStringContainsString('success', $html);

        $_SESSION = [];
    }

    public function testSessionCsrfMethodAndObject(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $csrf = $this->session->csrf('_test');

        $this->assertIsObject($csrf);

        $token = $csrf->token();

        $this->assertStringContainsString($token, (string)$csrf);

        $_POST['_test'] = $token;

        $this->assertTrue($csrf->isValid());

        $_POST['_test'] = 'invalid';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Exit)/');
        $this->expectOutputRegex('/(Invalid CSRF token)/');

        $csrf->check();

        Config::set('global.errorPages.403', null);

        $csrf->token();

        $_POST['_test'] = 'invalid';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Exit)/');
        $this->expectOutputRegex('/(Invalid CSRF token)/');

        $csrf->check();
    }

    public function testSessionStartMethod(): void
    {
        $true = $this->session->start();

        $this->assertTrue($true);
    }

    public function testSessionHasMethod(): void
    {
        $_SESSION['key'] = 'value';

        $true = $this->session->has('key');

        $this->assertTrue($true);

        $_SESSION = [];
    }

    public function testSessionGetMethod(): void
    {
        $_SESSION['key'] = 'value';

        $key = $this->session->get('key');

        $this->assertEquals($key, $_SESSION['key']);

        $_SESSION = [];
    }

    public function testSessionSetMethod(): void
    {
        $this->session->set('key', 'value');

        $this->assertEquals('value', $_SESSION['key']);

        $_SESSION = [];
    }

    public function testSessionCutMethod(): void
    {
        $_SESSION['key'] = 'value';

        $key = $this->session->cut('key');

        $this->assertEquals($key, 'value');
        $this->assertArrayNotHasKey('key', $_SESSION);

        $_SESSION = [];
    }

    public function testSessionClearMethod(): void
    {
        $this->expectException(\ErrorOrWarningException::class);
        $this->expectExceptionMessageMatches('/(Cannot modify header information)/');

        $this->session->clear();
    }

    public function testSessionUnsetMethod(): void
    {
        $true = $this->session->unset();

        $this->assertTrue($true);
    }

    public function testSessionDestroyMethod(): void
    {
        $true = $this->session->destroy();

        $this->assertTrue($true);
    }
}
