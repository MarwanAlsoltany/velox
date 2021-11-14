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
    protected ?string $username;
    protected ?string $password;
    protected ?array $options;


    /**
     * Adds some default options to the PDO connection.
     */
    protected function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null)
    {
        $this->dsn      = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options  = $options;

        $this->cache = [];

        parent::__construct($dsn, $username, $password, $options);

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
     * @param string|null $dsn The DSN string.
     * @param string|null $username [optional] The database username.
     * @param string|null $password [optional] The database password.
     * @param array|null $options [optional] PDO options.
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
     *
     * @codeCoverageIgnore This method is overridden (mocked) in the unit tests.
     */
    public static function instance(): Database
    {
        $databaseConfig = Config::get('database', []);

        try {
            return static::connect(
                $databaseConfig['dsn'] ?? '',
                $databaseConfig['username'] ?? null,
                $databaseConfig['password'] ?? null,
                $databaseConfig['options'] ?? null
            );
        } catch (\PDOException $error) {
            // connection can't be established (incorrect config), return a fake instance
            return static::mock();
        }
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
    public function prepare($query, $options = [])
    {
        $hash = md5($query);

        if (!isset($this->cache[$hash])) {
            $this->cache[$hash] = parent::prepare($query, $options);
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
     * @param int $retries The number of times to attempt the transaction. Each retry will be delayed by 1-3 seconds.
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

                sleep(rand(1, 3));
            } finally {
                if ($this->inTransaction()) {
                    $this->rollBack();
                }
            }
        } while ($attempts < $retries);

        return $return;
    }

    /**
     * Returns a fake instance of the `Database` class.
     *
     * @return Database This instance will throw an exception if a method is called.
     *
     * @codeCoverageIgnore
     */
    private static function mock()
    {
        return new class () extends Database {
            protected function __construct()
            {
                // constructor arguments are not used
            }
            public static function getAvailableDrivers()
            {
                static::fail();
            }
            public function getAttribute($attribute)
            {
                static::fail();
            }
            public function setAttribute($attribute, $value)
            {
                static::fail();
            }
            public function exec($statement)
            {
                static::fail();
            }
            public function prepare($query, $options = [])
            {
                static::fail();
            }
            public function query($query, $fetchMode = null, ...$fetchModeArgs)
            {
                static::fail();
            }
            public function quote($string, $type = \PDO::PARAM_STR)
            {
                static::fail();
            }
            public function lastInsertId($name = null)
            {
                static::fail();
            }
            public function beginTransaction()
            {
                static::fail();
            }
            public function inTransaction()
            {
                static::fail();
            }
            public function commit()
            {
                static::fail();
            }
            public function rollBack()
            {
                static::fail();
            }
            public function errorCode()
            {
                static::fail();
            }
            public function errorInfo()
            {
                static::fail();
            }
            private static function fail(): void
            {
                throw new \Exception(
                    'The app is currently running using a fake database, all database related operations will fail. ' .
                    'Add valid database credentials using "config/database.php" to resolve this issue'
                );
            }
        };
    }
}
