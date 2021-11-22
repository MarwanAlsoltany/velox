<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend;

use MAKS\Velox\Backend\Database;
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
 * @method mixed get*() Getter for model attribute, (`attribute_name` -> `getAttributeName()`).
 * @method $this set*() Setter for model attribute, (`attribute_name` -> `setAttributeName($value)`).
 * @method static[] findBy*() Finder by model attribute, (`attribute_name` -> `findByAttributeName($value)`).
 *
 * @property mixed $* Public attribute for model attribute, (`attribute_name` -> `attributeName`).
 */
abstract class Model implements \ArrayAccess, \Traversable, \IteratorAggregate
{
    /**
     * Model table name. If not set, an auto-generated name will be used instead.
     * For good practice, keep the model name in singular form and make the table name in plural form.
     */
    protected static ?string $table = null;

    /**
     * Model table columns. If not set, the model will fall back to the default primary key `['id']`.
     * For good practice, keep the table columns in `snake_case`. Model attribute names match table columns.
     */
    protected static ?array $columns = ['id'];

    /**
     * Model table primary key. If not set, `id` will be used by default.
     */
    protected static ?string $primaryKey = 'id';

    /**
     * The database instance/connection.
     */
    protected static ?Database $database;


    /**
     * Model attributes. Corresponds to table columns.
     */
    protected array $attributes;


    /**
     * The SQL code to create the model table from. Has to match `self::$table`, `self::$columns`, and `self::$primaryKey`.
     * Example: ```CREATE TABLE IF NOT EXISTS `table` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `text` VARCHAR(255));```
     *
     * @return string
     */
    abstract public static function schema(): string;

    /**
     * Migrates model table to the database.
     *
     * @return void
     */
    final public static function migrate(): void
    {
        static::getDatabase()->perform(trim(static::schema()));
    }

    /**
     * Checks whether the model table is migrated to the database or not.
     * You can override this method and return always `true` to disable auto migration.
     *
     * @return bool
     */
    public static function isMigrated(): bool
    {
        $table  = static::getTable();
        $tables = [$table];

        try {
            $tables = static::getDatabase()
                ->query('SHOW TABLES;')
                ->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            // ignore silently
        }

        return in_array($table, $tables);
    }

    /**
     * Returns model database connection and sets `static::$database` with a default value if it's not set.
     *
     * @return Database
     */
    public static function getDatabase(): Database
    {
        if (empty(static::$database)) {
            static::$database = Database::instance();
        }

        return static::$database;
    }

    /**
     * Returns model table name and sets `static::$table` with a default value if it's not set.
     *
     * @return string
     */
    public static function getTable(): string
    {
        if (empty(static::$table)) {
            $class = (new \ReflectionClass(static::class))->getShortName();
            static::$table = Misc::transform($class . '_model_entries', 'snake');
        }

        return static::$table;
    }

    /**
     * Returns model table columns and sets `static::$columns` with a default value if it's not set.
     *
     * @return array
     */
    public static function getColumns(): array
    {
        if (empty(static::$columns)) {
            static::$columns = ['id'];
        }

        return static::$columns;
    }

    /**
     * Returns model table primary key and sets `static::$primaryKey` with a default value if it's not set.
     *
     * @return string
     */
    public static function getPrimaryKey(): string
    {
        if (empty(static::$primaryKey)) {
            static::$primaryKey = 'id';
        }

        return static::$primaryKey;
    }

    /**
     * Asserts that the model attribute name is valid.
     *
     * @param mixed $name The name to validate.
     *
     * @return void
     */
    private static function assertAttributeExists($name): void
    {
        static $columns = null;

        if ($columns === null) {
            $columns = static::getColumns();
        }

        if (!in_array((string)$name, $columns)) {
            throw new \Exception(sprintf(
                'Cannot find attribute with the name "%s". %s model table does not consist of this column',
                $name,
                static::class
            ));
        }
    }

    /**
     * Gets and validates a specific model attribute.
     *
     * @param string $name Attribute name.
     * @param mixed $value Attribute value.
     *
     * @return mixed Attribute value.
     */
    public function get(string $name)
    {
        $this->assertAttributeExists($name);

        return $this->attributes[$name];
    }

