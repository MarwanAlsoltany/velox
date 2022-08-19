<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend\Model;

use MAKS\Velox\Backend\Exception;
use MAKS\Velox\Backend\Model\DBAL;
use MAKS\Velox\Helper\Misc;

/**
 * An abstract class that holds the base functionality of a model.
 * NOTE: This class is not meant to be used directly.
 *
 * @package Velox\Backend\Model
 * @since 1.5.1
 */
abstract class Element extends DBAL implements \ArrayAccess, \Traversable, \IteratorAggregate
{
    /**
     * Model attributes. Corresponds to table columns.
     */
    protected array $attributes;


    /**
     * Class constructor.
     *
     * Keep all constructor arguments optional when extending the class.
     * Or use `self::bootstrap()` instead.
     *
     * @param array $attributes [optional] The attributes to set on the model.
     */
    public function __construct(?array $attributes = [])
    {
        $this->attributes = array_merge(array_fill_keys($this->getColumns(), null), $attributes ?? []);

        if ($this->isMigrated() === false) {
            $this->migrate();
        }

        $this->bootstrap();
    }

    /**
     * Override this method to add your own bootstrap code to the modal.
     *
     * @return void
     */
    protected function bootstrap(): void
    {
        // implemented as needed
    }

    /**
     * Asserts that the model attribute name is valid.
     *
     * @param mixed $name The name to validate.
     *
     * @return void
     *
     * @throws \OutOfBoundsException If attribute name is not a part of model `$columns`.
     */
    protected static function assertAttributeExists($name): void
    {
        $columns = static::getColumns();

        if (!in_array((string)$name, $columns)) {
            Exception::throw(
                'UnknownAttributeException:OutOfBoundsException',
                sprintf('Cannot find attribute with the name "%s". %s model table does not consist of this column', $name, static::class)
            );
        }
    }

    /**
     * Gets the specified model attribute.
     *
     * @param string $name Attribute name as specified in `$columns`.
     *
     * @return mixed Attribute value.
     *
     * @throws \OutOfBoundsException If the attribute does not exists.
     */
    public function get(string $name)
    {
        $this->assertAttributeExists($name);

        return $this->attributes[$name];
    }

    /**
     * Sets the specified model attribute.
     *
     * @param string $name Attribute name as specified in `$columns`.
     * @param mixed $value Attribute value.
     *
     * @return $this
     *
     * @throws \OutOfBoundsException If the attribute does not exists.
     */
    public function set(string $name, $value): self
    {
        $this->assertAttributeExists($name);

        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Gets all model attributes.
     *
     * @return array Model attributes.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Sets all or a subset of model attributes.
     *
     * @param array $attributes Model attributes.
     *
     * @return $this
     */
    public function setAttributes(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            in_array($key, $this->getColumns()) && $this->set($key, $value);
        }

        return $this;
    }


    /**
     * Makes attributes accessible via public property access notation.
     * Examples: `model_id` as `$model->modelId`
     */
    public function __get(string $name)
    {
        $name = Misc::transform($name, 'snake');

        return $this->get($name);
    }

    /**
     * Makes attributes accessible via public property assignment notation.
     * Examples: `model_id` as `$model->modelId`
     */
    public function __set(string $name, $value)
    {
        $name = Misc::transform($name, 'snake');

        return $this->set($name, $value);
    }

    /**
     * Makes attributes consumable via `isset()`.
     */
    public function __isset(string $name)
    {
        $name = Misc::transform($name, 'snake');

        return $this->get($name) !== null;
    }

    /**
     * Makes attributes consumable via `unset()`.
     */
    public function __unset(string $name)
    {
        $name = Misc::transform($name, 'snake');

        return $this->set($name, null);
    }

    /**
     * Makes the model safely cloneable via the `clone` keyword.
     */
    public function __clone()
    {
        $this->set($this->getPrimaryKey(), null);
    }

    /**
     * Makes the model safely consumable via `serialize()`.
     */
    public function __sleep()
    {
        return ['attributes'];
    }


    /**
     * `ArrayAccess::offsetGet()` interface implementation.
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * `ArrayAccess::offsetSet()` interface implementation.
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * `ArrayAccess::offsetExists()` interface implementation.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return $this->get($offset) !== null;
    }

    /**
     * `ArrayAccess::offsetUnset()` interface implementation.
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        $this->set($offset, null);
    }


    /**
     * `IteratorAggregate::getIterator()` interface implementation.
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->attributes);
    }
}
