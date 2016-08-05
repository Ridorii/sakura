<?php
/**
 * Holds the configuration manager.
 * @package Sakura
 */

namespace Sakura;

/**
 * Handles the configuration settings of Sakura.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Config
{
    /**
     * Storage for the parsed config file.
     * @var array
     */
    private static $config = [];

    /**
     * Initialiser, parses the configuration.
     * @param string $path
     */
    public static function init($path)
    {
        // Check if the configuration file exists
        if (!file_exists($path)) {
            throw new Exception('Configuration file does not exist');
        }

        // Attempt to load the configuration file
        $config = parse_ini_file($path, true);

        if (is_array($config)) {
            self::$config = $config;
        } else {
            throw new Exception('Failed to parse configuration');
        }
    }

    /**
     * Get a value from the configuration.
     * @param string $section
     * @param string $key
     * @return array|string
     */
    public static function get($section, $key = null)
    {
        // Check if the key that we're looking for exists
        if (array_key_exists($section, self::$config)) {
            if ($key) {
                // If we also have a subkey return the proper data
                return self::$config[$section][$key];
            }

            // else we just return the default value
            return self::$config[$section];
        }

        throw new Exception("Couldn't find configuration value");
    }
}
