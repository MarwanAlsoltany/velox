<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 *  This file should not be loaded directly in your application, "./autoload.php" takes care of loading it.
 * ---------------------------------------------------------------------------------------------------------------------
 *  It takes care of:
 *      (1) Setting application include paths.
 *      (2) Registering an autoloader.
 *      (3) Aliasing VELOX classes.
 *      (4) Setting error and exception handlers.
 *      (5) Providing some helper functions for autoloading.
 * ---------------------------------------------------------------------------------------------------------------------
 *  If you ever wanted to extend VELOX functionality beyond basic stuff, you may want to do something here.
 * ---------------------------------------------------------------------------------------------------------------------
 */



// additional include paths
$paths = [
    BASE_PATH,
];

// set include paths
set_include_path(
    implode(
        PATH_SEPARATOR,
        [get_include_path(), ...$paths]
    )
);
unset($paths);


$namespaces = [
    // DIR    => Namespace Prefix
    'classes' => 'MAKS\\Velox\\',
    'app'     => 'App\\',
];

$aliases = [
    // Alias => FQN
    'App'     => \MAKS\Velox\App::class,
    'Config'  => \MAKS\Velox\Backend\Config::class,
    'Router'  => \MAKS\Velox\Backend\Router::class,
    'Globals' => \MAKS\Velox\Backend\Globals::class,
    'Data'    => \MAKS\Velox\Frontend\Data::class,
    'View'    => \MAKS\Velox\Frontend\View::class,
    'HTML'    => \MAKS\Velox\Frontend\HTML::class,
    'Path'    => \MAKS\Velox\Frontend\Path::class,
];

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
            $path = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . $directory);
            $name = str_replace([$namespace, '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $class);

            $file = $path . $name . $ext;

            if (file_exists($file)) {
                include_once($file);
            }
        }
    }
};

spl_autoload_register($loader, true, false);
// clean up
unset($namespaces);
unset($aliases);
unset($loader);


// everything is an exception, even minor warnings
set_error_handler(function (int $code, string $message, string $file, int $line) {
    class ErrorOrWarningException extends \ErrorException {};
    throw new ErrorOrWarningException($message, $code, 1, $file, $line, null);
});

// exceptions handler, logs the exception and then dumps it and or displays a nice page
set_exception_handler(function (\Throwable $exception) {
    $globalConfig = \MAKS\Velox\Backend\Config::get('global');

    http_response_code(500);

    // only keep the last buffer if nested
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // enable logging in case it is disabled
    \MAKS\Velox\Backend\Config::set('global.loggingEnabled', true);
    // log the exception
    \MAKS\Velox\Helper\Misc::log("[ERROR] $exception", null, 'system');

    // if in development environment, dump detailed exceptions
    if (!in_array(strtoupper($globalConfig['env']), ['PROD', 'PRODUCTION'])) {
        \MAKS\Velox\Helper\Dumper::dumpException($exception);
        exit;
    }

    // if in production environment, return a nice page without all the details
    if ($globalConfig['errorPage']) {
        // return user defined error page if there is any
        if (file_exists($globalConfig['errorPage'])) {
            echo file_get_contents($globalConfig['errorPage']);
            exit;
        }
    }

    // production environment fallback error page
    (new \MAKS\Velox\Frontend\HTML(false))
        ->node('<!DOCTYPE html>')
        ->open('html', ['lang' => 'en'])
            ->open('head')
                ->title('500 Server Error')
                ->link(null, [
                    'href' => 'https://cdn.jsdelivr.net/npm/bulma@0.9.2/css/bulma.min.css',
                    'rel' => 'stylesheet'
                ])
            ->close()
            ->open('body')
                ->open('section', ['class' => 'section is-large has-text-centered'])
                    ->hr(null)
                    ->h1('500 Server Error', ['class' => 'title is-1 is-spaced has-text-danger'])
                    ->h4('Something Bad Happened', ['class' => 'subtitle'])
                    ->p('Try again later!', ['style' => 'text-decoration:underline;'])
                    ->hr(null)
                    ->a('Reload', ['class' => 'button is-warning is-light', 'href' => 'javascript:location.reload();'])
                    ->entity('nbsp')
                    ->entity('nbsp')
                    ->a('Home', ['class' => 'button is-success is-light', 'href' => '/'])
                    ->hr(null)
                ->close()
            ->close()
        ->close()
    ->echo();

    exit;
});



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
