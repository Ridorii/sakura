<?php
/**
 * Holds the advanced section controller.
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
 * Advanced settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AdvancedController extends Controller
{
    public function sessions()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::MANAGE_SESSIONS)) {
            $message = "You aren't allowed to manage sessions.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $id = $_POST['id'] ?? null;
        $all = isset($_POST['all']);

        if ($session && ($id || $all)) {
            $redirect = Router::route('settings.advanced.sessions');

            // Check if the CSRF session matches
            if ($session !== session_id()) {
                $message = "Your session expired, not the one you were intending to let expire though!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // End all sessions
            if ($all) {
                DB::table('sessions')
                    ->where('user_id', ActiveUser::$user->id)
                    ->delete();

                $message = "Deleted all active session associated with your account!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Create the session statement
            $session = DB::table('sessions')
                ->where('user_id', ActiveUser::$user->id)
                ->where('session_id', $id);

            // Check if the session exists
            if (!$session->count()) {
                $message = "This session doesn't exist!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Delete it
            $session->delete();

            $message = "Deleted the session!";
            Template::vars(compact('redirect', 'message'));
            return Template::render('global/information');
        }

        $sessions = DB::table('sessions')
            ->where('user_id', ActiveUser::$user->id)
            ->get();
        $active = ActiveUser::$session->sessionId;

        Template::vars(compact('sessions', 'active'));

        return Template::render('settings/advanced/sessions');
    }

    public function deactivate()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::DEACTIVATE_ACCOUNT)) {
            $message = "You aren't allowed to deactivate your account.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $password = $_POST['password'] ?? null;

        if ($session && $password) {
            $redirect = Router::route('settings.advanced.deactivate');

            // Verify session
            if ($session !== session_id()) {
                $message = "Session verification failed!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Check password
            if (!ActiveUser::$user->verifyPassword($password)) {
                $message = "Your password was invalid!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Deactivate account
            ActiveUser::$user->removeRanks(array_keys(ActiveUser::$user->ranks));
            ActiveUser::$user->addRanks([1]);
            ActiveUser::$user->setMainRank(1);

            // Destroy all active sessions
            ActiveUser::$session->destroyAll();

            $redirect = Router::route('main.index');
            $message = "Farewell!";
            Template::vars(compact('redirect', 'message'));
            return Template::render('global/information');
        }

        return Template::render('settings/advanced/deactivate');
    }
}
