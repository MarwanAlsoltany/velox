<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Frontend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Frontend\Path;
use MAKS\Velox\Backend\Globals;

/**
 * @resetStaticProperties disabled
 */
class PathTest extends TestCase
{
    private Path $path;


    public function setUp(): void
    {
        parent::setUp();

        $this->path = new Path();

        // Superglobals needed in order for the Path class to work
        Globals::setServer('HTTPS', 'on');
        Globals::setServer('HTTP_HOST', 'velox.test');
        Globals::setServer('REQUEST_URI', '/');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->path);
    }


    public function testPathCurrentMethodReturnsCurrentPathAndComparesItCorrectly()
    {
        $this->assertIsString($this->path->current());
        $this->assertEquals('/', $this->path->current());
        $this->assertTrue($this->path->current('/'));
        $this->assertFalse($this->path->current('/some-page'));
    }

    public function testPathCurrentUrlMethodReturnsCurrentUrlAndComparesItCorrectly()
    {
        $this->assertIsString($this->path->currentUrl());
        $this->assertEquals('https://velox.test/', $this->path->currentUrl());
        $this->assertTrue($this->path->currentUrl('https://velox.test/'));
        $this->assertFalse($this->path->currentUrl('https://velox.test/some-page'));
    }


    public function testPathResolveMethodReturnsCorrectPath()
    {
        $testPath = BASE_PATH . DIRECTORY_SEPARATOR . 'tests';

        $this->assertIsString($this->path->resolve('/'));
        $this->assertEquals($testPath, $this->path->resolve('/tests'));
    }

    public function testPathResolveUrlMethodReturnsCorrectUrl()
    {
        $testUrl = 'https://velox.test/some-page';

        $this->assertIsString($this->path->resolveUrl('/some-page'));
        $this->assertEquals($testUrl, $this->path->resolveUrl('/some-page'));
    }

    public function testPathResolveFromThemeMethodReturnsCorrectPath()
    {
        $testPath = '/themes/velox/assets/images/velox.png';

        $this->assertIsString($this->path->resolveFromTheme('/assets/images/velox.png'));
        $this->assertEquals($testPath, $this->path->resolveFromTheme('/assets/images/velox.png'));
    }

    public function testPathResolveUrlFromThemeMethodReturnsCorrectUrl()
    {
        $testUrl = 'https://velox.test/themes/velox/assets/images/velox.png';

        $this->assertIsString($this->path->resolveUrlFromTheme('/assets/images/velox.png'));
        $this->assertEquals($testUrl, $this->path->resolveUrlFromTheme('/assets/images/velox.png'));
    }

    public function testPathResolveFromAssetsMethodReturnsCorrectPath()
    {
        $testPath = '/themes/velox/assets/images/velox.png';

        $this->assertIsString($this->path->resolveFromAssets('/images/velox.png'));
        $this->assertEquals($testPath, $this->path->resolveFromAssets('/images/velox.png'));
    }

    public function testPathResolveUrlFromAssetsMethodReturnsCorrectUrl()
    {
        $testUrl = 'https://velox.test/themes/velox/assets/images/velox.png';

        $this->assertIsString($this->path->resolveUrlFromAssets('/images/velox.png'));
        $this->assertEquals($testUrl, $this->path->resolveUrlFromAssets('/images/velox.png'));
    }

    public function testPathMagicMethods()
    {
        $testPath = '/themes/velox/assets/images/velox.png';

        $this->assertIsString($this->path->fromAssets('/images/velox.png'));
        $this->assertEquals($testPath, $this->path->fromAssets('/images/velox.png'));


        $testUrl = 'https://velox.test/themes/velox/assets/images/velox.png';
        $this->assertIsString($this->path->urlFromAssets('/images/velox.png'));
        $this->assertEquals($testUrl, $this->path->urlFromAssets('/images/velox.png'));
    }

    public function testPathMagicMethodsThrowAnExceptionIfAnUndefinedMethodIsCalled()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Call to undefined method)/');

        $this->path->unknownMethod();
    }
}
