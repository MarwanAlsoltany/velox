<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 *  This config file contains some global variables that are used by almost all classes (app-wide config).
 * ---------------------------------------------------------------------------------------------------------------------
 */



return [


    // VELOX global directory structure.
    'paths' => [
        'root'      => BASE_PATH,
        'app'       => '{global.paths.root}/app',
        'classes'   => '{global.paths.root}/classes',
        'functions' => '{global.paths.root}/functions',
        'themes'    => '{global.paths.root}/themes',
        'config'    => '{global.paths.root}/config',
        'storage'   => '{global.paths.root}/storage',
    ],


    // VELOX current environment, [DEV = development, PROD = production].
    'env' => 'DEV',


    // An absolute path or a path from "BASE_PATH" for an HTML file to use for '500 Server Error' responses if an uncaught exception was thrown in production environment.
    'errorPage' => null,


    // Whether or not to enable logging of different events of the app. Note that exception will be logged no matter what the value here is.
    'loggingEnabled' => true,


];
