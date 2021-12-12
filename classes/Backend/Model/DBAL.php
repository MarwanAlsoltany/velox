<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend\Model;

use MAKS\Velox\Backend\Database;
use MAKS\Velox\Helper\Misc;

/**
 * An abstract class that serves as a DBAL for models.
 *
 * @since 1.5.1
 * @internal
 */
abstract class DBAL
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
     * Returns model table name and sets `static::$table` with a default value and returns it if it's not set.
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
     * Returns model table columns and sets `static::$columns` with a default value and returns it if it's not set.
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
     * Returns model table primary key and sets `static::$primaryKey` with a default value and returns it if it's not set.
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
     *
     * @throws \BadMethodCallException If called in an abstract class context.
     */
    final public static function migrate(): void
    {
        if (self::class === static::class || (new \ReflectionClass(static::class))->isAbstract()) {
            throw new \BadMethodCallException(sprintf(
                'Cannot migrate an abstract class, "%s" methods should be used by extension only',
                self::class
            ));
        }

        static::getDatabase()->perform(trim(static::schema()));
    }

    /**
     * Checks whether the model table is migrated to the database or not.
     * You can override this method and return always `true` to disable auto migration.
     *
     * NOTE: For compatibility reasons, the return value of this method true if it fails to connect to the database.
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
     * Executes a query (a prepared statement) and returns the result.
     *
     * @param string $query The query to execute. The `@table` can be used to inject the current model table name into the query.
     * @param array|null $variables [optional] The variables needed for the query.
     * @param bool $raw [optional] Whether fetch the models as arrays (raw) or as hydrated objects.
     *
     * @return static[]|array[] The result as an array of objects or array of arrays depending on the passed parameters.
     *
     * @throws \BadMethodCallException If called in an abstract class context.
     *
     * Example:
     * - ```Model::fetch('SELECT * FROM `users` WHERE `name` = :name OR `age` = :age', ['name' => 'Doe', 'age' => 27], true)```
     */
    public static function fetch(string $query, ?array $variables = [], bool $raw = false): array
    {
        if (static::class === self::class || (new \ReflectionClass(static::class))->isAbstract()) {
            throw new \BadMethodCallException(sprintf(
                'Cannot fetch for an abstract class, "%s" methods should be used by extension only',
                self::class
            ));
        }

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
     * Finds a single or multiple models by the passed condition.
     *
     * @param string $column The column/attribute name.
     * @param string $operator Condition operator, can be: `=`, `!=`, `<>`, `<`, `>`, `<=`, `>=`, `LIKE`, `NOT LIKE`, `IN`, `NOT IN`.
     * @param mixed $value The value to compare to.
     * @param array[] $additional [optional] Additional conditions. Can be used to add more conditions to the `WHERE` clause. Deep nesting can be achieved by simply using a child array.
     * @param string|null $order [optional] SQL order expression (like: `id` or `id ASC`).
     * @param int|null $limit [optional] To how many items the result should be limited.
     * @param int|null $offset [optional] From which item the result should start.
     *
     * @return static[]|array[]
     *
     * @throws \InvalidArgumentException If operator is not supported or a condition is invalid.
     * @throws \BadMethodCallException If called in an abstract class context.
     *
     * Examples:
     * - `Model::where('name', '=', 'Doe')`.
     * - `Model::where('age', '>', 27, [['AND', 'name', 'LIKE', 'Doe%'], $query, ..., [$subQuery, ...]], $order, $limit, $offset)`.
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

        return [
            'query'     => (string)$query,
            'variables' => (array)$variables,
        ];
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
     * @param array $condition The condition to validate. in the form of `['OPERATOR1', 'COLUMN', 'OPERATOR2', 'VALUE']`
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
                "The passed condition ['%s'] in query at index (%s) is invalid. Was expecting ['%s'], got ['%s']",
                implode("', '", $condition),
                $index,
                implode("', '", ['operator1 (string)', 'column (string)', 'operator2 (string)', 'value (mixed)']),
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
        $supported = ['', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'AND', 'OR', '=', '!=', '<>', '<', '>', '<=', '>='];

        if (!in_array($operator, $supported)) {
            throw new \InvalidArgumentException(sprintf(
                "Got '%s' as an argument in query at index (%s), which is an invalid or unsupported SQL operator. Supported operators are: ['%s']",
                $operator,
                $index,
                implode("', '", $supported),
            ));
        }

        return $operator;
    }
}