    /**
     * Sets and validates a specific model attribute.
     *
     * @param string $name Attribute name.
     * @param mixed $value Attribute value.
     *
     * @return $this
     */
    public function set(string $name, $value): Model
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
    public function setAttributes(array $attributes): Model
    {
        foreach ($attributes as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Creates an instance of the model. The model is not saved to the database unless `self::save()` is called.
     *
     * @param string $attributes The attributes of the model.
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
     * - `Model::hydrate([...$arrayOfModelAttributes])`
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
     * Executes a query (a prepared statement) and returns the result.
     *
     * @param string $query The query to execute. The `@table` can be used to inject the current model table name into the query.
     * @param array|null $variables [optional] The variables needed for the query.
     * @param bool $raw [optional] Whether fetch the models as arrays (raw) or as hydrated objects.
     *
     * @return static[]|array[] The result as an array of objects or array of arrays depending on the passed parameters.
     *
     * Example:
     * - ```Model::raw('SELECT * FROM `users` WHERE `name` = :name OR `age` = :age', ['name' => 'Doe', 'age' => 27], true)```
     */
    public static function fetch(string $query, ?array $variables = [], bool $raw = false): array
    {
        $table     = sprintf('`%s`', static::getTable());
        $query     = str_ireplace(['@table', '`@table`'], $table, $query);
        $variables = $variables ?? [];

        $class = static::class;

        return static::getDatabase()->transactional(function () use ($query, $variables, $raw, $class) {
            /** @var Database $this */
            $statement = $this->perform($query, $variables);
            $result    = $raw
                ? $statement->fetchAll(\PDO::FETCH_ASSOC)
                : $statement->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $class, [/* $class constructor arguments */]);
            return $result;
        });
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
     * Finds a single or multiple models by the passed condition.
     *
     * @param string $column The column/attribute name.
     * @param string $operator Condition operator, can be: `=`, `<>`, `<`, `>`, `<=`, `>=`, `LIKE`, `NOT LIKE`, `IN`, `NOT IN`.
     * @param mixed $value The value to compare to.
     * @param array[] $additional [optional] Additional conditions. Can be used to add more conditions to the `WHERE` clause. Deep nesting can be achieved by simply using a child array.
     * @param string|null $order [optional] SQL order expression (like: `id` or `id ASC`).
     * @param int|null $limit [optional] To how many items the result should be limited.
     * @param int|null $offset [optional] From which item the result should start.
     *
     * @return static[]|array
     *
     * @throws \InvalidArgumentException If operator is not supported or a condition is invalid.
     *
     * Examples:
     * - `Model::where('name', '=', 'Doe')`.
     * - `Model::where('age', '>', 27, [['AND', 'name', 'LIKE', 'Doe%'], ..., [...,...]], $order, $limit, $offset)`.
     */
    public static function where(
        string $column,
        string $operator,
        $value,
        ?array $additional = null,
        ?string $order = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $conditions = array_merge([['', $column, $operator, $value]], $additional ?? []);

        $where     = static::buildWhereClause($conditions);
        $query     = sprintf('SELECT * FROM @table %s', $where['query']);
        $variables = $where['variables'];

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

        return static::fetch($query, $variables);
    }

    private static function buildWhereClause(array $conditions): array
    {
        $query     = 'WHERE';
        $variables = [];

        foreach ($conditions as $index => $condition) {
            $result = static::buildNestedQuery($condition, $index);

            $query     = $query . $result['query'];
            $variables = $variables + $result['variables'];
        }

        return compact('query', 'variables');
    }

    /**
     * Builds a nested query.
     *
     * @param array $condition The condition in the form of `['OPERATOR1', 'COLUMN', 'OPERATOR2', 'VALUE', ], ..., [..., ...]`.
     * @param int|string $index The index of the condition.
     *
     * @return mixed[] An associative array containing the SQL `query` and its needed `variables`.
     */
    private static function buildNestedQuery(array $condition, $index): array
    {
        $query     = '';
        $variables = [];
        $nested    = 0;

        if (is_array($condition[$nested] ?? null)) {
            $nested = count($condition);
            $subConditions = $condition;

            foreach ($subConditions as $subIndex => $subCondition) {
                $result = null;

                if ($subIndex === 0) {
                    $query .= ' ' . $subCondition[0] . ' (';
                    $subCondition[0] = '';
                    $subIndex = sprintf('%s_%s', $index, $subIndex);

                    $result = static::buildNestedQuery($subCondition, $subIndex);
                } else {
                    $result = static::buildNestedQuery($subCondition, $subIndex);
                }

                $query     = $query . $result['query'];
                $variables = $variables + $result['variables'];

                $nested--;
            }

            $query .= ' )';

            return compact('query', 'variables');
        }

        [$operator1, $column, $operator2, $value] = static::validateCondition($condition, $index);

        $operator1 = static::validateOperator($operator1, $index);
        $operator2 = static::validateOperator($operator2, $index);

        $placeholder  = sprintf('%s_%s', $column, $index);
        $placeholders = '';

        if ($isInOperator = substr($operator2, -2) === 'IN') {
            $placeholders = array_map(function ($id) use ($placeholder) {
                return sprintf('%s_%s', $placeholder, $id);
            }, array_keys($value));

            $keys       = array_values($placeholders);
            $values     = array_values($value);
            $variables  = array_merge($variables, array_combine($keys, $values));

            $placeholders = implode(', ', array_map(fn ($id) => ':' . $id, $placeholders));
        } else {
            $variables[$placeholder] = $value;
        }

        $query .= ' ' . trim(vsprintf('%s `%s` %s %s', [
            $operator1,
            $column,
            $operator2,
            $isInOperator ? "({$placeholders})" : ":{$placeholder}"
        ]));

        return compact('query', 'variables');
    }

    /**
     * Validates the passed condition.
     *
     * @param string $condition The condition to validate. in the form of `['OPERATOR1', 'COLUMN', 'OPERATOR2', 'VALUE']`
     * @param int|string $index The index of the condition (used to make more user-friendly exception).
     *
     * @return array An array containing the validated condition.
     *
     * @throws \InvalidArgumentException If the condition is invalid.
     */
    private static function validateCondition(array $condition, $index): array
    {
        $condition = array_merge($condition, array_fill(0, 4, null));
        $condition = array_splice($condition, 0, 4);

        // $operator1, $column, $value, $operator2
        if (!is_string($condition[0]) || !is_string($condition[1]) || !is_string($condition[2]) || !isset($condition[3])) {
            throw new \InvalidArgumentException(sprintf(
                "The passed condition ['%s'] at index (%s), is invalid. Was expecting ['%s'], got ['%s']",
                implode("', '", $condition),
                $index,
                implode("', '", ['operator1:string', 'column:string', 'operator2:string', 'value:mixed']),
                implode("', '", array_map(fn ($var) => gettype($var), $condition))
            ));
        }

        return $condition;
    }

    /**
     * Validates the passed operator.
     *
     * @param string $operator The operator to validate.
     * @param int|string $index The index of the condition (used to make more user-friendly exception).
     *
     * @return string The validated operator.
     *
     * @throws \InvalidArgumentException If the operator is invalid.
     */
    private static function validateOperator(string $operator, $index): string
    {
        $operator  = strtoupper(trim($operator));
        $supported = ['', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'AND', 'OR', '=', '<', '>', '<=', '>=', '<>'];

        if (!in_array($operator, $supported)) {
            throw new \InvalidArgumentException(sprintf(
                "Got '%s' as an argument at index (%s), which is invalid or unsupported SQL operator. Supported operators are: ['%s']",
                $operator,
                $index,
                implode("', '", $supported),
            ));
        }

        return $operator;
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
     * Override this method to add your own bootstrap code to the modal.
     *
     * @return void
     */
    protected function bootstrap(): void
    {
        // implemented as needed
    }


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
        $this->attributes = array_merge(array_fill_keys($this->getColumns(), null), $attributes);

        if ($this->isMigrated() === false) {
            $this->migrate();
        }

        $this->bootstrap();
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
     * Makes the model safely consumable via `unserialize()`.
     */
    public function __wakeup()
    {
        static::$database = static::getDatabase();
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


    /**
     * `ArrayAccess::offsetGet()` interface implementation.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * `ArrayAccess::offsetSet()` interface implementation.
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * `ArrayAccess::offsetExists()` interface implementation.
     */
    public function offsetExists($offset): bool
    {
        return $this->get($offset) !== null;
    }

    /**
     * `ArrayAccess::offsetUnset()` interface implementation.
     */
    public function offsetUnset($offset): void
    {
        $this->set($offset, null);
    }


    /**
     * `IteratorAggregate::getIterator()` interface implementation.
     */
    public function getIterator(): iterable
    {
        return new \ArrayIterator($this->attributes);
    }
}
