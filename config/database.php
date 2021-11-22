<?php

/**
 * This config file contains database credentials, it is used by the "Database::class".
 *
 * @see \MAKS\Velox\Backend\Database
 */



return [


    // Database login info.
    'driver'   => 'mysql',
    'host'     => 'localhost',
    'port'     => 3306,
    'charset'  => 'utf8mb4',
    'dbname'   => 'velox',
    'username' => 'root',
    'password' => '',

    // Database DSN (Data Source Name) of the connection.
    // Keep untouched, it will be populated from the provided values above.
    'dsn'      => '{database.driver}:host={database.host};port={database.port};dbname={database.dbname};charset={database.charset}',


];
