<?php
/**
 * Holds various functions to interface with users.
 *
 * @package Sakura
 */

namespace Sakura;

use Sakura\Perms\Site;

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
     * @param string $username The username.
     * @param string $email The e-mail.
     *
     * @return array The status.
     */
    public static function sendPasswordForgot($username, $email)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Clean username string
        $usernameClean = Utils::cleanString($username, true);
        $emailClean = Utils::cleanString($email, true);

        // Do database request
        $user = DB::table('users')
            ->where('username_clean', $usernameClean)
            ->where(':email', $emailClean)
            ->get(['user_id']);

        // Check if user exists
        if (count($user) < 1) {
            return [0, 'USER_NOT_EXIST'];
        }

        $userObj = User::construct($user[0]->user_id);

        // Check if the user has the required privs to log in
        if ($userObj->permission(Site::DEACTIVATED)) {
            return [0, 'NOT_ALLOWED'];
        }

        // Generate the verification key
        $verk = ActionCode::generate('LOST_PASS', $userObj->id);

        // Create new urls object
        $urls = new Urls();

        // Build the e-mail
        $message = "Hello " . $user['username'] . ",\r\n\r\n";
        $message .= "You are receiving this notification because you have (or someone pretending to be you has) requested a password reset link to be sent for your account on \"" . Config::get('sitename') . "\". If you did not request this notification then please ignore it, if you keep receiving it please contact the site administrator.\r\n\r\n";
        $message .= "To use this password reset key you need to go to a special page. To do this click the link provided below.\r\n\r\n";
        $message .= "http://" . Config::get('url_main') . $urls->format('SITE_FORGOT_PASSWORD') . "?pw=true&uid=" . $user['user_id'] . "&key=" . $verk . "\r\n\r\n";
        $message .= "If successful you should be able to change your password here.\r\n\r\n";
        $message .= "Alternatively if the above method fails for some reason you can go to http://" . Config::get('url_main') . $urls->format('SITE_FORGOT_PASSWORD') . "?pw=true&uid=" . $user['user_id'] . " and use the key listed below:\r\n\r\n";
        $message .= "Verification key: " . $verk . "\r\n\r\n";
        $message .= "You can of course change this password yourself via the profile page. If you have any difficulties please contact the site administrator.\r\n\r\n";
        $message .= "--\r\n\r\nThanks\r\n\r\n" . Config::get('mail_signature');

        // Send the message
        Utils::sendMail([$user['email'] => $user['username']], Config::get('sitename') . ' password restoration', $message);

        // Return success
        return [1, 'SUCCESS'];
    }

    /**
     * Reset a password.
     *
     * @param string $verk The e-mail verification key.
     * @param int $uid The user id.
     * @param string $newpass New pass.
     * @param string $verpass Again.
     *
     * @return array Status.
     */
    public static function resetPassword($verk, $uid, $newpass, $verpass)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Check password entropy
        if (Utils::pwdEntropy($newpass) < Config::get('min_entropy')) {
            return [0, 'PASS_TOO_SHIT'];
        }

        // Passwords do not match
        if ($newpass != $verpass) {
            return [0, 'PASS_NOT_MATCH'];
        }

        // Check the verification key
        $action = ActionCode::validate('LOST_PASS', $verk, $uid);

        // Check if we got a negative return
        if (!$action) {
            return [0, 'INVALID_CODE'];
        }

        // Hash the password
        $password = Hashing::createHash($newpass);

        // Update the user
        DB::table('users')
            ->where('user_id', $uid)
            ->update([
                'password_hash' => $password[3],
                'password_salt' => $password[2],
                'password_algo' => $password[0],
                'password_iter' => $password[1],
                'password_chan' => time(),
            ]);

        // Return success
        return [1, 'SUCCESS'];
    }

    /**
     * Resend activation e-mail.
     *
     * @param string $username Username.
     * @param string $email E-mail.
     *
     * @return array Status
     */
    public static function resendActivationMail($username, $email)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Clean username string
        $usernameClean = Utils::cleanString($username, true);
        $emailClean = Utils::cleanString($email, true);

        // Do database request
        $user = DB::table('users')
            ->where('username_clean', $usernameClean)
            ->where(':email', $emailClean)
            ->get(['user_id']);

        // Check if user exists
        if (count($user) < 1) {
            return [0, 'USER_NOT_EXIST'];
        }

        $userObj = User::construct($user[0]->user_id);

        // Check if a user is activated
        if (!$userObj->permission(Site::DEACTIVATED)) {
            return [0, 'USER_ALREADY_ACTIVE'];
        }

        // Send activation e-mail
        self::sendActivationMail($userObj->id);

        // Return success
        return [1, 'SUCCESS'];
    }

    /**
     * Send activation e-mail.
     *
     * @param mixed $uid User ID.
     * @param mixed $customKey Key.
     *
     * @return bool Always true.
     */
    public static function sendActivationMail($uid, $customKey = null)
    {

        // Get the user data
        $user = User::construct($uid);

        // User is already activated or doesn't even exist
        if (!$user->id || !$user->permission(Site::DEACTIVATED)) {
            return false;
        }

        // Generate activation key
        $activate = ActionCode::generate('ACTIVATE', $user->id);

        // Create new urls object
        $urls = new Urls();

        // Build the e-mail
        $message = "Welcome to " . Config::get('sitename') . "!\r\n\r\n";
        $message .= "Please keep this e-mail for your records. Your account intormation is as follows:\r\n\r\n";
        $message .= "----------------------------\r\n\r\n";
        $message .= "Username: " . $user->username . "\r\n\r\n";
        $message .= "Your profile: http://" . Config::get('url_main') . $urls->format('USER_PROFILE', [$user->id]) . "\r\n\r\n";
        $message .= "----------------------------\r\n\r\n";
        $message .= "Please visit the following link in order to activate your account:\r\n\r\n";
        $message .= "http://" . Config::get('url_main') . $urls->format('SITE_ACTIVATE') . "?mode=activate&u=" . $user->id . "&k=" . $activate . "\r\n\r\n";
        $message .= "Your password has been securely stored in our database and cannot be retrieved. ";
        $message .= "In the event that it is forgotten, you will be able to reset it using the email address associated with your account.\r\n\r\n";
        $message .= "Thank you for registering.\r\n\r\n";
        $message .= "--\r\n\r\nThanks\r\n\r\n" . Config::get('mail_signature');

        // Send the message
        Utils::sendMail(
            [
                $user->email => $user->username,
            ],
            Config::get('sitename') . ' Activation Mail',
            $message
        );

        // Return true indicating that the things have been sent
        return true;
    }

    /**
     * Activate a user.
     *
     * @param int $uid The ID.
     * @param bool $requireKey Require a key.
     * @param string $key The key.
     *
     * @return array Status.
     */
    public static function activateUser($uid, $requireKey = false, $key = null)
    {
        // Get the user data
        $user = User::construct($uid);

        // Check if user exists
        if (!$user->id) {
            return [0, 'USER_NOT_EXIST'];
        }

        // Check if user is already activated
        if (!$user->permission(Site::DEACTIVATED)) {
            return [0, 'USER_ALREADY_ACTIVE'];
        }

        // Check if a key is set
        if ($requireKey) {
            // Check the action code
            $action = ActionCode::validate('ACTIVATE', $key, $user->id);

            // Check if we got a negative return
            if (!$action) {
                return [0, 'INVALID_CODE'];
            }
        }

        // Add normal user, remove deactivated and set normal as default
        $user->addRanks([2]);
        $user->removeRanks([1]);
        $user->setMainRank(2);

        // Return success
        return [1, 'SUCCESS'];
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
     * Get a user's notifications.
     *
     * @param int $uid The user id.
     * @param int $timediff The maximum difference in time.
     * @param bool $excludeRead Exclude notifications that were already read.
     * @param bool $markRead Automatically mark as read.
     *
     * @return array The notifications.
     */
    public static function getNotifications($uid = null, $timediff = 0, $excludeRead = true, $markRead = false)
    {
        // Prepare conditions
        $uid = $uid ? $uid : self::checkLogin()[0];
        $time = $timediff ? time() - $timediff : '%';
        $read = $excludeRead ? '0' : '%';

        // Get notifications for the database
        $alerts = DB::table('notifications')
            ->where('user_id', $uid)
            ->where('alert_timestamp', '>', $time)
            ->where('alert_read', $read)
            ->get();

        // Mark the notifications as read
        if ($markRead) {
            // Iterate over all entries
            foreach ($alerts as $alert) {
                // If the notifcation is already read skip
                if ($alert->alert_read) {
                    continue;
                }

                // Mark them as read
                self::markNotificationRead($notification->alert_id);
            }
        }

        // Return the notifications
        return $notifications;
    }

    /**
     * Mark a notification as read
     *
     * @param mixed $id The notification's ID.
     * @param mixed $mode Read or unread.
     */
    public static function markNotificationRead($id, $mode = true)
    {
        // Execute an update statement
        DB::table('notifications')
            ->where('alert_id', $id)
            ->update([
                'alert_read' => ($mode ? 1 : 0),
            ]);
    }

    /**
     * Create a new notification.
     *
     * @param int $user The user id.
     * @param string $title The notification title.
     * @param string $text The rest of the text.
     * @param int $timeout After how many seconds the notification should disappear.
     * @param string $img The image.
     * @param string $link The link.
     * @param int $sound Whether it should play a noise.
     */
    public static function createNotification($user, $title, $text, $timeout = 60000, $img = 'FONT:fa-info-circle', $link = '', $sound = 0)
    {
        // Insert it into the database
        DB::table('notifications')
            ->insert([
                'user_id' => $user,
                'alert_timestamp' => time(),
                'alert_read' => 0,
                'alert_sound' => ($sound ? 1 : 0),
                'alert_title' => $title,
                'alert_text' => $text,
                'alert_link' => $link,
                'alert_img' => $img,
                'alert_timeout' => $timeout,
            ]);
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
            ->orderBy('user_id', 'desc')
            ->limit(1)
            ->get(['user_id']);

        return $get ? $get[0]->user_id : 0;
    }
}
