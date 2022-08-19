<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Helper;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Helper\Dumper;

class DumperTest extends TestCase
{
    private Dumper $dumper;


    public function setUp(): void
    {
        parent::setUp();

        $this->dumper = new Dumper();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->dumper);
    }


    public function testDumperDumpMethod()
    {
        $this->expectOutputRegex('/(string)/');
        $this->expectOutputRegex('/(This is a string!)/');
        $this->expectOutputRegex('/(integer)/');
        $this->expectOutputRegex('/(1234567890)/');
        $this->expectOutputRegex('/(boolean)/');
        $this->expectOutputRegex('/(true)/');
        $this->expectOutputRegex('/(false)/');
        $this->expectOutputRegex('/(null)/');
        $this->expectOutputRegex('/(NULL)/');
        $this->expectOutputRegex('/(object)/');

        $this->dumper->dump([
            'string' => 'This is a string!',
            'integer' => 1234567890,
            'boolean' => [true, false],
            'null' => null,
            'object' => new \stdClass()
        ]);
    }

    public function testDumperDumpMethodFailure()
    {
        $array = $this->getSelfReferencingArray();

        $this->expectOutputRegex('/(failed to dump the variable)/');

        $this->dumper->dump($array);
    }

    public function testDumperDumpMethodWithVarDump()
    {
        $array = $this->getSelfReferencingArray();

        Dumper::$useVarDump = true;

        $this->expectOutputRegex('/(__RECURSION__)/');

        $this->dumper->dump($array);
    }


    private function getSelfReferencingArray(): array
    {
        $array = [
            'null'    => null,
            'true'    => true,
            'false'   => false,
            'string'  => 'string',
            'integer' => 1234567890,
            'array'   => [],
            'object'  => (object)[],
        ];

        $array['array'] = &$array;
        $array['object']->self = &$array['object'];

        return $array;
    }
}
