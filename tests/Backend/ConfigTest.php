<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Backend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Backend\Config;

class ConfigTest extends TestCase
{
    private Config $config;


    public function setUp(): void
    {
        parent::setUp();

        $this->config = new Config();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->config);
    }


    public function testConfigObjectWhenCastingItToAString()
    {
        $configString = (string)(new Config());

        $this->assertIsString($configString);
        $this->assertEquals(realpath($configString), realpath(BASE_PATH . '/config'));
    }

    public function testConfigHasMethod()
    {
        $this->setTestObjectProperty($this->config, 'config', []);

        $true  = $this->config->has('global.paths.root');
        $false = $this->config->has('global.paths.base');

        $this->assertTrue($true);
        $this->assertFalse($false);
    }

    public function testConfigGetMethod()
    {
        $this->setTestObjectProperty($this->config, 'config', []);

        $value1 = $this->config->get('global.paths.root');
        $value2 = BASE_PATH;

        $this->assertEquals($value1, $value2);
    }

    public function testConfigSetMethod()
    {
        $this->setTestObjectProperty($this->config, 'config', []);

        $this->config->set('test.data', 'test-data');

        $value = $this->config->get('test');

        $this->assertArrayHasKey('data', $value);
        $this->assertEquals('test-data', $value['data']);
    }

    public function testConfigGetAllMethod()
    {
        $this->setTestObjectProperty($this->config, 'config', []);

        $config1 = $this->config->getAll();

        $config2 = $this->getTestObjectProperty($this->config, 'config');

        $this->assertEquals($config1, $config2);
        $this->assertIsArray($config1);
        $this->assertArrayHasKey('global', $config1);
    }

    public function testConfigCacheAndClearCacheMethods()
    {
        $this->config->clearCache();

        $this->assertFileDoesNotExist(BASE_PATH . '/storage/cache/config/config.json');

        $this->config->cache();

        $this->assertFileExists(BASE_PATH . '/storage/cache/config/config.json');
    }

    public function testConfigCacheMethodWithExistingCache()
    {
        $this->config->cache();
        $config = $this->config->getAll();

        $this->assertIsArray($config);
    }

    public function testConfigGetAndSetMethodsWithCache()
    {
        $this->config->clearCache();

        $this->assertFileDoesNotExist(BASE_PATH . '/storage/cache/config/config.json');

        // The method call down below is done because we're trying to test
        // that when the cache is cleared then the cache file is deleted
        // and the currently loaded config is unset.
        // The test underneath expect the internal config array to be
        // empty when the clearCache() method is called, the twist is
        // the clearCache() method calls the Misc::log() method, which
        // calls the Config::get() method, which results in reloading the
        // config, so when trying to cache the config via Config::cache(),
        // it will cache the config but not load it.
        // The method call down below is used to reset the internal
        // config array to be empty again to cover this test case.
        $this->setTestObjectProperty($this->config, 'config', []);

        if (file_exists(BASE_PATH . '/storage/cache/config')) {
            rmdir(BASE_PATH . '/storage/cache/config');
        }

        if (!file_exists(BASE_PATH . '/config/test-dir')) {
            // for testing directory inclusion (recursive inclusion)
            mkdir(BASE_PATH . '/config/test-dir');
        }

        $this->config->cache();

        $this->assertFileExists(BASE_PATH . '/storage/cache/config/config.json');

        $get1 = $this->config->get('test.newValue', 'test-data');
        $this->config->set('test.newValue', 'test-data');
        $get2 = $this->config->get('test.newValue');

        $this->assertEquals($get1, 'test-data');
        $this->assertEquals($get2, 'test-data');

        $this->config->clearCache();

        if (file_exists(BASE_PATH . '/storage/cache/config')) {
            rmdir(BASE_PATH . '/storage/cache/config');
        }

        if (file_exists(BASE_PATH . '/config/test-dir')) {
            rmdir(BASE_PATH . '/config/test-dir');
        }
    }
}
