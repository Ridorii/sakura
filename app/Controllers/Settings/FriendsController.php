<?php
/**
 * Holds the friends section controller.
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\CurrentSession;
use Sakura\Perms\Site;

/**
 * Friends settings.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class FriendsController extends Controller
{
    /**
     * Gets friends listing
     * @return string
     */
    public function listing()
    {
        // Check permission
        if (!CurrentSession::$user->permission(Site::MANAGE_FRIENDS)) {
            $message = "You aren't allowed to manage friends.";
            $redirect = route('settings.index');
            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/friends/listing');
    }

    /**
     * Gets friend requests listing
     * @return string
     */
    public function requests()
    {
        // Check permission
        if (!CurrentSession::$user->permission(Site::MANAGE_FRIENDS)) {
            $message = "You aren't allowed to manage friends.";
            $redirect = route('settings.index');
            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/friends/requests');
    }
}
