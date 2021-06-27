<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Closure;
use Exception;

class TestCase extends BaseTestCase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }


    /**
     * Gets a private, protected, or public property (default, static, or constant) of an object.
     * @param object $object Class instance.
     * @param string $property Property name.
     * @return mixed The property value.
     * @throws Exception On failure.
     */
    public static function getTestObjectProperty($object, string $property)
    {
        return call_user_func(
            Closure::bind(
                function () use ($object, $property) {
                    $return = null;
                    try {
                        $class = get_class($object);
                        if (defined($class . '::' . $property)) {
                            $return = constant($class . '::' . $property);
                        } elseif (isset($object::$$property)) {
                            $return = $object::$$property;
                        } elseif (isset($object->{$property})) {
                            $return = $object->{$property};
                        } else {
                            throw new Exception(
                                sprintf(
                                    'No default, static, or constant property with the name "%s" exists!',
                                    $property
                                )
                            );
                        }
                    } catch (Exception $error) {
                        throw new Exception(sprintf('%s::%s() failed!', static::class, __FUNCTION__), 0, $error);
                    }
                    return $return;
                },
                null,
                $object
            )
        );
    }

    /**
     * Sets a private, protected, or public property (default or static) of an object.
     * @param object $object Class instance.
     * @param string $property Property name.
     * @param string $value Property value.
     * @return mixed The new property value.
     * @throws Exception On failure.
     */
    public static function setTestObjectProperty($object, string $property, $value)
    {
        return call_user_func(
            Closure::bind(
                function () use ($object, $property, $value) {
                    $return = null;
                    try {
                        if (isset($object::$$property)) {
                            $return = $object::$$property = $value;
                        } elseif (isset($object->{$property})) {
                            $return = $object->{$property} = $value;
                        } else {
                            throw new Exception(
                                sprintf(
                                    'No default or static property with the name "%s" exists!',
                                    $property
                                )
                            );
                        }
                    } catch (Exception $error) {
                        throw new Exception(sprintf('%s::%s() failed!', static::class, __FUNCTION__), 0, $error);
                    }
                    return $return;
                },
                null,
                $object
            )
        );
    }
}
