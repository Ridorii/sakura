<?php
/**
 * Holds the a static referencer to Collection.
 * @package Sakura
 */

namespace Sakura\Router;

/**
 * Collection except static.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Router
{
    /**
     * Contains the collection.
     * @var Collection
     */
    protected static $instance = null;

    /**
     * Does the referencing.
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        if (static::$instance === null) {
            static::$instance = new Collection;
        }

        return static::$instance->$method(...$params);
    }
}
