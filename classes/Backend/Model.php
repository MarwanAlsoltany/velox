<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend;

use MAKS\Velox\Backend\{Database, Model\Element};
use MAKS\Velox\Helper\Misc;

/**
 * An abstract class that serves as a base model that can be extended to create models.
 *
 * Example:
 * ```
 * // Attributes has to match the attribute name (column name) unless otherwise specified.
 *
 * // creating/manipulating models
 * $model = new Model(); // set attributes later via setters or public assignment.
 * $model = new Model(['attribute_name' => $value]);
 * $model->get('attribute_name');
 * $model->set('attribute_name', $value);
 * $model->getAttributeName(); // case will be changed to 'snake_case' automatically.
 * $model->setAttributeName($value); // case will be changed to 'snake_case' automatically.
 * $model->anotherAttribute; // case will be changed to 'snake_case' automatically.
 * $model->anotherAttribute = $value; // case will be changed to 'snake_case' automatically.
 * $attributes = $model->getAttributes(); // returns all attributes.
 * $model->save(); // persists the model in the database.
 * $model->update(['attribute_name' => $value]); // updates the model and save changes in the database.
 * $model->delete(); // deletes the model from the database.
 * Model::create($attributes); // creates a new model instance, call save() on the instance to save it in the database.
 * Model::destroy($id); // destroys a model and deletes it from the database.
 *
 * // fetching models
 * $model = Model::first();
 * $model = Model::last();
 * $model = Model::one();
 * $models = Model::all(['name' => 'John'], 'age DESC', $offset, $limit);
 * $count = Model::count(); // returns the number of models in the database.
 * $model = Model::find($id); // $id is the primary key of the model.
 * $models = Model::find('age', 27, 'name', 'John', ...); // or Model::find(['name' => $value]);
 * $models = Model::findByName('John'); // fetches using an attribute, case will be changed to 'snake_case' automatically.
 * $models = Model::where('name', '=', $name); // fetches using a where clause condition.
 * $models = Model::where('name', 'LIKE', 'John%', [['AND', 'age', '>', 27], ...], 'age DESC', $limit, $offset);
 * $models = Model::fetch('SELECT * FROM @table WHERE `name` = ?', [$name]); // fetches using raw SQL query.
 * ```
 *
 * @package Velox\Backend
 * @since 1.3.0
 * @api
 *
 * @method mixed getSomeAttribute() Getter for model attribute, (`attribute_name` -> `getAttributeName()`).
 * @method $this setSomeAttribute($value) Setter for model attribute, (`attribute_name` -> `setAttributeName($value)`).
 * @method static[] findBySomeAttribute($value) Finder by model attribute, (`attribute_name` -> `findByAttributeName($value)`).
 *
 * @property mixed $attributeName* Public property for model attribute, (`attribute_name` -> `attributeName`).
 */
abstract class Model extends Element
{
    /**
     * Creates an instance of the model. The model is not saved to the database unless `self::save()` is called.
     *
     * @param array $attributes The attributes of the model.
     *
     * @return static
     */
    public static function create(array $attributes): Model
    {
        $item = new static();
        $item->setAttributes($attributes);

        return $item;
    }

    /**
     * Creates/updates a model and saves it in database.
     *
     * @param array $attributes Model attributes.
     *
     * @return static
     */
    public function save(array $attributes = []): Model
    {
        $isNew = !$this->get($this->getPrimaryKey());

        $this->setAttributes($attributes);

        $attributes = $this->getAttributes();
        $variables  = [];

        foreach ($attributes as $key => $value) {
            if ($isNew && $key === $this->getPrimaryKey()) {
                unset($attributes[$key]);
                continue;
            }

            $variables[':' . $key] = $value;
        }

        $query = vsprintf('%s INTO `%s` (%s) VALUES(%s);', [
            $isNew ? 'INSERT' : 'REPLACE',
            $this->getTable(),
            implode(', ', array_keys($attributes)),
            implode(', ', array_keys($variables)),
        ]);

        $id = $this->getDatabase()->transactional(function () use ($query, $variables) {
            /** @var Database $this */
            $this->perform($query, $variables);
            return $this->lastInsertId();
        });

        $this->set($this->getPrimaryKey(), is_numeric($id) ? (int)$id : $id);

        return $this;
    }

