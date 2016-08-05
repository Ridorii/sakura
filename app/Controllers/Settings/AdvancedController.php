<?php
/**
 * Holds the advanced section controller.
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\ActiveUser;
use Sakura\DB;
use Sakura\Perms\Site;

/**
 * Advanced settings.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AdvancedController extends Controller
{
    /**
     * Renders the session management page.
     * @return string
     */
    public function sessions()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::MANAGE_SESSIONS)) {
            $message = "You aren't allowed to manage sessions.";
            $redirect = route('settings.index');
            return view('global/information', compact('message', 'redirect'));
        }

        $id = $_POST['id'] ?? null;
        $all = isset($_POST['all']);

        if (session_check() && ($id || $all)) {
            $redirect = route('settings.advanced.sessions');

            // End all sessions
            if ($all) {
                DB::table('sessions')
                    ->where('user_id', ActiveUser::$user->id)
                    ->delete();

                $message = "Deleted all active session associated with your account!";
                return view('global/information', compact('message', 'redirect'));
            }

            // Create the session statement
            $session = DB::table('sessions')
                ->where('user_id', ActiveUser::$user->id)
                ->where('session_id', $id);

            // Check if the session exists
            if (!$session->count()) {
                $message = "This session doesn't exist!";
                return view('global/information', compact('message', 'redirect'));
            }

            // Delete it
            $session->delete();

            $message = "Deleted the session!";
            return view('global/information', compact('message', 'redirect'));
        }

        $sessions = DB::table('sessions')
            ->where('user_id', ActiveUser::$user->id)
            ->get();
        $active = ActiveUser::$session->sessionId;

        return view('settings/advanced/sessions', compact('sessions', 'active'));
    }

    /**
     * Renders the deactivation page.
     * @return string
     */
    public function deactivate()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::DEACTIVATE_ACCOUNT)) {
            $message = "You aren't allowed to deactivate your account.";
            return view('global/information', compact('message', 'redirect'));
        }

        $password = $_POST['password'] ?? null;

        if (session_check() && $password) {
            $redirect = route('settings.advanced.deactivate');

            // Check password
            if (!ActiveUser::$user->verifyPassword($password)) {
                $message = "Your password was invalid!";
                return view('global/information', compact('message', 'redirect'));
            }

            // Deactivate account
            ActiveUser::$user->removeRanks(array_keys(ActiveUser::$user->ranks));
            ActiveUser::$user->addRanks([1]);
            ActiveUser::$user->setMainRank(1);

            // Destroy all active sessions
            ActiveUser::$session->destroyAll();

            $redirect = route('main.index');
            $message = "Farewell!";
            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/advanced/deactivate');
    }
}
