<?php
/*
 * Database wrapper container
 */

namespace Sakura;

/**
 * Class Database
 * @package Sakura
 */
class Database
{
    // Database container
    public static $database;

    // Initialisation function
    public static function init($wrapper)
    {

        // Make the wrapper class name lowercase
        $wrapper = __NAMESPACE__ . '\DBWrapper\\' . strtolower($wrapper);

        // Check if the class exists
        if (!class_exists($wrapper)) {
            trigger_error('Failed to load database wrapper', E_USER_ERROR);
        }

        // Initialise SQL wrapper
        self::$database = new $wrapper;
    }

    // Select from database
    public static function select($table, $data = null, $order = null, $limit = null, $group = null, $distinct = false, $column = '*', $prefix = null)
    {
        return self::$database->select($table, $data, $order, $limit, $group, $distinct, $column, $prefix);
    }

    // Fetch from database
    public static function fetch($table, $fetchAll = true, $data = null, $order = null, $limit = null, $group = null, $distinct = false, $column = '*', $prefix = null)
    {
        return self::$database->fetch($table, $fetchAll, $data, $order, $limit, $group, $distinct, $column, $prefix);
    }

    // Insert into database
    public static function insert($table, $data, $prefix = null)
    {
        return self::$database->insert($table, $data, $prefix);
    }

    // Update in database
    public static function update($table, $data, $prefix = null)
    {
        return self::$database->update($table, $data, $prefix);
    }

    // Delete from database
    public static function delete($table, $data, $prefix = null)
    {
        return self::$database->delete($table, $data, $prefix);
    }

    // Count from database
    public static function count($table, $data = null, $prefix = null)
    {
        return self::$database->count($table, $data, $prefix);
    }

    // Get the ID of the last inserted item
    public static function lastInsertID($name = null)
    {
        return self::$database->lastInsertID($name);
    }
}