    /**
     * Updates the given attributes of the model and saves them in the database.
     *
     * @param array $attributes The attributes to update.
     *
     * @return static
     */
    public function update(array $attributes): Model
    {
        $variables = [];

        foreach ($attributes as $key => $value) {
            $this->set($key, $value);

            $variables[':' . $key] = $value;
        }

        $variables[':id'] = $this->get($this->getPrimaryKey());

        $query = vsprintf('UPDATE `%s` SET %s WHERE `%s` = :id;', [
            $this->getTable(),
            implode(', ', array_map(fn ($key) => sprintf('`%s` = :%s', $key, $key), array_keys($attributes))),
            $this->getPrimaryKey(),
        ]);

        $this->getDatabase()->transactional(function () use ($query, $variables) {
            /** @var Database $this */
            $this->perform($query, $variables);
        });

        return $this;
    }

    /**
     * Deletes the model from the database.
     *
     * @return int The number of affected rows during the SQL operation.
     */
    public function delete(): int
    {
        $query = vsprintf('DELETE FROM `%s` WHERE `%s` = :id LIMIT 1;', [
            $this->getTable(),
            $this->getPrimaryKey(),
        ]);

        $variables = [':id' => $this->get($this->getPrimaryKey())];

        return $this->getDatabase()->transactional(function () use ($query, $variables) {
            /** @var Database $this */
            return $this->perform($query, $variables)->rowCount();
        });
    }

    /**
     * Destroys (deletes) a model from the database if it exists.
     *
     * @param string|int $primaryKey
     *
     * @return int The number of affected rows during the SQL operation.
     */
    public static function destroy($primaryKey): int
    {
        $model = static::find($primaryKey);

        return $model ? $model->delete() : 0;
    }

    /**
     * Hydrates models from an array of model attributes (row).
     *
     * @param array $data Array of objects data.
     *
     * @return static[] Array of hydrated objects.
     *
     * Example:
     * - `Model::hydrate([$arrayOfModelAttributes, ...])`
     */
    public static function hydrate(array $models): array
    {
        $objects = [];
        foreach ($models as $model) {
            $objects[] = static::create($model);
        }

        return $objects;
    }


    /**
     * Fetches all models.
     *
     * @param array $conditions Fetch conditions (like: `['id' => $id, ...]`). Conditions are combined by logical `AND`.
     * @param string|null $order [optional] SQL order expression (like: `id` or `id ASC`).
     * @param int|null $limit [optional] To how many items the result should be limited.
     * @param int|null $offset [optional] From which item the result should start.
     *
     * @return static[]|array
     *
     * Examples:
     * - PHP: `Model::all(['name' => 'Doe', 'job' => 'Developer'],'age DESC', 3, 15)`.
     * - SQL: ```SELECT * FROM `users` WHERE `name` = "Doe" AND `job` = `Developer` ORDER BY age DESC LIMIT 3 OFFSET 15```.
     */
    public static function all(?array $conditions = [], ?string $order = null, ?int $limit = null, ?int $offset = null): array
    {
        $query = 'SELECT * FROM @table';

        if (!empty($conditions)) {
            $sqlConditions = [];
            foreach ($conditions as $key => $value) {
                static::assertAttributeExists($key);
                $sqlConditions[] = sprintf('`%s` = :%s', $key, $key);
            }

            $query .= ' WHERE ' . implode(' AND ', $sqlConditions);
        }

        if ($order !== null) {
            $query .= ' ORDER BY ' . $order;
        }

        if ($limit !== null) {
            $query .= ' LIMIT ' . $limit;
        }

        if ($offset !== null) {
            $query .= ' OFFSET ' . $offset;
        }

        $query .= ';';

        return static::fetch($query, $conditions);
    }

    /**
     * Fetches a single model object.
     *
     * @param array $conditions [optional] Query conditions (like: `['id' => 1, ...]`). Conditions are combined by logical `AND`.
     *
     * @return static|null
     *
     * Examples:
     * - Fetching the first items: `Model::one()`.
     * - Fetching an item according to a condition: `Model::one(['name' => $name])`.
     */
    public static function one(?array $conditions = []): ?Model
    {
        return static::all($conditions, null, 1, null)[0] ?? null;
    }

