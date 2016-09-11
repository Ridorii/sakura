<?php
/**
 * Holds the Route Collection object.
 * @package Sakura
 */

namespace Sakura\Router;

/**
 * A route collection.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Collection
{
    /**
     * Contains the path list associated with the routes.
     * @var array
     */
    private $paths = [];

    /**
     * Contains the names list associated with the routes.
     * @var array
     */
    private $names = [];

    /**
     * Add multiple routes.
     * @param Route $routes
     */
    public function add(Route...$routes)
    {
        foreach ($routes as $route) {
            foreach ($route->methods as $method) {
                $this->paths[$method][$route->path] = $route;
            }

            if ($route->name !== null) {
                $this->names[$route->name] = $route;
            }

            foreach ($route->subroutes as $subroute) {
                $subroute->setPath($route->path . '/' . $subroute->path);

                if ($subroute->controller === null) {
                    $subroute->controller($route->controller);
                }

                $this->add($subroute);
            }

            $route->subroutes = null;
        }
    }

    /**
     * Resolve route by path and method.
     * @param string $method
     * @param string $path
     * @return mixed
     */
    public function resolve($method, $path)
    {
        $path = trim(parse_url($path, PHP_URL_PATH), '/');

        if (!array_key_exists($method, $this->paths)
            || !array_key_exists($path, $this->paths[$method])) {
            throw new Exception;
        }

        return $this->paths[$method][$path]->fire();
    }

    /**
     * Generate a route's url by name.
     * @param string $name
     * @param array $params
     * @return string
     */
    public function url($name, $params = [])
    {
        if (!array_key_exists($name, $this->names)) {
            throw new Exception;
        }

        return parse_url('/' . $this->names[$name]->path, PHP_URL_PATH);
    }
}
