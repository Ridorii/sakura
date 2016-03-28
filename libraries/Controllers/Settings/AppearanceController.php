<?php
/**
 * Holds the appearance section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

/**
 * Appearance settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AppearanceController extends Controller
{
    public function avatar()
    {
        return $this->go('appearance.avatar');
    }

    public function background()
    {
        return $this->go('appearance.background');
    }

    public function header()
    {
        return $this->go('appearance.header');
    }

    public function userpage()
    {
        return $this->go('appearance.userpage');
    }

    public function signature()
    {
        return $this->go('appearance.signature');
    }
}
