<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 *  This config file is used with VELOX commands. Here you can enable/disable the commands or change their arguments.
 * ---------------------------------------------------------------------------------------------------------------------
 */



return [


    // This command starts a development server with the provided arguments.
    'app-serve' => [
        'enabled' => true,
        'args'    => [
            'host' => 'localhost',
            'port' => 8000,
            'root' => '{global.paths.root}',
        ],
    ],


    // This command clears the cache of the configuration and/or views.
    'cache-clear' => [
        'enabled' => true,
        'args'    => [
            'config' => true,
            'views'  => true,
        ],
    ],


    // This command caches the current configuration.
    'config-cache' => [
        'enabled' => true,
    ],


    // This command mirrors (symlinks/copies) the provided files/directories in {global.paths.public}.
    'app-mirror' => [
        'enabled' => true,
        'args'    => [
            // Files/directories to link. Key is the link, value is the target.
            // Providing no key will create the necessary directories to reflect the target path.
            'link' => [
                '{global.paths.root}/index.php',
                '{theme.paths.assets}',
            ],
            // Files/directories to copy. Key is the destination, value is the source.
            // Providing no key will create the necessary directories to reflect the source path.
            'copy' => [
                'html' => '{global.paths.storage}/cache/views',
            ],
        ],
    ],


];
