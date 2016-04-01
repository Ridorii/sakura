<?php
/**
 * Holds the base settings controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\ActiveUser;
use Sakura\Controllers\Controller as BaseController;
use Sakura\Perms\Site;
use Sakura\Router;
use Sakura\Template;
use Sakura\Urls;

/**
 * Base controller (which other controllers should extend on).
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Controller extends BaseController
{
    private $urls;

    public function __construct()
    {
        $this->urls = new Urls();

        $navigation = $this->navigation();

        Template::vars(compact('navigation'));
    }

    public function go($location)
    {
        $location = explode('.', $location);

        $url = $this->urls->format('SETTING_MODE', $location, null, false);

        return header("Location: {$url}");
    }

    public function navigation()
    {
        $nav = [];

        // General
        $nav["General"]["Home"] = Router::route('settings.general.home');
        if (ActiveUser::$user->permission(Site::ALTER_PROFILE)) {
            $nav["General"]["Profile"] = Router::route('settings.general.profile');
        }
        $nav["General"]["Options"] = Router::route('settings.general.options');

        // Friends
        if (ActiveUser::$user->permission(Site::MANAGE_FRIENDS)) {
            $nav["Friends"]["Listing"] = Router::route('settings.friends.listing');
            $nav["Friends"]["Requests"] = Router::route('settings.friends.requests');
        }

        // Groups

        // Notifications
        $nav["Notifications"]["History"] = Router::route('settings.notifications.history');

        // Appearance
        if (ActiveUser::$user->permission(Site::CHANGE_AVATAR)) {
            $nav["Appearance"]["Avatar"] = Router::route('settings.appearance.avatar');
        }
        if (ActiveUser::$user->permission(Site::CHANGE_BACKGROUND)) {
            $nav["Appearance"]["Background"] = Router::route('settings.appearance.background');
        }
        if (ActiveUser::$user->permission(Site::CHANGE_HEADER)) {
            $nav["Appearance"]["Header"] = Router::route('settings.appearance.header');
        }
        if ((
            ActiveUser::$user->page
            && ActiveUser::$user->permission(Site::CHANGE_USERPAGE)
        ) || ActiveUser::$user->permission(Site::CREATE_USERPAGE)) {
            $nav["Appearance"]["Userpage"] = Router::route('settings.appearance.userpage');
        }
        if (ActiveUser::$user->permission(Site::CHANGE_SIGNATURE)) {
            $nav["Appearance"]["Signature"] = Router::route('settings.appearance.signature');
        }

        // Account
        if (ActiveUser::$user->permission(Site::CHANGE_EMAIL)) {
            $nav["Account"]["E-mail address"] = Router::route('settings.account.email');
        }
        if (ActiveUser::$user->permission(Site::CHANGE_USERNAME)) {
            $nav["Account"]["Username"] = Router::route('settings.account.username');
        }
        if (ActiveUser::$user->permission(Site::CHANGE_USERTITLE)) {
            $nav["Account"]["Title"] = Router::route('settings.account.title');
        }
        if (ActiveUser::$user->permission(Site::CHANGE_PASSWORD)) {
            $nav["Account"]["Password"] = Router::route('settings.account.password');
        }
        if (ActiveUser::$user->permission(Site::ALTER_RANKS)) {
            $nav["Account"]["Ranks"] = Router::route('settings.account.ranks');
        }

        // Advanced
        if (ActiveUser::$user->permission(Site::MANAGE_SESSIONS)) {
            $nav["Advanced"]["Sessions"] = Router::route('settings.advanced.sessions');
        }
        if (ActiveUser::$user->permission(Site::DEACTIVATE_ACCOUNT)) {
            $nav["Advanced"]["Deactivate"] = Router::route('settings.advanced.deactivate');
        }

        return $nav;
    }
}
