<?php
/*
 * Configuration Management
 */

namespace Flashii;

class Configuration {

    public static $_LCNF;
    public static $_DCNF;

	// Constructor
    public static function init($local) {

        // Store $local in $_LCNF
        if(is_array($local))
            self::$_LCNF = $local;
        else
            die('<h1>Failed to initialise configuration.</h1>');

    }

    // Initialise Database configuration values.
    // Different from init as that is called before the database connection is initially
    // established
    public static function initDB() {

        $_DATA = Database::fetch('config', true);
        $_DBCN = array();

        foreach($_DATA as $_CONF)
            $_DBCN[$_CONF[0]] = $_CONF[1];

        self::$_DCNF = $_DBCN;

    }

	// Get values from the configuration on the file system
	public static function getLocalConfig($key, $subkey = null) {

		if(array_key_exists($key, self::$_LCNF)) {
			if($subkey)
				return self::$_LCNF[$key][$subkey];
			else
				return self::$_LCNF[$key];
		} else
			return null;

	}
    
	// Dynamically set local configuration values, does not update the configuration file
	public static function setLocalConfig($key, $subkey, $value) {

		if($subkey) {
			if(!isset(self::$_LCNF[$key]))
				self::$_LCNF[$key] = array();
			self::$_LCNF[$key][$subkey] = $value;
		} else {
			self::$_LCNF[$key] = $value;
		}

	}

	// Get values from the configuration in the database
	public static function getConfig($key) {

		if(array_key_exists($key, self::$_DCNF))
            return self::$_DCNF[$key];
		else
			return null;

	}

}
