<?php
/*
 * Sakura MySQL Database Engine
 */

namespace Sakura\DBWrapper;

use PDO;
use PDOException;
use \Sakura\Config;

/**
 * Class MySQL
 * @package Sakura\DBWrapper
 */
class mysql
{
    // Variable that will contain the SQL connection
    // Please refrain from referring to this, unless it's for your personal branch/purpose, despite it being public
    // it sort of defeats the "dynamic database system" I want to go for.
    public $sql;

    // Constructor
    public function __construct()
    {
        if (!extension_loaded('PDO')) {
            // Return error and die
            trigger_error('PDO extension not loaded.', E_USER_ERROR);
        }

        // Initialise connection
        $this->initConnect(
            (
                Config::getLocalConfig('database', 'unixsocket') ?
                $this->prepareSock(
                    Config::getLocalConfig('database', 'host'),
                    Config::getLocalConfig('database', 'database')
                ) :
                $this->prepareHost(
                    Config::getLocalConfig('database', 'host'),
                    Config::getLocalConfig('database', 'database'),
                    (
                        Config::getLocalConfig('database', 'port') !== null ?
                        Config::getLocalConfig('database', 'port') :
                        3306
                    )
                )
            ),
            Config::getLocalConfig('database', 'username'),
            Config::getLocalConfig('database', 'password')
        );
    }

    // Regular IP/Hostname connection method prepare function
    private function prepareHost($dbHost, $dbName, $dbPort = 3306)
    {
        $dsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName;

        return $dsn;
    }

    // Unix Socket connection method prepare function
    private function prepareSock($dbHost, $dbName)
    {
        $dsn = 'mysql:unix_socket=' . $dbHost . ';dbname=' . $dbName;

        return $dsn;
    }

    // Initialise connection using default PDO stuff
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

    public function select($table, $data = null, $order = null, $limit = null, $group = null, $distinct = false, $column = '*', $prefix = null)
    {

        // Begin preparation of the statement
        $prepare = 'SELECT ' . ($distinct ? 'DISTINCT ' : '') . ($column == '*' ? '' : '`') . $column . ($column == '*' ? '' : '`') . ' FROM `' . ($prefix ? $prefix : Config::getLocalConfig('database', 'prefix')) . $table . '`';

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

    // Fetch array from database
    public function fetch($table, $fetchAll = true, $data = null, $order = null, $limit = null, $group = null, $distinct = false, $column = '*', $prefix = null)
    {

        // Run a select statement
        $query = $this->select($table, $data, $order, $limit, $group, $distinct, $column, $prefix);

        // Return the output
        return $fetchAll ? $query->fetchAll(PDO::FETCH_ASSOC) : $query->fetch(PDO::FETCH_ASSOC);
    }

    // Insert data to database
    public function insert($table, $data, $prefix = null)
    {

        // Begin preparation of the statement
        $prepare = 'INSERT INTO `' . ($prefix ? $prefix : Config::getLocalConfig('database', 'prefix')) . $table . '` ';

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

    // Update data in the database
    public function update($table, $data, $prefix = null)
    {

        // Begin preparation of the statement
        $prepare = 'UPDATE `' . ($prefix ? $prefix : Config::getLocalConfig('database', 'prefix')) . $table . '`';

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

    // Delete data from the database
    public function delete($table, $data, $prefix = null)
    {

        // Begin preparation of the statement
        $prepare = 'DELETE FROM `' . ($prefix ? $prefix : Config::getLocalConfig('database', 'prefix')) . $table . '`';

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

    // Count data from the database
    public function count($table, $data = null, $prefix = null)
    {

        // Begin preparation of the statement
        $prepare = 'SELECT COUNT(*) FROM `' . ($prefix ? $prefix : Config::getLocalConfig('database', 'prefix')) . $table . '`';

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

    // Get the ID of the last inserted item
    public function lastInsertID($name = null)
    {
        return $this->sql->lastInsertID($name);
        ;
    }
}