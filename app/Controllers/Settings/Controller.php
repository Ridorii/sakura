<?php
/**
 * Holds the base settings controller.
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\Controllers\Controller as BaseController;
use Sakura\CurrentSession;
use Sakura\Perms\Site;
use Sakura\Router;
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
            $nav["Account"]["Profile"] = Router::route('settings.account.profile');
        }
        if (CurrentSession::$user->permission(Site::CHANGE_EMAIL)) {
            $nav["Account"]["E-mail address"] = Router::route('settings.account.email');
        }
        if (CurrentSession::$user->permission(Site::CHANGE_USERNAME)) {
            $nav["Account"]["Username"] = Router::route('settings.account.username');
        }
        if (CurrentSession::$user->permission(Site::CHANGE_USERTITLE)) {
            $nav["Account"]["Title"] = Router::route('settings.account.title');
        }
        if (CurrentSession::$user->permission(Site::CHANGE_PASSWORD)) {
            $nav["Account"]["Password"] = Router::route('settings.account.password');
        }
        if (CurrentSession::$user->permission(Site::ALTER_RANKS)) {
            $nav["Account"]["Ranks"] = Router::route('settings.account.ranks');
        }

        // Friends
        if (CurrentSession::$user->permission(Site::MANAGE_FRIENDS)) {
            $nav["Friends"]["Listing"] = Router::route('settings.friends.listing');
            $nav["Friends"]["Requests"] = Router::route('settings.friends.requests');
        }

        // Notifications
        $nav["Notifications"]["History"] = Router::route('settings.notifications.history');

        // Appearance
        if (CurrentSession::$user->permission(Site::CHANGE_AVATAR)) {
            $nav["Appearance"]["Avatar"] = Router::route('settings.appearance.avatar');
        }
        if (CurrentSession::$user->permission(Site::CHANGE_BACKGROUND)) {
            $nav["Appearance"]["Background"] = Router::route('settings.appearance.background');
        }
        if (CurrentSession::$user->permission(Site::CHANGE_HEADER)) {
            $nav["Appearance"]["Header"] = Router::route('settings.appearance.header');
        }
        if ((
            CurrentSession::$user->page
            && CurrentSession::$user->permission(Site::CHANGE_USERPAGE)
        ) || CurrentSession::$user->permission(Site::CREATE_USERPAGE)) {
            $nav["Appearance"]["Userpage"] = Router::route('settings.appearance.userpage');
        }
        if (CurrentSession::$user->permission(Site::CHANGE_SIGNATURE)) {
            $nav["Appearance"]["Signature"] = Router::route('settings.appearance.signature');
        }

        // Advanced
        if (CurrentSession::$user->permission(Site::MANAGE_SESSIONS)) {
            $nav["Advanced"]["Sessions"] = Router::route('settings.advanced.sessions');
        }
        if (CurrentSession::$user->permission(Site::DEACTIVATE_ACCOUNT)) {
            $nav["Advanced"]["Deactivate"] = Router::route('settings.advanced.deactivate');
        }

        return $nav;
    }
}
