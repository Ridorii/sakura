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
    public function json($object)
    {
        header('Content-Type: application/json; charset=utf-8');

        return json_encode(
            $object,
            JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING
        );
    }
}
