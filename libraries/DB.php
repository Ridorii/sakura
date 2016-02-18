<?php
/**
 * Holds the database wrapper (v2).
 * 
 * @package Sakura
 */

namespace Sakura;

use PDO;
use PDOException;
use PDOStatement;

/**
 * A wrapper to make the database communication experience smoother.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class DB
{
    /**
     * The container for the PDO object.
     * 
     * @var PDO
     */
    public static $db = null;

    /**
     * The table prefix
     * 
     * @var string
     */
    public static $prefix = '';

    /**
     * Open the SQL connection and creates a PDO object.
     * 
     * @param string $server A PDO driver.
     * @param array $dsnParts An array consisting out of DSN string parts.
     * @param string $username The username used to authenticate with the SQL server.
     * @param string $password The password for the same purpose.
     * @param array $options Additional PDO options.
     */
    public static function open($server, $dsnParts, $username = null, $password = null, $prefix = '', $options = [])
    {
        // Check if the selected driver is available
        if (!in_array($server, PDO::getAvailableDrivers())) {
            trigger_error('A driver for the selected SQL server wasn\'t found!', E_USER_ERROR);
            return;
        }

        // Set the table prefix
        self::$prefix = $prefix;

        // Create start of the DSN
        $dsn = "{$server}:";

        // Append the parts
        foreach ($dsnParts as $name => $value) {
            $dsn .= "{$name}={$value};";
        }

        try {
            // Connect to SQL server using PDO
            self::$db = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Catch connection errors
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
        
        self::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }

    /**
     * Closes the PDO object.
     */
    public static function close()
    {
        self::$db = null;
    }

    /**
     * Get the id of the item that was last inserted into the database.
     * 
     * @param string $name Sequence of which the last id should be returned.
     * 
     * @return string The last inserted id.
     */
    public static function lastID($name = null)
    {
        return self::$db->lastInsertID($name);
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     * 
     * @param string $stmt The statement to prepare.
     * @param array $opts Statement specific driver options.
     * 
     * @return PDOStatement
     */
    public static function prepare($stmt, $opts = [])
    {
        // Replace the table prefix
        $stmt = str_replace('{prefix}', self::$prefix, $stmt);

        return self::$db->prepare($stmt, $opts);
    }
}
