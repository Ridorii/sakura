<?php
/**
 * Holds the base settings controller.
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\Controllers\Controller as BaseController;
use Sakura\CurrentSession;
use Sakura\Template;

/**
 * Base controller (which other controllers should extend on).
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Controller extends BaseController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        Template::vars(['navigation' => $this->navigation()]);
    }

    /**
     * Generates the navigation.
     * @return array
     */
    public function navigation()
    {
        $nav = [];

        // Account
        if (CurrentSession::$user->perms->changeProfile) {
            $nav["Account"]["Profile"] = route('settings.account.profile');
        }
        $nav["Account"]["Details"] = route('settings.account.details');
        if (CurrentSession::$user->perms->manageRanks) {
            $nav["Account"]["Ranks"] = route('settings.account.ranks');
        }
        if (CurrentSession::$user->perms->changeUserpage) {
            $nav["Account"]["Userpage"] = route('settings.account.userpage');
        }
        if (CurrentSession::$user->perms->changeSignature) {
            $nav["Account"]["Signature"] = route('settings.account.signature');
        }

        // Friends
        if (CurrentSession::$user->perms->manageFriends) {
            $nav["Friends"]["Listing"] = route('settings.friends.listing');
            $nav["Friends"]["Requests"] = route('settings.friends.requests');
        }

        // Advanced
        $nav["Advanced"]["Sessions"] = route('settings.advanced.sessions');
        if (CurrentSession::$user->perms->deactivateAccount) {
            $nav["Advanced"]["Deactivate"] = route('settings.advanced.deactivate');
        }

        return $nav;
    }
}
