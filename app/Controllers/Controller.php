<?php
/**
 * Holds the base controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

/**
 * Base controller (which other controllers should extend on).
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Controller
{
    // Middleware to execute upon creating this class
    protected $middleware = [
        'UpdateLastOnline',
    ];

    // Used to except middleware in controllers that extend this one
    protected $exceptMiddleware = [];

    public function __construct()
    {
        // filter excepted middlewares
        $middlewares = array_diff($this->middleware, $this->exceptMiddleware);

        foreach ($middlewares as $middleware) {
            $className = "Sakura\\Middleware\\{$middleware}";
            (new $className)->run();
        }
    }

    public function json($object)
    {
        header('Content-Type: application/json; charset=utf-8');

        return json_encode(
            $object,
            JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING
        );
    }
}
