<?php
/**
 * Holds the general settings section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

/**
 * General settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class GeneralController extends Controller
{
    public function home()
    {
        return $this->go('general.home');
    }

    public function profile()
    {
        return $this->go('general.profile');
    }

    public function options()
    {
        return $this->go('general.options');
    }
}
