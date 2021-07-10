<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend;

use MAKS\Velox\Helper\Misc;

/**
 * A class that loads everything from the "/config" directory and make it as an array that is accessible via dot-notation.
 *
 * Example:
 * ```
 * // get the entire config
 * $entireConfig = Config::getAll();
 *
 * // check for config value availability
 * $varNameExists = Config::has('filename.config.varName');
 *
 * // get a specific config value or fall back to a default value
 * $varName = Config::get('filename.config.varName', 'fallbackValue');
 *
 * // set a specific config value at runtime
 * Config::set('filename.config.varName', 'varValue');
 *
 * // delete cached config
 * Config::clearCache();
 * ```
 *
 * @since 1.0.0
 * @api
 */
class Config
{
    /**
     * The default directory of the configuration files.
     *
     * @var string
     */
    public const CONFIG_DIR = BASE_PATH . '/config';

    /**
     * The path of the cached configuration file.
     *
     * @var string
     */
    public const CONFIG_CACHE_FILE = BASE_PATH . '/storage/cache/config/config.json';


    /**
     * The currently loaded configuration.
     */
    protected static array $config;


    public function __construct()
    {
        $this->load();
    }

    public function __toString()
    {
        return static::CONFIG_DIR;
    }


    /**
     * Includes all files in a directory.
     *
     * @param string $path
     *
     * @return array
     */
    private static function include(string $path): array
    {
        $includes = [];

        // load all config files
        $filenames = scandir($path);
        foreach ($filenames as $filename) {
            $file = sprintf('%s/%s', $path, $filename);
            $config = basename($filename, '.php');

            if (is_dir($file)) {
                if (strpos($filename, '.') === false) {
                    $includes[$config] = self::include($file);
                }
                continue;
            }

            if (is_file($file)) {
                $includes[$config] = include($file);
            }
        }

        return $includes;
    }

    /**
     * Parses the configuration to replace reference of some "{filename.config.varName}" with actual value from the passed configuration.
     *
     * @param array $config
     *
     * @return array
     */
    private static function parse(array $config): array
    {
        // parses all config variables
        $tries = count($config);
        for ($i = 0; $i < $tries; $i++) {
            array_walk_recursive($config, function (&$value) use (&$config) {
                if (is_string($value)) {
                    if (preg_match_all('/{([a-z0-9_\-\.]*)}/i', $value, $matches)) {
                        $variables = [];
                        array_walk($matches[1], function (&$variable) use (&$variables, &$config) {
                            $variables[$variable] = Misc::getArrayValueByKey($config, $variable, null);
                        });

                        $value = Misc::interpolate($value, $variables);
                    }
                }
            });
        }

        return $config;
    }

    /**
     * Loads the configuration (directly or when available from cache) and sets class internal state.
     *
     * @return void
     */
    protected static function load(): void
    {
        $configDir       = static::CONFIG_DIR;
        $configCacheFile = static::CONFIG_CACHE_FILE;

        if (empty(static::$config)) {
            if (file_exists($configCacheFile)) {
                $configJson     = file_get_contents($configCacheFile);
                static::$config = json_decode($configJson, true);

                return;
            }

            static::$config = self::parse(self::include($configDir));
        }
    }

    /**
     * Caches the current configuration as JSON. Note that a new version will not be generated unless the cache is cleared.
     *
     * @return void
     */
    public static function cache(): void
    {
        $configDir       = static::CONFIG_DIR;
        $configCacheFile = static::CONFIG_CACHE_FILE;
        $configCacheDir  = dirname($configCacheFile);

        if (file_exists($configCacheFile)) {
            return;
        }

        if (!file_exists($configCacheDir)) {
            mkdir($configCacheDir, 0744, true);
        }

        $config     = self::parse(self::include($configDir));
        $configJson = json_encode($config, JSON_PRETTY_PRINT);

        file_put_contents($configCacheFile, $configJson, LOCK_EX);

        Misc::log(
            'Generated cache for system config, checksum (SHA-256: {checksum})',
            ['checksum' => hash('sha256', $configJson)],
            'system'
        );
    }

    /**
     * Deletes the cached configuration JSON and resets class internal state.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        static::$config = [];

        $configCacheFile = static::CONFIG_CACHE_FILE;

        if (file_exists($configCacheFile)) {
            unlink($configCacheFile);
        }

        Misc::log('Cleared config cache', null, 'system');
    }

    /**
     * Checks whether a value of a key exists in the configuration via dot-notation.
     *
     * @param string $key The dotted key representation.
     *
     * @return bool
     */
    public static function has(string $key): bool
    {
        static::load();

        $value = Misc::getArrayValueByKey(static::$config, $key, null);

        return isset($value);
    }

    /**
     * Gets a value of a key from the configuration via dot-notation.
     *
     * @param string $key The dotted key representation.
     * @param mixed $fallback [optional] The default fallback value.
     *
     * @return mixed The requested value or null.
     */
    public static function get(string $key, $fallback = null)
    {
        static::load();

        return Misc::getArrayValueByKey(static::$config, $key, $fallback);
    }

    /**
     * Sets a value of a key in the configuration via dot-notation.
     *
     * @param string $key The dotted key representation.
     * @param mixed $value The value to set.
     *
     * @return void
     */
    public static function set(string $key, $value): void
    {
        static::load();

        Misc::setArrayValueByKey(static::$config, $key, $value);
    }

    /**
     * Returns the currently loaded configuration.
     *
     * @return array
     */
    public static function getAll(): ?array
    {
        static::load();

        return static::$config;
    }
}
