<?php
/**
 * Enables CORS globally.
 * @package Sakura
 */

namespace Sakura\Middleware;

/**
 * Enables CORS.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class EnableCORS implements MiddlewareInterface
{
    /**
     * Enables CORS.
     */
    public function run()
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
            header("Access-Control-Allow-Methods: GET, POST");
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }
        }
    }
}
