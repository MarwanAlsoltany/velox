<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Frontend\Data;

class DataTest extends TestCase
{
    private Data $data;


    public function setUp(): void
    {
        parent::setUp();

        $this->data = new Data();
        $this->data->set('test', [
            'null' => null,
            'array' => [
                'string' => 'text',
                'integer' => 0,
                'boolean' => false,
                'object' => new \stdClass()
            ]
        ]);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->data->set('test', null);

        unset($this->data);
    }


    public function testDataHas()
    {
        $true  = $this->data->has('test.array');
        $false = $this->data->has('test.object');

        $this->assertTrue($true);
        $this->assertFalse($false);
    }

    public function testDataGet()
    {
        $value1 = $this->data->get('test.array.string');
        $value2 = $this->data->get('test.array.integer');

        $this->assertNotEquals($value1, $value2);
    }

    public function testDataSet()
    {
        $this->data->set('test.array.null', 'not-null');
        $value = $this->data->get('test.array.null');

        $this->assertEquals('not-null', $value);
    }

    public function testDataGetAll()
    {
        $data = $this->data->getAll();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('null', $data['test']);
        $this->assertArrayHasKey('array', $data['test']);
    }
}
