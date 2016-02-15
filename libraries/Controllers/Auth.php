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
class Auth extends Controller
{
    public function login()
    {
        return Template::render('main/login');
    }
}
