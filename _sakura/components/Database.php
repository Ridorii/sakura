<?php
/*
 * Database engine container
 */
 
namespace Sakura;

class Database {

    // Database container
    private static $_DATABASE;

    // Initialisation function
    public static function init($engine) {

        // Make the engine class name lowercase
        $engine = __NAMESPACE__ .'\DBWrapper\\'. strtolower($engine);

        // Check if the class exists
        if(!class_exists($engine))
            trigger_error('Failed to load database driver', E_USER_ERROR);

        // Initialise SQL engine
        self::$_DATABASE = new $engine;

    }

    // Fetch from database
    public static function fetch($table, $fetchAll = true, $data = null, $order = null, $limit = null, $group = null, $distinct = false, $column = '*', $prefix = null) {

        return self::$_DATABASE->fetch($table, $fetchAll, $data, $order, $limit, $group, $distinct, $column, $prefix);

    }

    // Insert into database
    public static function insert($table, $data, $prefix = null) {

        return self::$_DATABASE->insert($table, $data, $prefix);

    }

    // Update in database
    public static function update($table, $data, $prefix = null) {

        return self::$_DATABASE->update($table, $data, $prefix);

    }

    // Delete from database
    public static function delete($table, $data, $prefix = null) {

        return self::$_DATABASE->delete($table, $data, $prefix);

    }

}
