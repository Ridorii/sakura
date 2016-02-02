<?php
namespace Sakura;

/**
 * Handles both the local and database stored configuration sides of Sakura.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Config
{
    /**
     * Container for the parsed local configuration.
     * 
     * @var array
     */
    private static $local = [];

    /**
     * Container for the configuration stored in the database.
     * 
     * @var array
     */
    private static $database = [];

    /**
     * Initialiser, parses the local configuration.
     * 
     * @param string $local Path to the configuration file.
     */
    public static function init($local)
    {

        // Check if the configuration file exists
        if (!file_exists($local)) {
            trigger_error('Local configuration file does not exist', E_USER_ERROR);
        }

        // Attempt to load the configuration file
        $local = parse_ini_file($local, true);

        // Check if $local is an array and then store it in $local
        if (is_array($local)) {
            self::$local = $local;
        } else {
            // Otherwise trigger an error
            trigger_error(
                'Failed to load local configuration file,' .
                ' check the structure of the file to see if you made mistake somewhere',
                E_USER_ERROR
            );
        }
    }

    /**
     * Fetch configuration values from the database.
     */
    public static function initDB()
    {

        // Get config table from the database
        $_DATA = Database::fetch('config', true);

        // Create variable to temporarily store values in
        $_DBCN = [];

        // Properly sort the values
        foreach ($_DATA as $_CONF) {
            $_DBCN[$_CONF['config_name']] = $_CONF['config_value'];
        }

        // Assign the temporary array to the static one
        self::$database = $_DBCN;
    }

    /**
     * Get a value from the local configuration file.
     * 
     * @param string $key Configuration section.
     * @param string $subkey Configuration key.
     * 
     * @return string Configuration value.
     */
    public static function local($key, $subkey = null)
    {

        // Check if the key that we're looking for exists
        if (array_key_exists($key, self::$local)) {
            if ($subkey) {
                // If we also have a subkey return the proper data
                return self::$local[$key][$subkey];
            }

            // else we just return the default value
            return self::$local[$key];
        }

        // If it doesn't exist trigger an error to avoid explosions
        trigger_error(
            'Unable to get local configuration value "' . $key . '"',
            E_USER_ERROR
        );
        return null;
    }

    /**
     * Get a configuration value from the database.
     * 
     * @param string $key Configuration key.
     * @param bool $returnNull Unused value, only exists to prevent explosions.
     * 
     * @return string Configuration value.
     */
    public static function get($key, $returnNull = false)
    {

        // Check if the key that we're looking for exists
        if (array_key_exists($key, self::$database)) {
            // Then return the value
            return self::$database[$key];
        }

        // Then return the value
        trigger_error(
            'Unable to get configuration value "' . $key . '"',
            E_USER_ERROR
        );
        return null;
    }
}
