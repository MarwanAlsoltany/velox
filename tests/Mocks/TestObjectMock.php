<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Mocks;

class TestObjectMock
{
    public const CONST_PROP = 'CONST';
    public static $staticProp = 'STATIC';
    private $privateProp = 'PRIVATE';
    protected $protectedProp = 'PROTECTED';
    public $publicProp = 'PUBLIC';

    public function __construct(
        $staticProp = 'STATIC',
        $privateProp = 'PRIVATE',
        $protectedProp = 'PROTECTED',
        $publicProp = 'PUBLIC'
    ) {
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
}
