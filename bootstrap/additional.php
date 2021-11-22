<?php

/**
 * This file can be used to load additional PHP files. These files will be loaded after the application is bootstrap.
 *
 * If you want to include classes using PSR-4, take a look at "./loader.php".
 *
 * Please note that "../classes/", "../functions/", and "../app/" are loaded by default!
 */



return [
    BASE_PATH . '/includes/events',
    BASE_PATH . '/includes/routes',
    // BASE_PATH . '/path/to/file.php',
    // BASE_PATH . '/path/to/directory',
];
