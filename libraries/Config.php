<?php
/*
 * Configuration Management
 */

namespace Sakura;

/**
 * Class Config
 * @package Sakura
 */
class Config
{
    // Configuration data
    private static $local = [];
    private static $database = [];

    // Initialise configuration, does not contain database initialisation because explained below
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

    /*
     * Initialise Database configuration values.
     * Different from init as that is called before the database connection is initially
     * established.
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

    // Get values from the configuration on the file system
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

    // Get values from the configuration in the database
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
