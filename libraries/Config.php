<?php
/**
 * Holds the configuration manager.
 *
 * @package Sakura
 */

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
     * Cache for the configuration stored in the database.
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
     * Get a value from the local configuration file.
     *
     * @param string $key Configuration section.
     * @param string $subkey Configuration key.
     *
     * @return array|string Configuration value.
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
     * @param string $default Value that gets used when the value doesn't exist.
     *
     * @return string Configuration value.
     */
    public static function get($key, $default = null)
    {
        // Check if the key that we're looking for exists
        if (array_key_exists($key, self::$database)) {
            // Then return the value
            return self::$database[$key];
        } else {
            $value = DB::prepare('SELECT * FROM `{prefix}config` WHERE `config_name` = :name');
            $value->execute([
                'name' => $key,
            ]);
            $value = $value->fetch();
            if ($value) {
                self::$database[$key] = $value->config_value;
                return self::$database[$key];
            }
        }

        // If we fell all the way down here set the bundled default value
        Config::set($key, $default);

        // And then return default that value
        return $default;
    }

    public static function set($key, $value)
    {
        // Unset the cached copy
        if (array_key_exists($key, self::$database)) {
            unset(self::$database[$key]);
        }

        // Check if the value already exists
        $exists = DB::prepare('SELECT * FROM `{prefix}config` WHERE `config_name` = :name');
        $exists->execute([
            'name' => $key,
        ]);

        // If it exists run an update
        if ($exists->rowCount()) {
            $set = DB::prepare('UPDATE `{prefix}config` SET `config_value` = :value WHERE `config_name` = :name');
        } else {
            $set = DB::prepare('INSERT INTO `{prefix}config` (`config_name`, `config_value`) VALUES (:name, :value)');
        }

        // Run the setter
        $set->execute([
            'name' => $key,
            'value' => $value,
        ]);

        // Return the value
        return $value;
    }
}
