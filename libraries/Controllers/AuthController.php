<?php
/**
 * Holds the auth controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\DB;
use Sakura\Hashing;
use Sakura\Net;
use Sakura\Perms\Site;
use Sakura\Router;
use Sakura\Session;
use Sakura\Template;
use Sakura\User;
use Sakura\Users;
use Sakura\Utils;

/**
 * Authentication controllers.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AuthController extends Controller
{
    protected function touchRateLimit($user, $mode = 0)
    {
        DB::table('login_attempts')
            ->insert([
                'attempt_success' => $mode,
                'attempt_timestamp' => time(),
                'attempt_ip' => Net::pton(Net::IP()),
                'user_id' => $user,
            ]);
    }

    public function logout()
    {
        // Check if user is logged in
        $check = Users::checkLogin();

        if (!$check || !isset($_REQUEST['s']) || $_REQUEST['s'] != session_id()) {
            $message = 'Something happened! This probably happened because you went here without being logged in.';
            $redirect = (isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : Router::route('main.index'));

            Template::vars(['page' => ['success' => 0, 'redirect' => $redirect, 'message' => $message]]);

            return Template::render('global/information');
        }

        // Destroy the active session
        (new Session($check[0], $check[1]))->destroy();

        // Return true indicating a successful logout
        $message = 'Goodbye!';
        $redirect = Router::route('auth.login');

        Template::vars(['page' => ['success' => 1, 'redirect' => $redirect, 'message' => $message]]);

        return Template::render('global/information');
    }

    public function loginGet()
    {
        return Template::render('main/login');
    }

    public function loginPost()
    {
        // Preliminarily set login to failed
        $success = 0;
        $redirect = Router::route('auth.login');

        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            $message = 'Logging in is disabled for security checkups! Try again later.';
            Template::vars(['page' => ['success' => $success, 'redirect' => $redirect, 'message' => $message]]);
            return Template::render('global/information');
        }

        // Get request variables
        $username = isset($_REQUEST['username']) ? $_REQUEST['username'] : null;
        $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : null;
        $remember = isset($_REQUEST['remember']);

        // Check if we haven't hit the rate limit
        $rates = DB::table('login_attempts')
            ->where('attempt_ip', Net::pton(Net::IP()))
            ->where('attempt_timestamp', '>', time() - 1800)
            ->where('attempt_success', '0')
            ->count();

        if ($rates > 4) {
            $message = 'Your have hit the login rate limit, try again later.';
            Template::vars(['page' => ['success' => $success, 'redirect' => $redirect, 'message' => $message]]);
            return Template::render('global/information');
        }

        // Get account data
        $user = User::construct(Utils::cleanString($username, true, true));

        // Check if the user that's trying to log in actually exists
        if ($user->id === 0) {
            $this->touchRateLimit($user->id);
            $message = 'The user you tried to log into does not exist.';
            Template::vars(['page' => ['success' => $success, 'redirect' => $redirect, 'message' => $message]]);
            return Template::render('global/information');
        }

        // Validate password
        switch ($user->passwordAlgo) {
            // Disabled
            case 'disabled':
                $this->touchRateLimit($user->id);
                $message = 'Logging into this account is disabled.';
                Template::vars(['page' => ['success' => $success, 'redirect' => $redirect, 'message' => $message]]);
                return Template::render('global/information');

            // Default hashing method
            default:
                if (!Hashing::validatePassword($password, [
                    $user->passwordAlgo,
                    $user->passwordIter,
                    $user->passwordSalt,
                    $user->passwordHash,
                ])) {
                    $this->touchRateLimit($user->id);
                    $message = 'The password you entered was invalid.';
                    Template::vars(['page' => ['success' => $success, 'redirect' => $redirect, 'message' => $message]]);
                    return Template::render('global/information');
                }
        }

        // Check if the user has the required privs to log in
        if ($user->permission(Site::DEACTIVATED)) {
            $this->touchRateLimit($user->id);
            $message = 'Your account does not have the required permissions to log in.';
            Template::vars(['page' => ['success' => $success, 'redirect' => $redirect, 'message' => $message]]);
            return Template::render('global/information');
        }

        // Create a new session
        $session = new Session($user->id);

        // Generate a session key
        $sessionKey = $session->create($remember);

        // User ID cookie
        setcookie(
            Config::get('cookie_prefix') . 'id',
            $user->id,
            time() + 604800,
            Config::get('cookie_path')
        );

        // Session ID cookie
        setcookie(
            Config::get('cookie_prefix') . 'session',
            $sessionKey,
            time() + 604800,
            Config::get('cookie_path')
        );

        $this->touchRateLimit($user->id, 1);

        $success = 1;
        $redirect = $user->lastOnline ? (isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : Router::route('main.index')) : Router::route('main.infopage', 'welcome');
        $message = 'Welcome' . ($user->lastOnline ? ' back' : '') . '!';

        Template::vars(['page' => ['success' => $success, 'redirect' => $redirect, 'message' => $message]]);

        return Template::render('global/information');
    }
}
