<?php
/**
 * Holds the friends section controller.
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
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
        // Check permission
        if (!CurrentSession::$user->permission(Site::MANAGE_FRIENDS)) {
            throw new HttpMethodNotAllowedException();
        }

        return view('settings/friends/requests');
    }
}
