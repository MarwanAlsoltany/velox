<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Mocks;

use MAKS\Velox\Backend\Config;
use MAKS\Velox\Backend\Database;

class DatabaseMock extends Database
{
    public static function instance(): Database
    {
        // these environment variables are required to test database related tests properly
        // DB_DSN="mysql:host={HOST};port={PORT};dbname={DATABASE};charset={CHARSET}"
        // DB_USERNAME={USERNAME}
        // DB_PASSWORD={PASSWORD}
        // Example: DB_DSN="mysql:host=localhost;port=3306;dbname=velox;charset=utf8mb4" DB_USERNAME="test" DB_PASSWORD="" composer run test

        $dsn      = getenv('DB_DSN');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');

        // update database credentials in config in case it is used directly by a test
        Config::set('database.dsn', $dsn);
        Config::set('database.username', $username);
        Config::set('database.password', $password);

        $database = static::connect($dsn, $username, $password);

        // drop the test table in case its created by a previous test
        $database->exec('DROP TABLE IF EXISTS `test`');

        return $database;
    }
}
