<?php
/**
 * Holds the base controller for manage.
 * @package Sakura
 */

namespace Sakura\Controllers\Manage;

use Sakura\Controllers\Controller as BaseController;
use Sakura\Template;

/**
 * Base management controller (which other controllers should extend on).
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Controller extends BaseController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $navigation = $this->navigation();
        Template::vars(compact('navigation'));
    }

    /**
     * Generates the navigation.
     * @return array
     */
    public function navigation()
    {
        $nav = [];

        // Overview
        $nav["Overview"]["Index"] = route('manage.overview.index');

        return $nav;
    }
}
