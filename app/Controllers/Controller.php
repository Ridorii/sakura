<?php
/**
 * Holds the base controller.
 * @package Sakura
 */

namespace Sakura\Controllers;

/**
 * Base controller (which other controllers should extend on).
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Controller
{
    /**
     * Middleware to execute upon creating this class.
     * @var array
     */
    protected $middleware = [
        'UpdateLastOnline',
    ];

    /**
     * Used to except middleware in controllers that extend this one.
     * @var array
     */
    protected $exceptMiddleware = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        // filter excepted middlewares
        $middlewares = array_diff($this->middleware, $this->exceptMiddleware);

        foreach ($middlewares as $middleware) {
            $className = "Sakura\\Middleware\\{$middleware}";
            (new $className)->run();
        }
    }

    /**
     * Encodes json.
     * @param array|\stdObject $object
     * @param int $operators
     * @return string
     */
    public function json($object, $operators = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($object, $operators);
    }
}
