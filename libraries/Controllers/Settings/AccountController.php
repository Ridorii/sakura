<?php
/**
 * Holds the account section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\ActiveUser;
use Sakura\Config;
use Sakura\DB;
use Sakura\Hashing;
use Sakura\Perms\Site;
use Sakura\Router;
use Sakura\Template;

/**
 * Account settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AccountController extends Controller
{
    public function email()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_EMAIL)) {
            $message = "You aren't allowed to change your e-mail address.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $email = $_POST['email'] ?? null;

        if ($session && $email) {
            $redirect = Router::route('settings.account.email');

            // Check if the CSRF session matches
            if ($session !== session_id()) {
                $message = "Your session expired!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Validate e-mail address
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "The e-mail address you supplied is invalid!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Check the MX record of the email
            if (!check_mx_record($email)) {
                $message = 'No valid MX-Record found on the e-mail address you supplied.';
                Template::vars(compact('message', 'redirect'));
                return Template::render('global/information');
            }

            // Check if the e-mail has already been used
            $emailCheck = DB::table('users')
                ->where('email', $email)
                ->count();
            if ($emailCheck) {
                $message = 'Someone already used this e-mail!';
                Template::vars(compact('message', 'redirect'));
                return Template::render('global/information');
            }

            ActiveUser::$user->setMail($email);

            $message = 'Changed your e-mail address!';
            Template::vars(compact('message', 'redirect'));
            return Template::render('global/information');
        }

        return Template::render('settings/account/email');
    }

    public function username()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_USERNAME)) {
            $message = "You aren't allowed to change your username.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $username = $_POST['username'] ?? null;

        if ($session && $username) {
            $redirect = Router::route('settings.account.username');
            $username_clean = clean_string($username, true);

            // Check if the CSRF session matches
            if ($session !== session_id()) {
                $message = "Your session expired!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Check if the username is too short
            if (strlen($username_clean) < Config::get('username_min_length')) {
                $message = "This username is too short!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Check if the username is too long
            if (strlen($username_clean) > Config::get('username_max_length')) {
                $message = "This username is too long!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Check if this username hasn't been used in the last amount of days set in the config
            $getOld = DB::table('username_history')
                ->where('username_old_clean', $username_clean)
                ->where('change_time', '>', (Config::get('old_username_reserve') * 24 * 60 * 60))
                ->orderBy('change_id', 'desc')
                ->get();

            // Check if anything was returned
            if ($getOld && $getOld[0]->user_id != ActiveUser::$user->id) {
                $message = "The username you tried to use is reserved, try again later!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Check if the username is already in use
            $getInUse = DB::table('users')
                ->where('username_clean', $username_clean)
                ->get();

            // Check if anything was returned
            if ($getInUse) {
                $message = "Someone is already using this name!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            ActiveUser::$user->setUsername($username, $username_clean);

            $message = "Changed your username!";
            Template::vars(compact('redirect', 'message'));
            return Template::render('global/information');
        }

        return Template::render('settings/account/username');
    }

    public function title()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_USERTITLE)) {
            $message = "You aren't allowed to change your title.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $title = $_POST['title'] ?? null;

        if ($session && $title !== null) {
            $redirect = Router::route('settings.account.title');

            // Check if the CSRF session matches
            if ($session !== session_id()) {
                $message = "Your session expired!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            if (strlen($title) > 64) {
                $message = "This title is too long!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            if ($title === ActiveUser::$user->title) {
                $message = "This is already your title!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Update database
            DB::table('users')
                ->where('user_id', ActiveUser::$user->id)
                ->update([
                    'user_title' => $title,
                ]);

            $message = "Changed your title!";
            Template::vars(compact('redirect', 'message'));
            return Template::render('global/information');
        }

        return Template::render('settings/account/title');
    }

    public function password()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_PASSWORD)) {
            $message = "You aren't allowed to change your password.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $current = $_POST['current'] ?? null;
        $password = $_POST['password'] ?? null;

        if ($session && $current && $password) {
            $redirect = Router::route('settings.account.password');

            // Check if the CSRF session matches
            if ($session !== session_id()) {
                $message = "Your session expired!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Check current password
            if (!Hashing::validatePassword($current, [
                ActiveUser::$user->passwordAlgo,
                ActiveUser::$user->passwordIter,
                ActiveUser::$user->passwordSalt,
                ActiveUser::$user->passwordHash,
            ])) {
                $message = "Your password was invalid!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Check password entropy
            if (password_entropy($password) < Config::get('min_entropy')) {
                $message = "Your password isn't strong enough!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            ActiveUser::$user->setPassword($password);

            $message = "Changed your password!";
            Template::vars(compact('redirect', 'message'));
            return Template::render('global/information');
        }

        return Template::render('settings/account/password');
    }

    public function ranks()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::ALTER_RANKS)) {
            $message = "You aren't allowed to manage your ranks.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $rank = $_POST['rank'] ?? null;
        $mode = $_POST['mode'] ?? null;

        $locked = [
            Config::get('deactive_rank_id'),
            Config::get('default_rank_id'),
            Config::get('premium_rank_id'),
            Config::get('restricted_rank_id'),
        ];

        if ($session && $rank && $mode) {
            $redirect = Router::route('settings.account.ranks');

            // Check if the CSRF session matches
            if ($session !== session_id()) {
                $message = "Your session expired!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            // Check if user has this rank
            if (!ActiveUser::$user->hasRanks([$rank])) {
                $message = "You aren't a part of this rank!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            if ($mode == 'remove') {
                if (in_array($rank, $locked)) {
                    $message = "You aren't allowed to remove this rank from your account!";
                    Template::vars(compact('redirect', 'message'));
                    return Template::render('global/information');
                }

                ActiveUser::$user->removeRanks([$rank]);

                $message = "Removed the rank from your account!";
                Template::vars(compact('redirect', 'message'));
                return Template::render('global/information');
            }

            ActiveUser::$user->setMainRank($rank);

            $message = "Changed your main rank!";
            Template::vars(compact('redirect', 'message'));
            return Template::render('global/information');
        }

        Template::vars(compact('locked'));

        return Template::render('settings/account/ranks');
    }
}
