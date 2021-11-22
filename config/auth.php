<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 *  This config file contains authentication configuration, it is used by the "Auth::class".
 * ---------------------------------------------------------------------------------------------------------------------
 */



return [


    // Auth user config.
    'user' => [
        // The auth user model class FQN. If left empty it will use the default auth user model.
        // If specified, this class has to have at least the "id", "username", and "password" attributes.
        'model'   => null,

        // The timeout in seconds before the user is automatically logged out.
        'timeout' => (1 * 60 * 60), // 1 hour
    ],

    // Password hashing config.
    'hashing' => [
        'algorithm' => PASSWORD_DEFAULT,
        'cost'      => 11,
    ],


];
