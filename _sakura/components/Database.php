<?php
/*
 * Database wrapper container
 */

namespace Sakura;

class Database {

    // Database container
    public static $_DATABASE;

    // Initialisation function
    public static function init($wrapper) {

        // Make the wrapper class name lowercase
        $wrapper = __NAMESPACE__ .'\DBWrapper\\'. strtolower($wrapper);

        // Check if the class exists
        if(!class_exists($wrapper)) {

            trigger_error('Failed to load database wrapper', E_USER_ERROR);

        }

        // Initialise SQL wrapper
        self::$_DATABASE = new $wrapper;

    }

    // Select from database
    public static function select($table, $data = null, $order = null, $limit = null, $group = null, $distinct = false, $column = '*', $prefix = null) {

        return self::$_DATABASE->select($table, $data, $order, $limit, $group, $distinct, $column, $prefix);

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

    // Count from database
    public static function count($table, $data, $prefix = null) {

        return self::$_DATABASE->count($table, $data, $prefix);

    }

}
