<?php
/**
 * Holds the appearance section controller.
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Sakura\CurrentSession;
use Sakura\DB;
use Sakura\Perms\Site;

/**
 * Appearance settings.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AppearanceController extends Controller
{
    /**
     * Renders the userpage editing page.
     */
    public function userpage()
    {
        // Check permission
        if (!(
            CurrentSession::$user->page
            && CurrentSession::$user->permission(Site::CHANGE_USERPAGE)
        ) && !CurrentSession::$user->permission(Site::CREATE_USERPAGE)) {
            throw new HttpMethodNotAllowedException();
        }

        $userpage = $_POST['userpage'] ?? null;
        $maxLength = config('user.page_max');

        if (session_check() && $userpage) {
            $redirect = route('settings.appearance.userpage');

            if (strlen($userpage) > $maxLength) {
                $message = 'Your userpage is too long, shorten it a little!';
            } else {
                DB::table('users')
                    ->where('user_id', CurrentSession::$user->id)
                    ->update([
                        'user_page' => $userpage,
                    ]);

                $message = 'Updated your userpage!';
            }

            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/appearance/userpage', compact('maxLength'));
    }

    /**
     * Renders the signature changing page.
     * @return string
     */
    public function signature()
    {
        // Check permission
        if (!CurrentSession::$user->permission(Site::CHANGE_SIGNATURE)) {
            throw new HttpMethodNotAllowedException();
        }

        $signature = $_POST['signature'] ?? null;
        $maxLength = config('user.signature_max');

        if (session_check() && $signature) {
            $redirect = route('settings.appearance.signature');

            if (strlen($signature) > $maxLength) {
                $message = 'Your signature is too long, shorten it a little!';
            } else {
                DB::table('users')
                    ->where('user_id', CurrentSession::$user->id)
                    ->update([
                        'user_signature' => $signature,
                    ]);

                $message = 'Updated your signature!';
            }

            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/appearance/signature', compact('maxLength'));
    }
}
