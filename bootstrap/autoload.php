<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 *  This file should be loaded in the application entry point ("../index.php" for example).
 * ---------------------------------------------------------------------------------------------------------------------
 *  It takes care of:
 *      (1) Loading VELOX classes "../classes/" and "../app/".
 *      (2) Loading VELOX functions "../functions/".
 *      (3) Loading Composer "../vendor/" if available.
 *      (4) Loading additional paths defined in "./additional.php".
 * ---------------------------------------------------------------------------------------------------------------------
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



// load VELOX classes from "../classes/", "../app/" and alias them
include(BASE_PATH . '/bootstrap/loader.php');



// load composer autoload if composer is used in this project
$composer = BASE_PATH . '/vendor/autoload.php';
if (file_exists($composer)) {
    require_once($composer);
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
        require_once($include);
    }

    if (is_dir($include)) {
        require_recursive($include);
    }
}
unset($includes);
