<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);



if (!function_exists('dd')) {
    /**
     * Dumps a variable and dies.
     *
     * @param mixed ...$variable
     *
     * @return void The result will simply get echoed.
     */
    function dd(...$variable) {
        app()->dumper->dd(...func_get_args());
    }
}

if (!function_exists('dump')) {
    /**
     * Dumps a variable in a nice HTML block with syntax highlighting.
     *
     * @param mixed ...$variable
     *
     * @return void The result will simply get echoed.
     */
    function dump(...$variable) {
        app()->dumper->dump(...func_get_args());
    }
}

if (!function_exists('dump_exception')) {
    /**
     * Dumps an exception in a nice HTML page or as string and exits the script.
     *
     * @param \Throwable $exception
     *
     * @return void The result will be echoed as HTML page or a string representation of the exception if the interface is CLI.
     */
    function dump_exception(Throwable $exception) {
        app()->dumper->dumpException(...func_get_args());
    }
}
