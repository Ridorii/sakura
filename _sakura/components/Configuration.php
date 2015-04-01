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
		if(array_key_exists($key, self::$_LCNF)) { // If we also have a subkey we check if that exists, else we just return the default value.
			if($subkey && is_array($key) && array_key_exists($subkey, $key))
				return self::$_LCNF[$key][$subkey];
			else
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

    // Parse .cfg files, mainly/only used for templates
	public static function parseCfg($data) {

        // Create storage variable
		$out = array();

        // Remove comments and empty lines
		$data = preg_replace('/#.*?\r\n/im',    null, $data);
		$data = preg_replace('/^\r\n/im',       null, $data);

        // Break line breaks up into array values
		$data = explode("\r\n", $data);

		foreach($data as $var) {

            // Remove whitespace between key, equals sign and value
			$var = preg_replace('/[\s+]=[\s+]/i', '=', $var);

            // Then break this up
			$var = explode('=', $var);

            // And assign the value with the key to the output variable
			$out[$var[0]] = $var[1];

		}

        // Return the output variable
		return $out;

	}

}
