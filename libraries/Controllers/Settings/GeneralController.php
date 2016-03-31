<?php
/**
 * Holds the general settings section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\ActiveUser;
use Sakura\Perms\Site;
use Sakura\Template;

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
        ActiveUser::class;
        Site::class;

        $navigation = $this->navigation();

        Template::vars(compact('navigation'));

        return Template::render('settings/general/home');
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
