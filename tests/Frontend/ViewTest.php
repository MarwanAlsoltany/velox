<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Frontend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Frontend\View;
use MAKS\Velox\Backend\Config;

class ViewTest extends TestCase
{
    private View $view;


    public function setUp(): void
    {
        parent::setUp();

        $this->view = new View();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->view);
    }


    public function testViewSectionMethods()
    {
        $this->view->section('test-section-1', 'Test Section 1');
        $testSection1 = $this->view->yield('test-section-1');
        $this->view->sectionReset('test-section-1');

        $this->assertEquals('Test Section 1', $testSection1);

        $testSectionX = $this->view->yield('test-section-x', 'Test Section X');

        $this->assertEquals('Test Section X', $testSectionX);

        $this->view->sectionStart('test-section-2');
        echo 'Test Section 2';
        $this->view->sectionEnd();
        $testSection2 = $this->view->yield('test-section-2');

        $this->assertEquals('Test Section 2', $testSection2);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Not in a context to end a section)/');

        $this->view->sectionEnd();
    }

    public function testViewIncludeMethod()
    {
        $this->view->include('partials/__default__');

        $this->expectOutputString('');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Could not load the file with the path)/');

        $this->view->include('partials/unknown');
    }

    public function testViewLayoutMethod()
    {
        $layout = $this->view->layout('__default__', ['name' => 'Test Layout']);

        $this->assertEquals('', $layout);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Could not load the file with the path)/');

        $this->view->layout('unknown');
    }

    public function testViewPageMethod()
    {
        $page = $this->view->page('__default__', ['name' => 'Test Page']);

        $this->assertEquals('', $page);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Could not load the file with the path)/');

        $this->view->page('unknown');
    }

    public function testViewPartialMethod()
    {
        $partial = $this->view->partial('__default__', ['name' => 'Test Partial']);

        $this->assertEquals('', $partial);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Could not load the file with the path)/');

        $this->view->partial('unknown');
    }

    public function testViewRenderMethod()
    {
        $view = $this->view->render('__default__', ['var' => 'Test'], '__default__');

        $this->assertStringContainsString('', $view);
    }

    public function testViewRenderMethodWithCacheAndClearCache()
    {
        Config::set('view.cache', true);
        $view1 = $this->view->render('__default__', ['var' => 'Test'], '__default__');

        Config::set('view.cacheExclude', []);
        $view2 = $this->view->render('__default__', ['var' => 'Test'], '__default__');

        Config::set('view.cacheWithTimestamp', false);
        $view3 = $this->view->render('error/404', ['path' => '/test'], 'base');

        Config::set('view.cacheAsIndex', true);
        $view4 = $this->view->render('__default__', ['var' => 'Test'], '__default__');

        Config::set('view.cache', false);
        Config::set('view.cacheExclude', ['__default__']);
        Config::set('view.cacheWithTimestamp', true);
        Config::set('view.cacheAsIndex', false);

        $this->assertEquals('', $view1);
        $this->assertEquals('', $view2);
        $this->assertNotEquals('', $view3);
        $this->assertStringContainsString('<!DOCTYPE html>', $view3);
        $this->assertStringNotContainsString('[CACHE]', $view3);
        $this->assertEquals('', $view4);

        $this->view->clearCache();

        rmdir(BASE_PATH . '/storage/cache/views');
    }
}