    /**
     * Fetches the first model object.
     *
     * @return static|null
     *
     * Examples: `Model::first()`.
     */
    public static function first(): ?Model
    {
        return static::all(null, static::getPrimaryKey() . ' ASC', 1, 0)[0] ?? null;
    }

    /**
     * Fetches the last model object.
     *
     * @return static|null
     *
     * Example: `Model::last()`.
     */
    public static function last(): ?Model
    {
        return static::all(null, static::getPrimaryKey() . ' DESC', 1, 0)[0] ?? null;
    }

    /**
     * Finds a single or multiple models matching the passed condition.
     *
     * @param mixed|mixed[] ...$condition Can either be the primary key or a set of condition(s) (like: `id`, or `'name', 'Doe', 'age', 35`, or `['name' => $name]`).
     *
     * @return static|static[]|null|array Depends on the number of conditions (1 = single, >1 = multiple).
     *
     * Examples:
     * - Find by primary key (ID): `Model::find(1)`.
     * - Find by specific value: `Model::find('name', 'Doe', 'age', 35, ...)` or `Model::find(['name' => $name, 'age' => 35], ...)`.
     *
     */
    public static function find(...$condition)
    {
        // formats conditions to be consumed as `$name, $value, $name, $value, ...`
        $format = function ($array) use (&$format) {
            $pairs = array_map(function ($key, $value) use (&$format) {
                if (is_string($key)) {
                    return [$key, $value];
                }

                if (is_array($value)) {
                    return $format($value);
                }

                return [$value];
            }, array_keys($array), $array);

            return array_values((array)array_merge(...$pairs));
        };

        $pairs = $format($condition);
        $count = count($pairs);

        if ($count === 1) {
            return static::one([static::getPrimaryKey() => current($condition)]);
        }

        $conditions = [];
        for ($i = 0; $i < $count; $i++) {
            if ($i % 2 === 0) {
                $conditions[$pairs[$i]] = $pairs[$i + 1];
            }
        }

        return static::all($conditions);
    }

    /**
     * Returns the count of models matching the passed condition (counting is done on the SQL end for better performance).
     *
     * @param array $conditions [optional] Query conditions (like: `['id' => 1, ...]`). Conditions are combined by logical `AND`.
     *
     * @return int
     */
    public static function count(?array $conditions = []): int
    {
        $query = 'SELECT COUNT(*) FROM @table';

        if (!empty($conditions)) {
            $sqlConditions = [];
            foreach ($conditions as $key => $value) {
                static::assertAttributeExists($key);
                $sqlConditions[] = sprintf('`%s` = :%s', $key, $key);
            }

            $query .= ' WHERE ' . implode(' AND ', $sqlConditions) . ';';
        }

        $data = static::fetch($query, $conditions, true);

        return $data ? $data[0]['COUNT(*)'] ?? 0 : 0;
    }


    /**
     * Returns array representation of the model. All attributes will be converted to `camelCase` form.
     */
    public function toArray(): array
    {
        $attributes = $this->getAttributes();

        return array_combine(
            array_map(
                fn ($key) => Misc::transform($key, 'camel'),
                array_keys($attributes)
            ),
            $attributes
        );
    }

    /**
     * Returns JSON representation of the model. All attributes will be converted to `camelCase` form.
     */
    public function toJson(): string
    {
        return json_encode(
            $this->toArray(),
            JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT
        );
    }


    /**
     * Defines magic getters, setters, and finders for model attributes.
     * Examples: `attribute_name` has `getAttributeName()`, `setAttributeName()`, and `findByAttributeName()` methods.
     */
    public function __call(string $method, array $arguments)
    {
        if (preg_match('/^([gs]et|findBy)([a-z0-9]+)$/i', $method, $matches)) {
            $function  = Misc::transform($matches[1], 'camel');
            $attribute = Misc::transform($matches[2], 'snake');

            return $this->{$function === 'findBy' ? 'find' : $function}($attribute, ...$arguments);
        }

        throw new \Exception(sprintf('Call to undefined method %s::%s()', static::class, $method));
    }

    /**
     * Makes the model more friendly presented when exported via `var_dump()`.
     */
    public function __debugInfo()
    {
        return $this->toArray();
    }

    /**
     * Makes the object quickly available as a JSON string.
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
