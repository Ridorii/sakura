<?php
/*
 * Router Wrapper
 */

namespace Sakura;

use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

/**
 * Class Router
 * @package Sakura
 */
class Router
{
    // Router container
    protected static $router = null;

    // Base path (unused for now)
    protected static $basePath = null;

    // Dispatcher
    protected static $dispatcher = null;

    // Request methods
    protected static $methods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'HEAD',
        'ANY'
    ];

    // Add a handler
    public static function __callStatic($name, $args)
    {
        // Check if the method exists
        if (in_array($name = strtoupper($name), self::$methods)) {
            $path = isset($args[2]) && $args !== null ? [$args[0], $args[2]] : $args[0];
            $handler = is_callable($args[1]) || is_array($args[1]) ? $args[1] : explode('@', $args[1]);
            $filter = isset($args[3]) ? $args[3] : [];

            self::$router->addRoute($name, $path, $handler, $filter);
        }
    }

    // Initialisation function
    public static function init($basePath = '/')
    {
        // Set base path
        self::setBasePath($basePath);

        // Create router
        self::$router = new RouteCollector;
    }

    // Set base path
    public static function setBasePath($basePath)
    {
        self::$basePath = $basePath;
    }

    // Parse the url
    private static function parseUrl($url)
    {
        return parse_url($url, PHP_URL_PATH);
    }

    // Handle requests
    public static function handle($method, $url)
    {
        // Check if the dispatcher is defined
        if (self::$dispatcher === null) {
            self::$dispatcher = new Dispatcher(self::$router->getData());
        }

        // Parse url
        $url = self::parseUrl($url);

        // Handle the request
        return self::$dispatcher->dispatch($method, $url);
    }
}
