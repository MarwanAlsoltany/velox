<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Frontend\View;

use MAKS\Velox\Backend\Config;
use MAKS\Velox\Frontend\Path;
use MAKS\Velox\Frontend\View\Engine;

/**
 * A class that offers some utility functions to require, parse, and compile view files.
 *
 * @package Velox\Frontend\View
 * @since 1.5.4
 */
class Compiler
{
    /**
     * Compiles a PHP file with the passed variables.
     *
     * @param string $file An absolute path to the file that should be compiled.
     * @param string $type The type of the file (just a name to make for friendly exceptions).
     * @param array|null [optional] An associative array of the variables to pass.
     *
     * @return string
     *
     * @throws \Exception If failed to compile the file.
     */
    public static function compile(string $file, string $type, ?array $variables = null): string
    {
        ob_start();

        try {
            self::require($file, $variables);
        } catch (\Exception $error) {
            // clean started buffer before throwing the exception
            ob_end_clean();

            throw $error;
        }

        $buffer = ob_get_contents();
        ob_end_clean();

        if ($buffer === false) {
            $name = basename($file, Config::get('view.fileExtension'));
            throw new \Exception("Something went wrong when trying to compile the {$type} with the name '{$name}' in {$file}");
        }

        return trim($buffer);
    }

    /**
     * Requires a PHP file and pass it the passed variables.
     *
     * @param string $file An absolute path to the file that should be compiled.
     * @param array|null $variables [optional] An associative array of the variables to pass.
     *
     * @return void
     *
     * @throws \Exception If the file could not be loaded.
     */
    public static function require(string $file, ?array $variables = null): void
    {
        $file = self::resolve($file);

        if (!file_exists($file)) {
            throw new \Exception(
                "Could not load the file with the path '{$file}' nor fall back to a parent. Check if the file exists"
            );
        }

        $_file = static::parse($file);

        unset($file);

        if ($variables !== null) {
            extract($variables, EXTR_OVERWRITE);
            unset($variables);
        }

        require($_file);

        unset($_file);
    }

    /**
     * Parses a file through the templating engine and returns a path to the compiled file.
     *
     * @param string $file The file to parse.
     *
     * @return string
     */
    public static function parse(string $file): string
    {
        if (!Config::get('view.engine.enabled', true)) {
            return $file;
        }

        static $engine = null;

        if ($engine === null) {
            $engine = new Engine(
                (string)Config::get('global.paths.themes') . '/',
                (string)Config::get('view.fileExtension', '.phtml'),
                (string)Config::get('global.paths.storage') . '/temp/views/',
                (bool)Config::get('view.engine.cache', true),
                (bool)Config::get('view.engine.debug', false)
            );
        }

        $file = $engine->getCompiledFile(strtr($file, [
            Path::normalize(Config::get('global.paths.themes'), '') => ''
        ]));

        return $file;
    }

    /**
     * Resolves a file from the active theme or inherits it from a parent theme.
     *
     * @param string $file
     *
     * @return string
     */
    public static function resolve(string $file): string
    {
        if (file_exists($file)) {
            return $file;
        }

        if (Config::get('view.inherit')) {
            $active = Config::get('theme.active');
            $parent = Config::get('theme.parent');
            $themes = Config::get('global.paths.themes');
            $nameWrapper = basename($themes) . DIRECTORY_SEPARATOR . '%s';

            foreach ((array)$parent as $substitute) {
                $fallbackFile = strtr($file, [
                    sprintf($nameWrapper, $active) => sprintf($nameWrapper, $substitute)
                ]);

                if (file_exists($fallbackFile)) {
                    $file = $fallbackFile;
                    break;
                }
            }
        }

        return $file;
    }
}
