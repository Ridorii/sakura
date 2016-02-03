<?php
/**
 * Holds the MySQL/PDO wrapper.
 * 
 * @package Sakura
 */

namespace Sakura\DBWrapper;

use PDO;
use PDOException;
use PDOStatement;
use \Sakura\Config;

/**
 * Sakura MySQL Wrapper.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class mysql
{
    /**
     * Contains the PDO object.
     * 
     * @var PDO
     */
    protected $sql;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (!extension_loaded('PDO')) {
            // Return error and die
            trigger_error('PDO extension not loaded.', E_USER_ERROR);
        }

        // Initialise connection
        $this->initConnect(
            (
                Config::local('database', 'unixsocket') ?
                $this->prepareSock(
                    Config::local('database', 'host'),
                    Config::local('database', 'database')
                ) :
                $this->prepareHost(
                    Config::local('database', 'host'),
                    Config::local('database', 'database'),
                    (
                        Config::local('database', 'port') !== null ?
                        Config::local('database', 'port') :
                        3306
                    )
                )
            ),
            Config::local('database', 'username'),
            Config::local('database', 'password')
        );
    }

    /**
     * Generates a DSN for a regular hostname:port endpoint.
     * 
     * @param string $dbHost Database hostname.
     * @param string $dbName Database name.
     * @param int $dbPort Database host port.
     * 
     * @return string The PDO DSN.
     */
    private function prepareHost($dbHost, $dbName, $dbPort = 3306)
    {
        $dsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName;

        return $dsn;
    }

    /**
     * Generates a DSN for a unix socket endpoint.
     * 
     * @param string $dbHost Path to the Unix Socket.
     * @param string $dbName Database name.
     * 
     * @return string The PDO DSN.
     */
    private function prepareSock($dbHost, $dbName)
    {
        $dsn = 'mysql:unix_socket=' . $dbHost . ';dbname=' . $dbName;

        return $dsn;
    }

    /**
     * Initialise a the database connection.
     * 
     * @param string $dsn The PDO DSN.
     * @param string $dbUname The database username.
     * @param string $dbPword The database password.
     * 
     * @return bool Returns true if the connection was successful.
     */
    private function initConnect($dsn, $dbUname, $dbPword)
    {
        try {
            // Connect to SQL server using PDO
            $this->sql = new PDO($dsn, $dbUname, $dbPword, [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
            ]);
        } catch (PDOException $e) {
            // Catch connection errors
            trigger_error('SQL Driver: ' . $e->getMessage(), E_USER_ERROR);
        }

        return true;
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
     * @return PDOStatement The PDOStatement object for this action.
     */
    public function select($table, $data = null, $order = null, $limit = null, $group = null, $distinct = false, $column = '*', $prefix = null)
    {

        // Begin preparation of the statement
        $prepare = 'SELECT ' . ($distinct ? 'DISTINCT ' : '') . ($column == '*' ? '' : '`') . $column . ($column == '*' ? '' : '`') . ' FROM `' . ($prefix ? $prefix : Config::local('database', 'prefix')) . $table . '`';

        // If $data is set and is an array continue
        if (is_array($data)) {
            $prepare .= ' WHERE';

            foreach ($data as $key => $value) {
                // Check if there's multiple statements
                if (!is_array($value[0])) {
                    $temp = $value;
                    unset($value);
                    $value[0] = $temp;
                }

                // Go over each data thing
                foreach ($value as $sub => $val) {
                    $prepare .= ' `' . $key . '` ' . $val[1] . ' :' . $key . '_' . $sub . ($key == key(array_slice($data, -1, 1, true)) && $sub == key(array_slice($value, -1, 1, true)) ? '' : ' ' . (isset($val[2]) && $val[2] ? 'OR' : 'AND'));

                    unset($sub);
                    unset($val);
                }

                // Unset variables to be safe
                unset($key);
                unset($value);
            }
        }

        // If $group is set and is an array continue
        if (is_array($group)) {
            $prepare .= ' GROUP BY';

            foreach ($group as $key => $value) {
                $prepare .= ' `' . $value . '`' . ($key == key(array_slice($group, -1, 1, true)) ? '' : ',');

                // Unset variables to be safe
                unset($key);
                unset($value);
            }
        }

        // If $order is set and is an array continue
        if (is_array($order)) {
            $prepare .= ' ORDER BY `' . $order[0] . '`' . (!empty($order[1]) && $order[1] ? ' DESC' : '');
        }

        // If $limit is set and is an array continue
        if (is_array($limit)) {
            $prepare .= ' LIMIT';

            foreach ($limit as $key => $value) {
                $prepare .= ' ' . $value . ($key == key(array_slice($limit, -1, 1, true)) ? '' : ',');

                // Unset variables to be safe
                unset($key);
                unset($value);
            }
        }

        // Add the finishing semicolon
        $prepare .= ';';

        // Actually prepare the preration
        $query = $this->sql->prepare($prepare);

        // Bind those parameters if $data is an array that is
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                // Check if there's multiple statements
                if (!is_array($value[0])) {
                    $temp = $value;
                    unset($value);
                    $value[0] = $temp;
                }

                // Go over each data thing
                foreach ($value as $sub => $val) {
                    $query->bindParam(':' . $key . '_' . $sub, $val[0]);

                    unset($sub);
                    unset($val);
                }

                // Unset variables to be safe
                unset($key);
                unset($value);
            }
        }

        // Execute the prepared statements with parameters bound
        $query->execute();

        // Return the query
        return $query;
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
    public function fetch($table, $fetchAll = true, $data = null, $order = null, $limit = null, $group = null, $distinct = false, $column = '*', $prefix = null)
    {

        // Run a select statement
        $query = $this->select($table, $data, $order, $limit, $group, $distinct, $column, $prefix);

        // Return the output
        return $fetchAll ? $query->fetchAll(PDO::FETCH_ASSOC) : $query->fetch(PDO::FETCH_ASSOC);
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
    public function insert($table, $data, $prefix = null)
    {

        // Begin preparation of the statement
        $prepare = 'INSERT INTO `' . ($prefix ? $prefix : Config::local('database', 'prefix')) . $table . '` ';

        // Run the foreach statement twice for (`stuff`) VALUES (:stuff)
        for ($i = 0; $i < 2; $i++) {
            $prepare .= '(';
            // Do more shit, don't feel like describing this so yeah
            foreach ($data as $key => $value) {
                if (strlen($value)) {
                    $prepare .= ($i ? ':' : '`') . $key . ($i ? '' : '`') . ($key == key(array_slice($data, -1, 1, true)) ? '' : ', ');
                }
            }

            $prepare .= ')' . ($i ? ';' : ' VALUES ');
        }

        // Actually prepare the preration
        $query = $this->sql->prepare($prepare);

        // Bind those parameters
        foreach ($data as $key => $value) {
            if (strlen($value)) {
                $query->bindParam(':' . $key, $value);
            }

            // Unset variables to be safe
            unset($key);
            unset($value);
        }

        // Execute the prepared statements with parameters bound
        $result = $query->execute();

        // Return whatever can be returned
        return $result;
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
    public function update($table, $data, $prefix = null)
    {

        // Begin preparation of the statement
        $prepare = 'UPDATE `' . ($prefix ? $prefix : Config::local('database', 'prefix')) . $table . '`';

        // Run a foreach on $data and complete the statement
        foreach ($data as $key => $values) {
            // Append WHERE or SET depending on where we are
            $prepare .= ' ' . ($key ? 'WHERE' : 'SET');

            // Do this complicated shit, I barely know what's going on anymore but it works
            foreach ($values as $column => $column_data) {
                $prepare .= ' `' . $column . '` ' . ($key ? $column_data[1] : '=') . ' :' . ($key ? 'w' : 's') . '_' . $column . ($column == key(array_slice($values, -1, 1, true)) ? ($key ? ';' : '') : ($key ? ' ' . (isset($value[2]) && $value[2] ? 'OR' : 'AND') : ','));
            }
        }

        // Actually prepare the preration
        $query = $this->sql->prepare($prepare);

        // Seperate the foreaches for the SET and WHERE clauses because it's fucking it up for some odd reason
        // Bind Set Clauses
        foreach ($data[0] as $key => $value) {
            // Do the binding
            $query->bindParam(':s_' . $key, $value);

            // Unset variables to be safe
            unset($key);
            unset($value);
        }

        // Bind Where Clauses
        foreach ($data[1] as $key => $values) {
            // Assign the array entry to a variable because fuck strict standards
            $value = $values[0];

            // Binding two electrifying memes
            $query->bindParam(':w_' . $key, $value);

            // Unset variables to be safe
            unset($key);
            unset($value);
            unset($values);
        }

        // Execute the prepared statements with parameters bound
        $result = $query->execute();

        // Return whatever can be returned
        return $result;
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
    public function delete($table, $data, $prefix = null)
    {

        // Begin preparation of the statement
        $prepare = 'DELETE FROM `' . ($prefix ? $prefix : Config::local('database', 'prefix')) . $table . '`';

        // If $data is set and is an array continue
        if (is_array($data)) {
            $prepare .= ' WHERE';

            foreach ($data as $key => $value) {
                $prepare .= ' `' . $key . '` ' . $value[1] . ' :' . $key . ($key == key(array_slice($data, -1, 1, true)) ? '' : ' ' . (isset($value[2]) && $value[2] ? 'OR' : 'AND'));

                // Unset variables to be safe
                unset($key);
                unset($value);
            }
        }

        // Actually prepare the preration
        $query = $this->sql->prepare($prepare);

        // Bind those parameters
        foreach ($data as $key => $value) {
            $query->bindParam(':' . $key, $value[0]);

            // Unset variables to be safe
            unset($key);
            unset($value);
        }

        // Execute the prepared statements with parameters bound
        $result = $query->execute();

        // Return whatever can be returned
        return $result;
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
    public function count($table, $data = null, $prefix = null)
    {

        // Begin preparation of the statement
        $prepare = 'SELECT COUNT(*) FROM `' . ($prefix ? $prefix : Config::local('database', 'prefix')) . $table . '`';

        // If $data is set and is an array continue
        if (is_array($data)) {
            $prepare .= ' WHERE';

            foreach ($data as $key => $value) {
                $prepare .= ' `' . $key . '` ' . $value[1] . ' :' . $key . ($key == key(array_slice($data, -1, 1, true)) ? '' : ' ' . (isset($value[2]) && $value[2] ? 'OR' : 'AND'));

                // Unset variables to be safe
                unset($key);
                unset($value);
            }
        }

        // Add the finishing semicolon
        $prepare .= ';';

        // Actually prepare the preration
        $query = $this->sql->prepare($prepare);

        // Bind those parameters if $data is an array that is
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $query->bindParam(':' . $key, $value[0]);

                // Unset variables to be safe
                unset($key);
                unset($value);
            }
        }

        // Execute the prepared statements with parameters bound
        $query->execute();

        // Return the output
        return $query->fetch(PDO::FETCH_BOTH);
    }

    /**
     * Get the id of the item that was last inserted into the database.
     * 
     * @param string $name Sequence of which the last id should be returned.
     * 
     * @return string The last inserted id.
     */
    public function lastInsertID($name = null)
    {
        return $this->sql->lastInsertID($name);
    }
}
