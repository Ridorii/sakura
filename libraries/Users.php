<?php
/**
 * Holds various functions to interface with users.
 *
 * @package Sakura
 */

namespace Sakura;

use Sakura\Perms\Site;
use Sakura\Router;

/**
 * User management
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Users
{
    /**
     * Check if a user is logged in
     *
     * @param int $uid The user ID.
     * @param string $sid The session ID.
     *
     * @return array|bool Either false or the ID and session in an array.
     */
    public static function checkLogin($uid = null, $sid = null)
    {
        // Assign $uid and $sid
        $uid = $uid ? $uid : (isset($_COOKIE[Config::get('cookie_prefix') . 'id'])
            ? $_COOKIE[Config::get('cookie_prefix') . 'id']
            : 0);
        $sid = $sid ? $sid : (isset($_COOKIE[Config::get('cookie_prefix') . 'session'])
            ? $_COOKIE[Config::get('cookie_prefix') . 'session']
            : 0);

        // Get session
        $session = new Session($uid, $sid);

        // Validate the session
        $sessionValid = $session->validate();

        // Get user object
        $user = User::construct($uid);

        // Check if the session exists and check if the user is activated
        if ($sessionValid == 0 || $user->permission(Site::DEACTIVATED)) {
            // Unset User ID
            setcookie(
                Config::get('cookie_prefix') . 'id',
                0,
                time() - 60,
                Config::get('cookie_path')
            );

            // Unset Session ID
            setcookie(
                Config::get('cookie_prefix') . 'session',
                '',
                time() - 60,
                Config::get('cookie_path')
            );

            return false;
        }

        // Extend the cookie times if the remember flag is set
        if ($sessionValid == 2) {
            // User ID cookie
            setcookie(
                Config::get('cookie_prefix') . 'id',
                $uid,
                time() + 604800,
                Config::get('cookie_path')
            );

            // Session ID cookie
            setcookie(
                Config::get('cookie_prefix') . 'session',
                $sid,
                time() + 604800,
                Config::get('cookie_path')
            );
        }

        // Update last online
        DB::table('users')
            ->where('user_id', $uid)
            ->update([
                'user_last_online' => time(),
            ]);

        // Update the premium meta
        self::updatePremiumMeta($uid);

        // If everything went through return true
        return [$uid, $sid];
    }

    /**
     * Send password forgot e-mail
     *
     * @param string $userId The user id.
     * @param string $email The e-mail.
     */
    public static function sendPasswordForgot($userId, $email)
    {
        $user = User::construct($userId);

        if (!$user->id || $user->permission(Site::DEACTIVATED)) {
            return;
        }

        // Generate the verification key
        $verk = ActionCode::generate('LOST_PASS', $user->id);

        $siteName = Config::get('sitename');
        $baseUrl = "http://" . Config::get('url_main');
        $reactivateLink = Router::route('auth.resetpassword') . "?u={$user->id}&k={$verk}";
        $signature = Config::get('mail_signature');

        // Build the e-mail
        $message = "Hello {$user->username},\r\n\r\n"
            . "You are receiving this notification because you have (or someone pretending to be you has)"
            . " requested a password reset link to be sent for your account on \"{$siteName}\"."
            . " If you did not request this notification then please ignore it,"
            . " if you keep receiving it please contact the site administrator.\r\n\r\n"
            . "To use this password reset key you need to go to a special page."
            . " To do this click the link provided below.\r\n\r\n"
            . "{$baseUrl}{$reactivateLink}\r\n\r\n"
            . "If successful you should be able to change your password here.\r\n\r\n"
            . "You can of course change this password yourself via the settings page."
            . " If you have any difficulties please contact the site administrator.\r\n\r\n"
            . "--\r\n\r\nThanks\r\n\r\n{$signature}";

        // Send the message
        Utils::sendMail([$user->email => $user->username], "{$siteName} password restoration", $message);
    }

    /**
     * Send activation e-mail.
     *
     * @param mixed $userId User ID.
     * @param mixed $customKey Key.
     */
    public static function sendActivationMail($userId, $customKey = null)
    {

        // Get the user data
        $user = User::construct($userId);

        // User is already activated or doesn't even exist
        if (!$user->id || !$user->permission(Site::DEACTIVATED)) {
            return;
        }

        // Generate activation key
        $activate = ActionCode::generate('ACTIVATE', $user->id);

        $siteName = Config::get('sitename');
        $baseUrl = "http://" . Config::get('url_main');
        $activateLink = Router::route('auth.activate') . "?u={$user->id}&k={$activate}";
        $profileLink = Router::route('user.profile', $user->id);
        $signature = Config::get('mail_signature');

        // Build the e-mail
        $message = "Welcome to {$siteName}!\r\n\r\n"
            . "Please keep this e-mail for your records. Your account intormation is as follows:\r\n\r\n"
            . "----------------------------\r\n\r\n"
            . "Username: {$user->username}\r\n\r\n"
            . "Your profile: {$baseUrl}{$profileLink}\r\n\r\n"
            . "----------------------------\r\n\r\n"
            . "Please visit the following link in order to activate your account:\r\n\r\n"
            . "{$baseUrl}{$activateLink}\r\n\r\n"
            . "Your password has been securely stored in our database and cannot be retrieved. "
            . "In the event that it is forgotten,"
            . " you will be able to reset it using the email address associated with your account.\r\n\r\n"
            . "Thank you for registering.\r\n\r\n"
            . "--\r\n\r\nThanks\r\n\r\n{$signature}";

        // Send the message
        Utils::sendMail([$user->email => $user->username], "{$siteName} activation mail", $message);
    }

    /**
     * Get all available profile fields.
     *
     * @return array|null The fields.
     */
    public static function getProfileFields()
    {
        // Get profile fields
        $profileFields = DB::table('profilefields')
            ->get();

        // If there's nothing just return null
        if (!count($profileFields)) {
            return null;
        }

        // Create output array
        $fields = [];

        // Iterate over the fields and clean them up
        foreach ($profileFields as $field) {
            $field = get_object_vars($field);
            $fields[$field['field_id']] = $field;
            $fields[$field['field_id']]['field_identity'] = Utils::cleanString($field['field_name'], true, true);
            $fields[$field['field_id']]['field_additional'] = json_decode($field['field_additional'], true);
        }

        // Return the yeahs
        return $fields;
    }

    /**
     * Get all available option fields.
     *
     * @return array|null The fields.
     */
    public static function getOptionFields()
    {
        // Get option fields
        $optionFields = DB::table('optionfields')
            ->get();

        // If there's nothing just return null
        if (!count($optionFields)) {
            return null;
        }

        // Create output array
        $fields = [];

        $user = User::construct(self::checkLogin()[0]);

        // Iterate over the fields and clean them up
        foreach ($optionFields as $field) {
            $field = get_object_vars($field);

            if (!$user->permission(constant('Sakura\Perms\Site::' . $field['option_permission']))) {
                continue;
            }

            $fields[$field['option_id']] = $field;
        }

        // Return the yeahs
        return $fields;
    }

    /**
     * Get all online users.
     *
     * @return array Array containing User instances.
     */
    public static function checkAllOnline()
    {
        // Assign time - 500 to a variable
        $time = time() - Config::get('max_online_time');

        $return = [];

        // Get all online users in the past 5 minutes
        $getAll = DB::table('users')
            ->where('user_last_online', '>', $time)
            ->get();

        foreach ($getAll as $user) {
            $return[] = User::construct($user->user_id);
        }

        // Return all the online users
        return $return;
    }

    /**
     * Add premium time to a user.
     *
     * @param int $id The user ID.
     * @param int $seconds The amount of extra seconds.
     *
     * @return array|double|int The new expiry date.
     */
    public static function addUserPremium($id, $seconds)
    {
        // Check if there's already a record of premium for this user in the database
        $getUser = DB::table('premium')
            ->where('user_id', $id)
            ->count();

        // Calculate the (new) start and expiration timestamp
        $start = isset($getUser['premium_start']) ? $getUser['premium_start'] : time();
        $expire = isset($getUser['premium_expire']) ? $getUser['premium_expire'] + $seconds : time() + $seconds;

        // If the user already exists do an update call, otherwise an insert call
        if (empty($getUser)) {
            DB::table('premium')
                ->insert([
                    'user_id' => $id,
                    'premium_start' => $start,
                    'premium_expire' => $expire,
                ]);
        } else {
            DB::table('premium')
                ->where('user_id', $id)
                ->update('premium_expire', $expire);
        }

        // Return the expiration timestamp
        return $expire;
    }

    /**
     * Process premium meta data.
     *
     * @param int $id The user ID.
     */
    public static function updatePremiumMeta($id)
    {
        // Get the ID for the premium user rank from the database
        $premiumRank = Config::get('premium_rank_id');
        $excepted = Config::get('restricted_rank_id');

        // Create user object
        $user = User::construct($id);

        // Run the check
        $check = $user->isPremium();

        // Check if the user has premium
        if ($check[0] && !array_key_exists($excepted, $user->ranks)) {
            // If so add the rank to them
            $user->addRanks([$premiumRank]);

            // Check if the user's default rank is standard user and update it to premium
            if ($user->mainRankId == 2) {
                $user->setMainRank($premiumRank);
            }
        } elseif (!$check[0]) {
            // Remove the expired entry
            DB::table('premium')
                ->where('user_id', $user->id)
                ->delete();

            // Else remove the rank from them
            $user->removeRanks([$premiumRank]);
        }
    }

    /**
     * Get the newest member's ID.
     *
     * @return int The user ID.
     */
    public static function getNewestUserId()
    {
        $get = DB::table('users')
            ->where('rank_main', '!=', Config::get('restricted_rank_id'))
            ->where('rank_main', '!=', Config::get('deactive_rank_id'))
            ->orderBy('user_id', 'desc')
            ->limit(1)
            ->get(['user_id']);

        return $get ? $get[0]->user_id : 0;
    }
}
