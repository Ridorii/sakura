<?php
/**
 * Holds the status controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

/**
 * The status page and related stuff.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class StatusController extends Controller
{
    public function index()
    {
        return view('status/index');
    }
}
