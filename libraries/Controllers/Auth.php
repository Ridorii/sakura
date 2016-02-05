<?php
/**
 * Holds the auth controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Template;

/**
 * Authentication controllers.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Auth
{
    public static function login()
    {
        return Template::render('main/login');
    }
}
