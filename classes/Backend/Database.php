<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend;

/**
 * A class that represents the database and handles database operations.
 *
 * Example:
 * ```
 * $database = Database::instance();
 * $database->query('SELECT * FROM `users`');
 * $database->prepare('SELECT * FROM `users` WHERE `job` = :job LIMIT 5')->execute([':job' => 'Developer'])->fetchAll();
 * $database->perform('SELECT * FROM `users` WHERE `title` LIKE :title AND `id` > :id', ['title' => 'Dr.%', 'id' => 1])->fetchAll();
 * ```
 *
 * @since 1.3.0
 * @api
 */
class Database extends \PDO
{
    /**
     * Current open database connections.
     */
    protected static array $connections;

    /**
     * A cache to hold prepared statements.
     */
    protected array $cache;

    protected string $dsn;
    protected string $username;
    protected string $password;
    protected array $options;


    /**
     * Adds some default options to the PDO connection.
     */
    protected function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null)
    {
        $this->dsn      = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options  = $options ?? [];

        $this->cache = [];

        parent::__construct($dsn, $username, $password, $options ?? []);

        $this->setAttribute(static::ATTR_ERRMODE, static::ERRMODE_EXCEPTION);
        $this->setAttribute(static::ATTR_DEFAULT_FETCH_MODE, static::FETCH_ASSOC);
        $this->setAttribute(static::ATTR_EMULATE_PREPARES, false);
        $this->setAttribute(static::MYSQL_ATTR_FOUND_ROWS, true);
        $this->setAttribute(static::ATTR_STATEMENT_CLASS, [$this->getStatementClass()]);
    }


    /**
     * Returns a singleton instance of the `Database` class based on connection credentials.
     * This method makes sure that a single connection is opened and reused for each connection credentials set (DSN, User, Password, ...).
     *
     * @param string|null $dsn [optional] The DSN string. If not specified, it will be retrieved from the config.
     * @param string|null $username [optional] The database username. If not specified, it will be retrieved from the config.
     * @param string|null $password [optional] The database password. If not specified, it will be retrieved from the config.
     * @param array|null $options [optional] PDO options. If not specified, it will be retrieved from the config.
     *
     * @return static
     */
    final public static function connect(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null): Database
    {
        $connection = md5(serialize(func_get_args()));

        if (!isset(static::$connections[$connection])) {
            static::$connections[$connection] = new static($dsn, $username, $password, $options);
        }

        return static::$connections[$connection];
    }

    /**
     * Returns the singleton instance of the `Database` class using credentials found in "{database}" config.
     *
     * @return static
     */
    public static function instance(): Database
    {
        $databaseConfig = Config::get('database', []);

        return static::connect(
            $databaseConfig['dsn'] ?? '',
            $databaseConfig['username'] ?? null,
            $databaseConfig['password'] ?? null,
            $databaseConfig['options'] ?? null
        );
    }

    /**
     * Returns FQN for a custom PDOStatement class.
     *
     * @return string
     */
    private function getStatementClass(): string
    {
        $statement = new class () extends \PDOStatement {
            // Makes method chaining a little bit more convenient.
            public function execute($params = null)
            {
                parent::execute($params);

                return $this;
            }
            // Catches the debug dump instead of printing it out directly.
            public function debugDumpParams()
            {
                ob_start();

                parent::debugDumpParams();

                $dump = ob_get_contents();
                ob_end_clean();

                return $dump;
            }
        };

        return get_class($statement);
    }

    /**
     * Adds caching capabilities for prepared statement.
     * {@inheritDoc}
     */
    public function prepare($query, $options = null)
    {
        $hash = md5($query);

        if (!isset($this->cache[$hash])) {
            $this->cache[$hash] = parent::prepare($query, $options ?? []);
        }

        return $this->cache[$hash];
    }

    /**
     * A wrapper method to perform a query on the fly using either `self::query()` or `self::prepare()` + `self::execute()`.
     *
     * @param string $query The query to execute.
     * @param array $params The parameters to bind to the query.
     *
     * @return \PDOStatement
     */
    public function perform(string $query, ?array $params = null): \PDOStatement
    {
        try {
            if (empty($params)) {
                return $this->query($query);
            }

            $statement = $this->prepare($query);
            $statement->execute($params);

            return $statement;
        } catch (\PDOException $error) {
            throw $error;
        }
    }

    /**
     * Serves as a wrapper method to execute some operations in transactional context with the ability to attempt retires.
     *
     * @param callable $callback The callback to execute inside the transaction. This callback will be bound to the `Database` class.
     * @param int $retries The number of times to attempt the transaction.
     *
     * @return mixed The result of the callback.
     */
    public function transactional(callable $callback, int $retries = 3)
    {
        $callback = \Closure::fromCallable($callback)->bindTo($this);
        $attempts = 0;
        $return   = null;

        do {
            $this->beginTransaction();

            try {
                $return = $callback($this);

                $this->commit();

                break;
            } catch (\Throwable $error) {
                $this->rollBack();

                if (++$attempts === $retries) {
                    throw new \Exception(
                        "Could not complete the transaction after {$retries} attempt(s).",
                        (int)$error->getCode(),
                        $error
                    );
                }
            } finally {
                if ($this->inTransaction()) {
                    $this->rollBack();
                }
            }
        } while ($attempts < $retries);

        return $return;
    }
}
