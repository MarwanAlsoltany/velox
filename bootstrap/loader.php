<?php

/**
 * This file should not be loaded directly in the application, "./autoload.php" takes care of loading it.
 *
 * It takes care of:
 * - Setting application include paths.
 * - Registering an autoloader.
 * - Aliasing VELOX classes.
 * - Setting error and exception handlers and the shutdown function.
 * - Setting the default timezone.
 * - Providing some helper functions for autoloading.
 *
 * If you ever wanted to extend VELOX functionality beyond basic stuff, you may want to do something here.
 */



// additional include paths
$paths = [
    // ID  => Path
    'base' => BASE_PATH,
];

// autoloader directory to namespace mapping
$namespaces = [
    // DIR    => Namespace Prefix
    'classes' => 'MAKS\\Velox\\',
    'app'     => 'App\\',
];

// autoloader dynamically aliased aliased classes
$aliases = [
    // Alias   => FQN
    'App'      => \MAKS\Velox\App::class,
    'Auth'     => \MAKS\Velox\Backend\Auth::class,
    'Event'    => \MAKS\Velox\Backend\Event::class,
    'Config'   => \MAKS\Velox\Backend\Config::class,
    'Router'   => \MAKS\Velox\Backend\Router::class,
    'Globals'  => \MAKS\Velox\Backend\Globals::class,
    'Session'  => \MAKS\Velox\Backend\Session::class,
    'Database' => \MAKS\Velox\Backend\Database::class,
    'Data'     => \MAKS\Velox\Frontend\Data::class,
    'View'     => \MAKS\Velox\Frontend\View::class,
    'HTML'     => \MAKS\Velox\Frontend\HTML::class,
    'Path'     => \MAKS\Velox\Frontend\Path::class,
];



// include paths
$paths = implode(PATH_SEPARATOR, [get_include_path(), ...array_values($paths)]);

// autoloader function
$loader = function ($class) use (&$loader, $namespaces, $aliases) {
    if (isset($aliases[$class])) {
        $loader($aliases[$class]);

        if (!class_exists($class)) {
            class_alias($aliases[$class], $class);
        }

        return;
    }

    foreach ($namespaces as $directory => $namespace) {
        if (strrpos($class, $namespace) !== false) {
            $ext = '.php';
            $path = realpath(BASE_PATH . DIRECTORY_SEPARATOR . $directory);
            $name = str_replace([$namespace, '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $class);

            $file = $path . $name . $ext;

            if (file_exists($file)) {
                require_once($file);
            }
        }
    }
};

// errors handler, makes everything an exception, even minor warnings
$errorHandler = function (int $code, string $message, string $file, int $line) {
    if (!class_exists('ErrorOrWarningException')) {
        class ErrorOrWarningException extends \ErrorException {};
    }

    throw new ErrorOrWarningException($message, $code, E_ERROR, $file, $line);
};

// exceptions handler, logs the exception and then dumps it and/or displays a nice page
$exceptionHandler = function (\Throwable $exception) {
    // explicitly set the status code in case it is not set
    http_response_code(500);

    // only keep the last buffer if nested
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // enable logging in case it is disabled
    \MAKS\Velox\Backend\Config::set('global.logging.enabled', true);

    // get app environment
    $environment = \MAKS\Velox\Backend\Config::get('global.env');

    // log the exception
    \MAKS\Velox\App::log("[ERROR: (env: {$environment})] {$exception}", null, 'system');

    if (in_array(strtoupper($environment), ['PROD', 'PRODUCTION'])) {
        // if in production environment, return a nice page without all the details
        $view = \MAKS\Velox\Backend\Config::get('global.errorPages.' . $exception->getCode());

        if ($view) {
            // if exception code matches a error page code, then render it
            \MAKS\Velox\App::abort($exception->getCode(), null, $exception->getMessage());
        } else {
            // otherwise, fall back to 500 error page
            \MAKS\Velox\App::abort(500, null, 'An error occurred, try again later.');
        }

    } else {
        // if in development environment, dump a detailed exception
        \MAKS\Velox\Helper\Dumper::dumpException($exception);
    }

    // terminate the app entirely without executing shutdown functions
    \MAKS\Velox\App::terminate(null, false);
};

// shutdown function, makes errors and exceptions handlers available at shutdown
$shutdownFunction = function () use ($errorHandler, $exceptionHandler) {
    // die only if no shutdown is allowed
    if (\MAKS\Velox\Helper\Misc::getArrayValueByKey($GLOBALS['_VELOX'], 'TERMINATE', false)) {
        die;
    }

    // add App runtime shutdown methods
    \MAKS\Velox\App::extendStatic('handleError', $errorHandler);
    \MAKS\Velox\App::extendStatic('handleException', $exceptionHandler);

    // execute the shutdown event only if it is not already executed
    if (\MAKS\Velox\Helper\Misc::getArrayValueByKey($GLOBALS['_VELOX'], 'SHUTDOWN', true)) {
        \MAKS\Velox\App::shutdown();
    }
};



// set include paths
set_include_path($paths);
spl_autoload_register($loader, true, false);
set_error_handler($errorHandler);
set_exception_handler($exceptionHandler);
register_shutdown_function($shutdownFunction);
date_default_timezone_set(\MAKS\Velox\Backend\Config::get('global.timezone'));



// clean up
unset(
    $paths,
    $namespaces,
    $aliases,
    $loader,
    $exceptionHandler,
    $errorHandler,
    $shutdownFunction,
);



/**
 * Requires a directory recursively.
 *
 * @param string $path The path to a directory. If the passed parameter is not a directory, this function will skip it.
 *
 * @return bool If something was included, it returns true, otherwise false.
 */
function require_recursive(string $directory): bool {
    static $files = [];

    if (!is_dir($directory)) {
        return false;
    }

    $filenames = scandir($directory) ?: [];
    foreach ($filenames as $filename) {
        $file = sprintf('%s/%s', $directory, $filename);

        if (is_dir($file)) {
            // only if subdirectory, not current or parent.
            if (strpos($filename, '.') === false) {
                require_recursive($file);
            }
            continue;
        }

        if (is_file($file)) {
            require_once($file);

            $files[] = $file;
        }
    }

    return (bool)count($files);
}

/**
 * Aliases classes in a directory to the root namespace recursively. Note that namespaces have to follow PSR-4.
 *
 * @param string $directory The path to a directory. If the passed parameter is not a directory, this function will skip it.
 * @param string $namespacePrefix The prefix for classes namespace.
 *
 * @return bool If something was aliased, it returns true, otherwise false.
 */
function class_alias_recursive(string $directory, string $namespacePrefix): bool {
    static $aliases = [];

    if (!is_dir($directory)) {
        return false;
    }

    $filenames = scandir($directory) ?: [];
    foreach ($filenames as $filename) {
        $file = sprintf('%s/%s', $directory, $filename);

        if (is_dir($file)) {
            // only if subdirectory, not current or parent.
            if (strpos($filename, '.') === false) {
                class_alias_recursive($file, $namespacePrefix);
            }
            continue;
        }

        if (is_file($file)) {
            $className      = basename($file, '.php');
            $classDirectory = str_replace(dirname($directory), '', dirname($file));
            $classNamespace = sprintf('%s\\%s', trim($namespacePrefix, '\\'), trim($classDirectory, '/'));
            $classFQN       = sprintf('%s\\%s', $classNamespace, $className);

            class_alias($classFQN, $className);
            $aliases[$className] = $classFQN;
        }
    }

    return (bool)count($aliases);
}
