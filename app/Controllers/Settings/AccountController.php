<?php
/**
 * Holds the account section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\ActiveUser;
use Sakura\DB;
use Sakura\Perms\Site;

/**
 * Account settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AccountController extends Controller
{
    public function profile()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::ALTER_PROFILE)) {
            $message = "You aren't allowed to edit your profile!";
            $redirect = route('settings.index');
            return view('global/information', compact('message', 'redirect'));
        }

        if (session_check()) {
            $redirect = route('settings.account.profile');
            $save = [];
            $allowed = [
                'website',
                'twitter',
                'github',
                'skype',
                'discord',
                'youtube',
                'steam',
                'osu',
                'lastfm',
            ];

            foreach ($allowed as $field) {
                $save["user_{$field}"] = $_POST["profile_{$field}"] ?? null;
            }

            DB::table('users')
                ->where('user_id', ActiveUser::$user->id)
                ->update($save);

            // Birthdays
            if (isset($_POST['birthday_day'], $_POST['birthday_month'], $_POST['birthday_year'])) {
                $day = intval($_POST['birthday_day']);
                $month = intval($_POST['birthday_month']);
                $year = intval($_POST['birthday_year']);

                if (!$day && !$month && !$year) {
                    $birthdate = null;
                } else {
                    if (!checkdate($month, $day, $year ? $year : 1)
                        || $year > date("Y")
                        || ($year != 0 && $year < (date("Y") - 100))) {
                        $message = "Your birthdate was invalid, everything else was saved though!";

                        return view('global/information', compact('message', 'redirect'));
                    }

                    // Combine it into a YYYY-MM-DD format
                    $birthdate = implode('-', compact('year', 'month', 'day'));
                }

                DB::table('users')
                    ->where('user_id', ActiveUser::$user->id)
                    ->update([
                        'user_birthday' => $birthdate,
                    ]);
            }

            $message = "Updated your profile!";

            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/account/profile');
    }

    public function email()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_EMAIL)) {
            $message = "You aren't allowed to change your e-mail address.";
            $redirect = route('settings.index');
            return view('global/information', compact('message', 'redirect'));
        }

        $email = $_POST['email'] ?? null;

        if (session_check() && $email) {
            $redirect = route('settings.account.email');

            // Validate e-mail address
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "The e-mail address you supplied is invalid!";
                return view('global/information', compact('redirect', 'message'));
            }

            // Check the MX record of the email
            if (!check_mx_record($email)) {
                $message = 'No valid MX-Record found on the e-mail address you supplied.';
                return view('global/information', compact('redirect', 'message'));
            }

            // Check if the e-mail has already been used
            $emailCheck = DB::table('users')
                ->where('email', $email)
                ->count();
            if ($emailCheck) {
                $message = 'Someone already used this e-mail!';
                return view('global/information', compact('redirect', 'message'));
            }

            ActiveUser::$user->setMail($email);

            $message = 'Changed your e-mail address!';
            return view('global/information', compact('redirect', 'message'));
        }

        return view('settings/account/email');
    }

    public function username()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_USERNAME)) {
            $message = "You aren't allowed to change your username.";
            $redirect = route('settings.index');
            return view('global/information', compact('redirect', 'message'));
        }

        $username = $_POST['username'] ?? null;

        if (session_check() && $username) {
            $redirect = route('settings.account.username');
            $username_clean = clean_string($username, true);

            // Check if the username is too short
            if (strlen($username_clean) < config('user.name_min')) {
                $message = "This username is too short!";
                return view('global/information', compact('redirect', 'message'));
            }

            // Check if the username is too long
            if (strlen($username_clean) > config('user.name_max')) {
                $message = "This username is too long!";
                return view('global/information', compact('redirect', 'message'));
            }

            // Check if this username hasn't been used in the last amount of days set in the config
            $getOld = DB::table('username_history')
                ->where('username_old_clean', $username_clean)
                ->where('change_time', '>', (config('user.name_reserve') * 24 * 60 * 60))
                ->orderBy('change_id', 'desc')
                ->get();

            // Check if anything was returned
            if ($getOld && $getOld[0]->user_id != ActiveUser::$user->id) {
                $message = "The username you tried to use is reserved, try again later!";
                return view('global/information', compact('redirect', 'message'));
            }

            // Check if the username is already in use
            $getInUse = DB::table('users')
                ->where('username_clean', $username_clean)
                ->get();

            // Check if anything was returned
            if ($getInUse) {
                $message = "Someone is already using this name!";
                return view('global/information', compact('redirect', 'message'));
            }

            ActiveUser::$user->setUsername($username, $username_clean);

            $message = "Changed your username!";
            return view('global/information', compact('redirect', 'message'));
        }

        return view('settings/account/username');
    }

    public function title()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_USERTITLE)) {
            $message = "You aren't allowed to change your title.";
            $redirect = route('settings.index');
            return view('global/information', compact('redirect', 'message'));
        }

        $title = $_POST['title'] ?? null;

        if (session_check() && $title !== null) {
            $redirect = route('settings.account.title');

            if (strlen($title) > 64) {
                $message = "This title is too long!";
                return view('global/information', compact('redirect', 'message'));
            }

            if ($title === ActiveUser::$user->title) {
                $message = "This is already your title!";
                return view('global/information', compact('redirect', 'message'));
            }

            // Update database
            DB::table('users')
                ->where('user_id', ActiveUser::$user->id)
                ->update([
                    'user_title' => $title,
                ]);

            $message = "Changed your title!";
            return view('global/information', compact('redirect', 'message'));
        }

        return view('settings/account/title');
    }

    public function password()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_PASSWORD)) {
            $message = "You aren't allowed to change your password.";
            $redirect = route('settings.index');
            return view('global/information', compact('redirect', 'message'));
        }

        $current = $_POST['current'] ?? null;
        $password = $_POST['password'] ?? null;

        if (session_check() && $current && $password) {
            $redirect = route('settings.account.password');

            // Check current password
            if (!password_verify($current, ActiveUser::$user->password)) {
                $message = "Your password was invalid!";
                return view('global/information', compact('redirect', 'message'));
            }

            // Check password entropy
            if (password_entropy($password) < config('user.pass_min_entropy')) {
                $message = "Your password isn't strong enough!";
                return view('global/information', compact('redirect', 'message'));
            }

            ActiveUser::$user->setPassword($password);

            $message = "Changed your password!";
            return view('global/information', compact('redirect', 'message'));
        }

        return view('settings/account/password');
    }

    public function ranks()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::ALTER_RANKS)) {
            $message = "You aren't allowed to manage your ranks.";
            $redirect = route('settings.index');
            return view('global/information', compact('redirect', 'message'));
        }

        $rank = $_POST['rank'] ?? null;
        $mode = $_POST['mode'] ?? null;

        $locked = [
            config('rank.inactive'),
            config('rank.regular'),
            config('rank.premium'),
            config('rank.alumni'),
            config('rank.banned'),
        ];

        if (session_check() && $rank && $mode) {
            $redirect = route('settings.account.ranks');

            // Check if user has this rank
            if (!ActiveUser::$user->hasRanks([$rank])) {
                $message = "You aren't a part of this rank!";
                return view('global/information', compact('redirect', 'message'));
            }

            if ($mode == 'remove') {
                if (in_array($rank, $locked)) {
                    $message = "You aren't allowed to remove this rank from your account!";
                    return view('global/information', compact('redirect', 'message'));
                }

                ActiveUser::$user->removeRanks([$rank]);

                $message = "Removed the rank from your account!";
                return view('global/information', compact('redirect', 'message'));
            }

            ActiveUser::$user->setMainRank($rank);

            $message = "Changed your main rank!";
            return view('global/information', compact('redirect', 'message'));
        }

        return view('settings/account/ranks', compact('locked'));
    }
}
