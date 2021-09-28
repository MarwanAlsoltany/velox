<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 *  This config file contains session configuration, it is used by the "Session::class".
 * ---------------------------------------------------------------------------------------------------------------------
 */



return [


    // PHP session save path, see https://www.php.net/manual/en/function.session-save-path
    'path' => '{global.paths.storage}/app/sessions',


    // PHP session cache configuration.
    'cache' => [
        // Cache limiter, see https://www.php.net/manual/en/function.session-cache-limiter
        'limiter'    => null,
        // Cache expiration, see https://www.php.net/manual/en/function.session-cache-expire
        'expiration' => null,
    ],


    // CSRF protection.
    'csrf' => [
        // Input field name that contains the CSRF token.
        'name'        => '_token',
        // HTTP methods that should be checked against CSRF.
        'methods'     => ['POST', 'PUT', 'PATCH', 'DELETE'],
        // Whitelisted hosts and/or IPs that are allowed to pass CSRF check (Hostname has precedence over IP).
        'whitelisted' => [
            // 'https://domain.tld'
            // '127.0.0.1'
        ],
    ],


];
