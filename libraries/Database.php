<?php
/**
 * Holds the database wrapper interfacer.
 * 
 * @package Sakura
 */

namespace Sakura;

/**
 * A wrapper to make the database communication experience smoother.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Database
{
    /**
     * The container for the wrapper.
     * 
     * @var mixed
     */
    public static $database;

    /**
     * Initialise the database wrapper.
     * 
     * @param string $wrapper The wrapper to wrap.
     */
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

    /**
     * Select table row(s).
     * 
     * @param string $table The table to select data from.
     * @param array $data The WHERE selectors.
     * @param array $order The order in which the data is returned.
     * @param array $limit The limit of what should be returned.
     * @param array $group The way MySQL will group the data.
     * @param bool $distinct Only return distinct values.
     * @param string $column Only select from this column.
     * @param string $prefix Use a different table prefix than the one from the configuration.
     * 
     * @return \PDOStatement The PDOStatement object for this action.
     */
    public static function select($table, $data = null, $order = null, $limit = null, $group = null, $distinct = false, $column = '*', $prefix = null)
    {
        return self::$database->select($table, $data, $order, $limit, $group, $distinct, $column, $prefix);
    }

    /**
     * Summary of fetch
     * 
     * @param string $table The table to select data from.
     * @param bool $fetchAll Whether all result will be returned or just the first one.
     * @param array $data The WHERE selectors.
     * @param array $order The order in which the data is returned.
     * @param array $limit The limit of what should be returned.
     * @param array $group The way MySQL will group the data.
     * @param bool $distinct Only return distinct values.
     * @param string $column Only select from this column.
     * @param string $prefix Use a different table prefix than the one from the configuration.
     * 
     * @return array The data the database returned.
     */
    public static function fetch($table, $fetchAll = true, $data = null, $order = null, $limit = null, $group = null, $distinct = false, $column = '*', $prefix = null)
    {
        return self::$database->fetch($table, $fetchAll, $data, $order, $limit, $group, $distinct, $column, $prefix);
    }

    /**
     * Insert data into the database.
     * 
     * @param string $table The table that the data will be inserted into.
     * @param array $data The data that should be stored.
     * @param string $prefix Use a different table prefix than the one from the configuration.
     * 
     * @return bool Successfulness.
     */
    public static function insert($table, $data, $prefix = null)
    {
        return self::$database->insert($table, $data, $prefix);
    }

    /**
     * Update existing database rows.
     * 
     * @param string $table The table that the updated data will be inserted into.
     * @param array $data The data that should be stored.
     * @param string $prefix Use a different table prefix than the one from the configuration.
     * 
     * @return bool Successfulness.
     */
    public static function update($table, $data, $prefix = null)
    {
        return self::$database->update($table, $data, $prefix);
    }

    /**
     * Deleted data from the database.
     * 
     * @param string $table The table that the data will be removed from.
     * @param array $data The pointers to what should be deleted.
     * @param string $prefix Use a different table prefix than the one from the configuration.
     * 
     * @return bool Successfulness.
     */
    public static function delete($table, $data, $prefix = null)
    {
        return self::$database->delete($table, $data, $prefix);
    }

    /**
     * Return the amount of rows from a table.
     * 
     * @param string $table Table to count in.
     * @param array $data Data that should be matched.
     * @param string $prefix Use a different table prefix than the one from the configuration.
     * 
     * @return array Array containing the SQL result.
     */
    public static function count($table, $data = null, $prefix = null)
    {
        return self::$database->count($table, $data, $prefix);
    }

    /**
     * Get the id of the item that was last inserted into the database.
     * 
     * @param string $name Sequence of which the last id should be returned.
     * 
     * @return string The last inserted id.
     */
    public static function lastInsertID($name = null)
    {
        return self::$database->lastInsertID($name);
    }
}
