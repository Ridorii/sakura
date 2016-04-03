<?php
/**
 * Holds the friends section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\ActiveUser;
use Sakura\Perms\Site;
use Sakura\Template;

/**
 * Friends settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class FriendsController extends Controller
{
    public function listing()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::MANAGE_FRIENDS)) {
            $message = "You aren't allowed to manage friends.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        return Template::render('settings/friends/listing');
    }

    public function requests()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::MANAGE_FRIENDS)) {
            $message = "You aren't allowed to manage friends.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        return Template::render('settings/friends/requests');
    }
}
