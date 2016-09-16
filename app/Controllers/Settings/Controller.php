<?php
/**
 * Holds the base settings controller.
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\Controllers\Controller as BaseController;
use Sakura\CurrentSession;
use Sakura\Perms\Site;
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
        if (CurrentSession::$user->permission(Site::ALTER_PROFILE)) {
            $nav["Account"]["Profile"] = route('settings.account.profile');
        }
        if (CurrentSession::$user->permission(Site::CHANGE_EMAIL)
            || CurrentSession::$user->permission(Site::CHANGE_USERNAME)
            || CurrentSession::$user->permission(Site::CHANGE_USERTITLE)
            || CurrentSession::$user->permission(Site::CHANGE_PASSWORD)) {
            $nav["Account"]["Details"] = route('settings.account.details');
        }
        if (CurrentSession::$user->permission(Site::ALTER_RANKS)) {
            $nav["Account"]["Ranks"] = route('settings.account.ranks');
        }
        if ((
            CurrentSession::$user->page
            && CurrentSession::$user->permission(Site::CHANGE_USERPAGE)
        ) || CurrentSession::$user->permission(Site::CREATE_USERPAGE)) {
            $nav["Account"]["Userpage"] = route('settings.account.userpage');
        }
        if (CurrentSession::$user->permission(Site::CHANGE_SIGNATURE)) {
            $nav["Account"]["Signature"] = route('settings.account.signature');
        }

        // Friends
        if (CurrentSession::$user->permission(Site::MANAGE_FRIENDS)) {
            $nav["Friends"]["Listing"] = route('settings.friends.listing');
            $nav["Friends"]["Requests"] = route('settings.friends.requests');
        }

        // Advanced
        if (CurrentSession::$user->permission(Site::MANAGE_SESSIONS)) {
            $nav["Advanced"]["Sessions"] = route('settings.advanced.sessions');
        }
        if (CurrentSession::$user->permission(Site::DEACTIVATE_ACCOUNT)) {
            $nav["Advanced"]["Deactivate"] = route('settings.advanced.deactivate');
        }

        return $nav;
    }
}
