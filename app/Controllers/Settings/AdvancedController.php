<?php
/**
 * Holds the advanced section controller.
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\CurrentSession;
use Sakura\Perms\Site;
use Sakura\Session;

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
        if (!CurrentSession::$user->permission(Site::MANAGE_SESSIONS)) {
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
                CurrentSession::$user->purgeSessions();
                $message = "Deleted all active session associated with your account!";
                return view('global/information', compact('message', 'redirect'));
            }

            // Create the session statement
            $session = new Session($id);

            // Check if the session exists
            if ($session->id < 1 || $session->user !== CurrentSession::$user->id) {
                $message = "This session doesn't exist!";
                return view('global/information', compact('message', 'redirect'));
            }

            // Delete it
            $session->delete();

            header("Location: {$redirect}");
            return;
        }

        $sessions = CurrentSession::$user->sessions();
        $active = CurrentSession::$session->id;

        return view('settings/advanced/sessions', compact('sessions', 'active'));
    }

    /**
     * Renders the deactivation page.
     * @return string
     */
    public function deactivate()
    {
        // Check permission
        if (!CurrentSession::$user->permission(Site::DEACTIVATE_ACCOUNT)) {
            $message = "You aren't allowed to deactivate your account.";
            return view('global/information', compact('message', 'redirect'));
        }

        $password = $_POST['password'] ?? null;

        if (session_check() && $password) {
            $redirect = route('settings.advanced.deactivate');

            // Check password
            if (!CurrentSession::$user->verifyPassword($password)) {
                $message = "Your password was invalid!";
                return view('global/information', compact('message', 'redirect'));
            }

            // Deactivate account
            CurrentSession::$user->removeRanks(array_keys(CurrentSession::$user->ranks));
            CurrentSession::$user->addRanks([1]);
            CurrentSession::$user->setMainRank(1);

            // Destroy all active sessions
            CurrentSession::$user->purgeSessions();

            $redirect = route('main.index');
            $message = "Farewell!";
            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/advanced/deactivate');
    }
}
