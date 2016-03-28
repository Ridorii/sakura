<?php
/**
 * Holds the advanced section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

/**
 * Advanced settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AdvancedController extends Controller
{
    public function sessions()
    {
        return $this->go('advanced.sessions');
    }

    public function deactivate()
    {
        return $this->go('advanced.deactivate');
    }
}
