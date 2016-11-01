<?php
/**
 * Holds the friends section controller.
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Sakura\CurrentSession;

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
        if (!CurrentSession::$user->perms->manageFriends) {
            throw new HttpMethodNotAllowedException();
        }

        return view('settings/friends/listing');
    }

    /**
     * Gets friend requests listing
     * @return string
     */
    public function requests()
    {
        if (!CurrentSession::$user->perms->manageFriends) {
            throw new HttpMethodNotAllowedException();
        }

        return view('settings/friends/requests');
    }
}
