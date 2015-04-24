<?php
/*
 * Sakura MySQL Database Engine
 */

namespace Sakura;

use PDO;
use PDOException;
use PDOStatement;

class Database {

    // Variable that will contain the SQL connection
    // Please refrain from referring to this, unless it's for your personal branch/purpose, despite it being public
    // it sort of defeats the "dynamic database system" I want to go for.
    public static $sql;

    // Constructor
    public static function init() {
        
        if(!extension_loaded('PDO')) {
            // Return error and die
            trigger_error('<b>SQL Driver</b>: PDO extension not loaded.', E_USER_ERROR);
        }

        // Initialise connection
        self::initConnect(
            (
                Configuration::getLocalConfig('db', 'unixsocket') ?
                self::prepareSock(
                    Configuration::getLocalConfig('db', 'host'),
                    Configuration::getLocalConfig('db', 'database')
                ) :
                self::prepareHost(
                    Configuration::getLocalConfig('db', 'host'),
                    Configuration::getLocalConfig('db', 'database'),
                    (
                        Configuration::getLocalConfig('db', 'port') !== null ?
                        Configuration::getLocalConfig('db', 'port') :
                        3306
                    )
                )
            ),
            Configuration::getLocalConfig('db', 'username'),
            Configuration::getLocalConfig('db', 'password')
        );
        
    }

    // Regular IP/Hostname connection method prepare function
    private static function prepareHost($dbHost, $dbName, $dbPort = 3306) {
        
        $DSN = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName;

        return $DSN;
        
    }

    // Unix Socket connection method prepare function
    private static function prepareSock($dbHost, $dbName) {
        
        $DSN = 'mysql:unix_socket=' . $dbHost . ';dbname=' . $dbName;

        return $DSN;
        
    }

