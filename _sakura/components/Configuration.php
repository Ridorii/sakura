<?php
/*
 * Configuration Management
 */

namespace Sakura;

class Configuration {

    // Configuration data
    public static $_LCNF;
    public static $_DCNF;

	// Initialise configuration, does not contain database initialisation because explained below
    public static function init($local) {

        // Check if $local is an array and then store it in $_LCNF
        if(is_array($local))
            self::$_LCNF = $local;
        else // Otherwise trigger an error
            trigger_error('Failed to load local configuration!', E_USER_ERROR);

    }

    /*
     * Initialise Database configuration values.
     * Different from init as that is called before the database connection is initially
     * established.
     */
    public static function initDB() {

        // Get config table from the database
        $_DATA = Database::fetch('config', true);

        // Create variable to temporarily store values in
        $_DBCN = array();

        foreach($_DATA as $_CONF) // Properly sort the values
            $_DBCN[$_CONF[0]] = $_CONF[1];

        // Assign the temporary array to the static one
        self::$_DCNF = $_DBCN;

    }

	// Get values from the configuration on the file system
	public static function getLocalConfig($key, $subkey = null) {

        // Check if the key that we're looking for exists
		if(array_key_exists($key, self::$_LCNF)) {
			if($subkey) // If we also have a subkey return the proper shit
				return self::$_LCNF[$key][$subkey];
			else // else we just return the default value
				return self::$_LCNF[$key];
		} else // If it doesn't exist trigger an error to avoid explosions
			trigger_error('Unable to get local configuration value!', E_USER_ERROR);

	}
    
	// Dynamically set local configuration values, does not update the configuration file
	public static function setLocalConfig($key, $subkey, $value) {

        // Check if we also do a subkey
		if($subkey) {

            // If we do we make sure that the parent key is an array
			if(!isset(self::$_LCNF[$key]))
				self::$_LCNF[$key] = array();

            // And then assign the value
			self::$_LCNF[$key][$subkey] = $value;

		} else // Otherwise we just straight up assign it
			self::$_LCNF[$key] = $value;

	}

	// Get values from the configuration in the database
	public static function getConfig($key) {

        // Check if the key that we're looking for exists
		if(array_key_exists($key, self::$_DCNF))
            return self::$_DCNF[$key]; // Then return the value
		else // If it doesn't exist trigger an error to avoid explosions
			trigger_error('Unable to get configuration value!', E_USER_ERROR);

	}

}
