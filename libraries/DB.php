<?php
/*
 * Database wrapper (v2)
 */

namespace Sakura;

use PDO;
use PDOException;

/**
 * Class DB
 * @package Sakura
 */
class DB
{
    // Database container
    private static $pdo;

    // Initialisation function
    public static function init($wrapper)
    {
    }
}
