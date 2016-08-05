<?php
/**
 * Holds the middleware interface.
 * @package Sakura
 */

namespace Sakura\Middleware;

/**
 * Middleware interface.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
interface Middleware
{
    /**
     * Runs the middleware task.
     */
    public function run();
}
