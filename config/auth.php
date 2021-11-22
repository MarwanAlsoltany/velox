<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 *  This config file contains auth, it is used by the "Auth::class".
 * ---------------------------------------------------------------------------------------------------------------------
 */



return [


    // Auth user config.
    'user' => [
        // The auth user model class FQN. If left empty it will use the default auth user model.
        // If specified, this class has to have at least the "id", "username", and "password" attributes.
        'model'   => null,

        // The timeout in seconds before the user is automatically logged out.
        'timeout' => (12 * 60 * 60), // 12 hours
    ],

    // Password hashing config.
    'hashing' => [
        'algorithm' => PASSWORD_DEFAULT,
        'cost'      => 11,
    ],


];
