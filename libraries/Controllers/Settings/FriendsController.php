<?php
/**
 * Holds the friends section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

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
        return $this->go('friends.listing');
    }

    public function requests()
    {
        return $this->go('friends.requests');
    }
}
