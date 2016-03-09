<?php
/**
 * Holds the router class.
 *
 * @package Sakura
 */

namespace Sakura;

use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\RouteCollector;

/**
 * Sakura Wrapper for Phroute.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Router
{
    /**
     * Container for RouteCollector
     *
     * @var RouteCollector
     */
    protected static $router = null;

    /**
     * Base path of the router.
     *
     * @var string
     */
    protected static $basePath = null;

    /**
     * Container for the Dispatcher
     *
     * @var Dispatcher
     */
    protected static $dispatcher = null;

    /**
     * Collection of handled HTTP request types.
     *
     * @var array
     */
    protected static $methods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'HEAD',
        'OPTIONS',
        'ANY',
    ];

    /**
     * Method aliases for adding routes.
     *
     * @param string $name A HTTP method.
     * @param array $args The arguments.
     */
    public static function __callStatic($name, $args)
    {
        // Check if the method exists
        if (in_array($name = strtoupper($name), self::$methods)) {
            $path = isset($args[2]) && $args !== null ? [$args[0], $args[2]] : $args[0];
            $handler = is_callable($args[1]) || is_array($args[1]) ? $args[1] : explode('@', ('Sakura\Controllers\\' . $args[1]));
            $filter = isset($args[3]) ? $args[3] : [];

            self::$router->addRoute($name, $path, $handler, $filter);
        }
    }

    /**
     * Initialisation.
     *
     * @param string $basePath The base path of the router.
     */
    public static function init($basePath = '/')
    {
        // Set base path
        self::setBasePath($basePath);

        // Create router
        self::$router = new RouteCollector;
    }

    /**
     * Set the base path.
     *
     * @param string $basePath The base path of the router.
     */
    public static function setBasePath($basePath)
    {
        self::$basePath = $basePath;
    }

    /**
     * Parse a URL.
     *
     * @param string $url The URL that is to be parsed.
     *
     * @return string THe parsed URL.
     */
    private static function parseUrl($url)
    {
        return parse_url($url, PHP_URL_PATH);
    }

    /**
     * Generate the URI of a route using names.
     *
     * @param string $name The identifier of the route.
     * @param string|array $args The route arguments.
     *
     * @return string The generated URI.
     */
    public static function route($name, $args = null)
    {
        // Array-ify the arguments
        if ($args !== null && !is_array($args)) {
            $temp = $args;
            $args = [];
            $args[] = $temp;
        }

        return self::$basePath . self::$router->route($name, $args);
    }

    /**
     * Create group.
     *
     * @param array $filters The filters for this group.
     * @param \Closure $callback The containers
     *
     * @return string The generated URI.
     */
    public static function group($filters, $callback)
    {
        // Execute the inner function
        self::$router->group($filters, $callback);
    }

    /**
     * Handle requests.
     *
     * @param string $method The HTTP method used to make the request.
     * @param string $url The URL the request is made to.
     *
     * @return mixed The response.
     */
    public static function handle($method, $url)
    {
        // Check if the dispatcher is defined
        if (self::$dispatcher === null) {
            self::$dispatcher = new Dispatcher(self::$router->getData());
        }

        // Parse url
        $url = self::parseUrl($url);

        // Handle the request
        try {
            return self::$dispatcher->dispatch($method, $url);
        } catch (HttpMethodNotAllowedException $e) {
        } catch (HttpRouteNotFoundException $e) {
        }

        // Default to the not found page
        return Template::render('global/notfound');
    }
}
