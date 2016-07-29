<?php
/**
 * Holds the alias class for the Illuminate database thing.
 *
 * @package Sakura
 */

namespace Sakura;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;

/**
 * The Illuminate (Laravel) database wrapper.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class DB extends Manager
{
    public static function getMigrationRepository()
    {
        $resolver = new ConnectionResolver(['database' => self::connection()]);
        $repository = new DatabaseMigrationRepository($resolver, 'migrations');
        $repository->setSource('database');
        return $repository;
    }

    public static function getSchemaBuilder()
    {
        return self::connection()->getSchemaBuilder();
    }
}
