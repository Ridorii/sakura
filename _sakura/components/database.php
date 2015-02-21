<?php
/*
 * Database Engine
 */

namespace Flashii;

use PDO;
use PDOException;
use PDOStatement;

class Database {

    // Variable that will contain the SQL connection
    // Please refrain from referring to this, unless it's for your personal branch/purpose, despite it being public
    // it sort of defeats the "dynamic database system" I want to go for.
    public static $sql;

    // Constructor
    function __construct() {
        if(!extension_loaded('PDO')) {
            // Return error and die
            trigger_error('PDO extension not loaded.');
        }

        // Initialise connection
        $this->initConnect(
            (
            Flashii::getConfig('db', 'unixsocket') ?
                $this->prepareSock(
                    Flashii::getConfig('db', 'host'),
                    Flashii::getConfig('db', 'database')
                ) :
                $this->prepareHost(
                    Flashii::getConfig('db', 'host'),
                    Flashii::getConfig('db', 'database'),
                    (
                        Flashii::getConfig('db', 'port') !== null ?
                        Flashii::getConfig('db', 'port') :
                        3306
                    )
                )
            ),
            Flashii::getConfig('db', 'username'),
            Flashii::getConfig('db', 'password')
        );
    }

    // Regular IP/Hostname connection method prepare function
    private function prepareHost($dbHost, $dbName, $dbPort = 3306) {
        $DSN = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName;

        return $DSN;
    }

    // Unix Socket connection method prepare function
    private function prepareSock($dbHost, $dbName) {
        $DSN = 'mysql:unix_socket=' . $dbHost . ';dbname=' . $dbName;

        return $DSN;
    }

    // Initialise connection using default PDO stuff
    private function initConnect($DSN, $dbUname, $dbPword) {
        try {
            // Connect to SQL server using PDO
            self::$sql = new PDO($DSN, $dbUname, $dbPword);
        } catch(PDOException $e) {
            // Catch connection errors
            trigger_error("SQL Connection Error: ". $e->getMessage());
        }
        return true;
    }

    // Fetch array from database
    public function fetchAll($table) {
        $query = self::$sql->prepare('SELECT * FROM `' . Flashii::getConfig('prefix') . $table . '`');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_BOTH);

        return $result;
    }
    
}