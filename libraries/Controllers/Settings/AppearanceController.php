<?php
/**
 * Holds the appearance section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\ActiveUser;
use Sakura\DB;
use Sakura\Perms\Site;
use Sakura\Router;
use Sakura\Template;

/**
 * Appearance settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AppearanceController extends Controller
{
    public function avatar()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_AVATAR)) {
            $message = "You aren't allowed to change your avatar.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        return $this->go('appearance.avatar');
    }

    public function background()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_BACKGROUND)) {
            $message = "You aren't allowed to change your background.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        return $this->go('appearance.background');
    }

    public function header()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_HEADER)) {
            $message = "You aren't allowed to change your profile header.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $header = $_POST['header'] ?? null;

        return Template::render('settings/appearance/header');
    }

    public function userpage()
    {
        // Check permission
        if (!(
            ActiveUser::$user->page
            && ActiveUser::$user->permission(Site::CHANGE_USERPAGE)
        ) || !ActiveUser::$user->permission(Site::CREATE_USERPAGE)) {
            $message = "You aren't allowed to change your userpage.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $userpage = $_POST['userpage'] ?? null;

        $maxLength = 65535;

        if ($session && $userpage) {
            $redirect = Router::route('settings.appearance.userpage');

            if ($session !== session_id()) {
                $message = 'Your session expired!';
                Template::vars(compact('message', 'redirect'));
                return Template::render('global/information');
            }

            if (strlen($userpage) > $maxLength) {
                $message = 'Your userpage is too long, shorten it a little!';
                Template::vars(compact('message', 'redirect'));
                return Template::render('global/information');
            }

            // Update database
            DB::table('users')
                ->where('user_id', ActiveUser::$user->id)
                ->update([
                    'user_page' => $userpage,
                ]);

            $message = 'Updated your userpage!';
            Template::vars(compact('message', 'redirect'));
            return Template::render('global/information');
        }

        Template::vars(compact('maxLength'));

        return Template::render('settings/appearance/userpage');
    }

    public function signature()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_SIGNATURE)) {
            $message = "You aren't allowed to change your signature.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $signature = $_POST['signature'] ?? null;

        $maxLength = 500;

        if ($session && $signature) {
            $redirect = Router::route('settings.appearance.signature');

            if ($session !== session_id()) {
                $message = 'Your session expired!';
                Template::vars(compact('message', 'redirect'));
                return Template::render('global/information');
            }

            if (strlen($signature) > $maxLength) {
                $message = 'Your signature is too long, shorten it a little!';
                Template::vars(compact('message', 'redirect'));
                return Template::render('global/information');
            }

            // Update database
            DB::table('users')
                ->where('user_id', ActiveUser::$user->id)
                ->update([
                    'user_signature' => $signature,
                ]);

            $message = 'Updated your signature!';
            Template::vars(compact('message', 'redirect'));
            return Template::render('global/information');
        }

        Template::vars(compact('maxLength'));

        return Template::render('settings/appearance/signature');
    }
}