    // Initialise connection using default PDO stuff
    private static function initConnect($DSN, $dbUname, $dbPword) {
        
        try {
            // Connect to SQL server using PDO
            self::$sql = new PDO($DSN, $dbUname, $dbPword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        } catch(PDOException $e) {
            // Catch connection errors
            trigger_error('SQL Driver: '. $e->getMessage(), E_USER_ERROR);
        }
        
        return true;
        
    }

    // Fetch array from database
    public static function fetch($table, $fetchAll = true, $data = null, $order = null, $limit = null, $group = null, $distinct = false, $column = '*') {

        // Begin preparation of the statement
        $prepare = 'SELECT '. ($distinct ? 'DISTINCT ' : '') . ($column == '*' ? '' : '`') . $column . ($column == '*' ? '' : '`') .' FROM `' . Configuration::getLocalConfig('db', 'prefix') . $table . '`';

        // If $data is set and is an array continue
        if(is_array($data)) {

            $prepare .= ' WHERE';

            foreach($data as $key => $value) {
                $prepare .= ' `'. $key .'` '. $value[1] .' :'. $key . ($key == key(array_slice($data, -1, 1, true)) ? '' : ' AND');

                // Unset variables to be safe
                unset($key);
                unset($value);
            }

        }

        // If $order is set and is an array continue
        if(is_array($order)) {

            $prepare .= ' ORDER BY `'. $order[0] .'`'. (!empty($order[1]) && $order[1] ? ' DESC' : '');

        }

        // If $group is set and is an array continue
        if(is_array($group)) {

            $prepare .= ' GROUP BY';

            foreach($group as $key => $value) {
                $prepare .= ' `'. $value .'`'. ($key == key(array_slice($group, -1, 1, true)) ? '' : ',');

                // Unset variables to be safe
                unset($key);
                unset($value);
            }

        }
        
        // If $limit is set and is an array continue
        if(is_array($limit)) {

            $prepare .= ' LIMIT';

            foreach($limit as $key => $value) {
                $prepare .= ' '. $value . ($key == key(array_slice($limit, -1, 1, true)) ? '' : ',');

                // Unset variables to be safe
                unset($key);
                unset($value);
            }

        }

        // Add the finishing semicolon
        $prepare .= ';';

        // Actually prepare the preration
        $query = self::$sql->prepare($prepare);

        // Bind those parameters if $data is an array that is
        if(is_array($data)) {
            foreach($data as $key => $value) {
                $query->bindParam(':'. $key, $value[0]);

                // Unset variables to be safe
                unset($key);
                unset($value);
            }
        }

        // Execute the prepared statements with parameters bound
        $query->execute();

        // Do fetch or fetchAll
        if($fetchAll)
            $result = $query->fetchAll(PDO::FETCH_BOTH);
        else
            $result = $query->fetch(PDO::FETCH_BOTH);


        // And return the output
        return $result;

    }
    
    // Insert data to database
    public static function insert($table, $data) {

        // Begin preparation of the statement
        $prepare = 'INSERT INTO `' . Configuration::getLocalConfig('db', 'prefix') . $table . '` ';

        // Run the foreach statement twice for (`stuff`) VALUES (:stuff)
        for($i = 0; $i < 2; $i++) {

            $prepare .= '(';

            // Do more shit, don't feel like describing this so yeah
            foreach($data as $key => $value) {
                if(strlen($value))
                    $prepare .= ($i ? ':' : '`') . $key . ($i ? '' : '`') . ($key == key(array_slice($data, -1, 1, true)) ? '' : ', ');
            }

            $prepare .= ')' . ($i ? ';' : ' VALUES ');

        }

        // Actually prepare the preration
        $query = self::$sql->prepare($prepare);

        // Bind those parameters
        foreach($data as $key => $value) {
            if(strlen($value))
                $query->bindParam(':'. $key, $value);

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
    public static function update($table, $data) {

        // Begin preparation of the statement
        $prepare = 'UPDATE `' . Configuration::getLocalConfig('db', 'prefix') . $table . '`';

        // Run a foreach on $data and complete the statement
        foreach($data as $key => $values) {

            // Append WHERE or SET depending on where we are
            $prepare .= ' '. ($key ? 'WHERE' : 'SET');

            // Do this complicated shit, I barely know what's going on anymore but it works
            foreach($values as $column => $column_data)
                $prepare .= ' `'. $column .'` '. ($key ? $column_data[1] : '=') .' :'. ($key ? 'w' : 's') .'_'. $column . ($column == key(array_slice($values, -1, 1, true)) ? ($key ? ';' : '') : ($key ? ' AND' : ','));

        }

        // Actually prepare the preration
        $query = self::$sql->prepare($prepare);

        // Seperate the foreaches for the SET and WHERE clauses because it's fucking it up for some odd reason
        // Bind Set Clauses
        foreach($data[0] as $key => $value) {

            // Do the binding
            $query->bindParam(':s_'. $key, $value);

            // Unset variables to be safe
            unset($key);
            unset($value);

        }

        // Bind Where Clauses
        foreach($data[1] as $key => $values) {

            // Assign the array entry to a variable because fuck strict standards
            $value = $values[0];

            // Binding two electrifying memes
            $query->bindParam(':w_'. $key, $value);

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
    public static function delete($table, $data) {

        // Begin preparation of the statement
        $prepare = 'DELETE FROM `' . Configuration::getLocalConfig('db', 'prefix') . $table . '`';

        // If $data is set and is an array continue
        if(is_array($data)) {

            $prepare .= ' WHERE';

            foreach($data as $key => $value) {
                $prepare .= ' `'. $key .'` '. $value[1] .' :'. $key . ($key == key(array_slice($data, -1, 1, true)) ? '' : ' AND');

                // Unset variables to be safe
                unset($key);
                unset($value);
            }

        }

        // Actually prepare the preration
        $query = self::$sql->prepare($prepare);

        // Bind those parameters
        foreach($data as $key => $value) {
            $query->bindParam(':'. $key, $value[0]);

            // Unset variables to be safe
            unset($key);
            unset($value);
        }

        // Execute the prepared statements with parameters bound
        $result = $query->execute();

        // Return whatever can be returned
        return $result;

    }

}
