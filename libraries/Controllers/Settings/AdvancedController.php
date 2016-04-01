<?php
/**
 * Holds the advanced section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\ActiveUser;
use Sakura\DB;
use Sakura\Template;

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
        $sessions = DB::table('sessions')
            ->where('user_id', ActiveUser::$user->id)
            ->get();

        Template::vars(compact('sessions'));

        return Template::render('settings/advanced/sessions');
    }

    public function deactivate()
    {
        return $this->go('advanced.deactivate');
    }
}
