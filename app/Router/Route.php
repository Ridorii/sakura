<?php
/**
 * Holds the Route object.
 * @package Sakura
 */

namespace Sakura\Router;

use Sakura\Exceptions\RouterInvalidMethodException;
use Sakura\Exceptions\RouterNonExistentControllerException;

/**
 * A route.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Route
{
    /**
     * Collection of handled HTTP request types.
     */
    const METHODS = [
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
     * Method this route is intended for.
     * @var array
     */
    public $methods = [];

    /**
     * Path of this route.
     * @var string
     */
    public $path;

    /**
     * Controller class for this route.
     * @var string
     */
    public $controller;

    /**
     * Controller method for this route.
     * @var string|Callable
     */
    public $action;

    /**
     * Name for this route.
     * @var string
     */
    public $name;

    /**
     * Subroutes
     * @var array
     */
    public $subroutes = [];

    /**
     * Construct an instance and set path.
     * @param string $path
     * @return $this
     */
    public static function path($path)
    {
        $instance = new static;
        $instance->setPath($path);
        return $instance;
    }

    /**
     * Set path.
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = trim(parse_url($path, PHP_URL_PATH), '/');
        return $this;
    }

    /**
     * Define subroutes.
     * @param Route $routes
     * @return $this
     */
    public function group(Route...$routes)
    {
        foreach ($routes as $route) {
            if ($route->controller === null) {
                $route->controller($this->controller);
            }

            $this->subroutes[] = $route;
        }

        return $this;
    }

    /**
     * Set accepted methods.
     * @param string|array $methods
     * @throws RouterInvalidMethodException
     * @return $this
     */
    public function methods($methods)
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }

        foreach ($methods as $method) {
            if (!in_array($method, static::METHODS)) {
                throw new RouterInvalidMethodException;
            }

            if (!in_array($method, $this->methods)) {
                $this->methods[] = $method;
            }
        }

        return $this;
    }

    /**
     * Set controller class.
     * @param string $class
     * @return $this
     */
    public function controller($class)
    {
        $this->controller = $class;
        return $this;
    }

    /**
     * Set action.
     * @param string|Callable $action
     * @return $this
     */
    public function action($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Set name.
     * @param string $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Call the controller.
     * @throws RouterNonExistentControllerException
     * @return mixed
     */
    public function fire($params = [])
    {
        if (is_callable($this->action)) {
            return call_user_func_array($this->action, $params);
        }

        if (!class_exists($this->controller)
            || !method_exists($this->controller, $this->action)) {
            throw new RouterNonExistentControllerException;
        }

        return (new $this->controller)->{$this->action}(...$params);
    }
}
