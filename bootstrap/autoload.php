<?php

/**
 * This file should be loaded in application entry point ("../index.php" for example).
 *
 * It takes care of:
 *  - Loading VELOX classes "../classes/" and "../app/".
 *  - Loading VELOX functions "../functions/".
 *  - Loading Composer "../vendor/" if available.
 *  - Loading additional paths defined in "./additional.php".
 */


/**
 * App start time.
 *
 * @var float
 */
define('START_TIME', microtime(true));

/**
 * App base path.
 *
 * @var string
 */
define('BASE_PATH', dirname(__DIR__));



// global variables holder for various VELOX global state variables
$_VELOX = [];



// load VELOX classes from "../classes/", "../app/" and alias them
require(BASE_PATH . '/bootstrap/loader.php');



// load composer autoload if composer is used in this project
$composer = BASE_PATH . '/vendor/autoload.php';

if (file_exists($composer)) {
    require($composer);
}

unset($composer);


// functions are required separately to allow for a variable number of files to be required
$functions = BASE_PATH . '/functions';

if (file_exists($functions)) {
    require_recursive($functions);
}

unset($functions);


// load additional files defined in "./additional.php"
$includes = include(BASE_PATH . '/bootstrap/additional.php');

foreach ($includes as $include) {
    if (is_file($include)) {
        require($include);
    }

    if (is_dir($include)) {
        require_recursive($include);
    }

    unset($include);
}

unset($includes);
