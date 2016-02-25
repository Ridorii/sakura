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
        DBv2::prepare('UPDATE `{prefix}users` SET `user_last_online` = :lo WHERE `user_id` = :id')
            ->execute([
            'lo' => time(),
            'id' => $uid,
        ]);

        // Update the premium meta
        self::updatePremiumMeta($uid);

        // If everything went through return true
        return [$uid, $sid];
    }

    /**
     * Log in to an account.
     *
     * @param string $username The username.
     * @param string $password The password.
     * @param bool $remember Stay logged in "forever"?
     * @param bool $cookies Set cookies?
     *
     * @return array Return the status.
     */
    public static function login($username, $password, $remember = false, $cookies = true)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Check if we haven't hit the rate limit
        $rates = DBv2::prepare('SELECT * FROM `{prefix}login_attempts` WHERE `attempt_ip` = :ip AND `attempt_timestamp` > :time AND `attempt_success` = 0');
        $rates->execute([
            'ip' => Net::pton(Net::IP()),
            'time' => time() - 1800,
        ]);
        $rates = $rates->fetchAll(\PDO::FETCH_ASSOC);

        if (count($rates) > 4) {
            return [0, 'RATE_LIMIT'];
        }

        // Check if the user that's trying to log in actually exists
        if (!$uid = self::userExists($username, false)) {
            return [0, 'USER_NOT_EXIST'];
        }

        // Get account data
        $user = User::construct($uid);

        // Validate password
        switch ($user->passwordAlgo) {
            // Disabled
            case 'disabled':
                return [0, 'NO_LOGIN'];

            // Default hashing method
            default:
                if (!Hashing::validatePassword($password, [
                    $user->passwordAlgo,
                    $user->passwordIter,
                    $user->passwordSalt,
                    $user->passwordHash,
                ])) {
                    return [0, 'INCORRECT_PASSWORD', $user->id, $user->passwordChan];
                }
        }

        // Check if the user has the required privs to log in
        if ($user->permission(Site::DEACTIVATED)) {
            return [0, 'NOT_ALLOWED', $user->id];
        }

        // Create a new session
        $session = new Session($user->id);

        // Generate a session key
        $sessionKey = $session->create($remember);

        // Set cookies
        if ($cookies) {
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
        }

        // Successful login! (also has a thing for the legacy password system)
        return [1, 'LOGIN_SUCCESS', $user->id];
    }

    /**
     * Logout
     *
     * @return bool Was the logout successful?
     */
    public static function logout()
    {
        // Check if user is logged in
        if (!$check = self::checkLogin()) {
            return false;
        }

        // Destroy the active session
        (new Session($check[0], $check[1]))->destroy();

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

        // Return true indicating a successful logout
        return true;
    }

    /**
     * Register a new account.
     *
     * @param string $username The username.
     * @param string $password The password.
     * @param string $confirmpass The password, again.
     * @param string $email The e-mail.
     * @param bool $tos Agreeing to the ToS.
     * @param string $captcha Captcha.
     * @param string $regkey Registration key (unused).
     *
     * @return array Status.
     */
    public static function register($username, $password, $confirmpass, $email, $tos, $captcha = null, $regkey = null)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Check if registration is even enabled
        if (Config::get('disable_registration')) {
            return [0, 'DISABLED'];
        }

        // Check if the user agreed to the ToS
        if (!$tos) {
            return [0, 'TOS'];
        }

        // Verify the captcha if it's enabled
        if (Config::get('recaptcha')) {
            if (!Utils::verifyCaptcha($captcha)['success']) {
                return [0, 'CAPTCHA_FAIL'];
            }
        }

        // Check if the username already exists
        if (self::userExists($username, false)) {
            return [0, 'USER_EXISTS'];
        }

        // Username too short
        if (strlen($username) < Config::get('username_min_length')) {
            return [0, 'NAME_TOO_SHORT'];
        }

        // Username too long
        if (strlen($username) > Config::get('username_max_length')) {
            return [0, 'NAME_TOO_LONG'];
        }

        // Check if the given email address is formatted properly
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [0, 'INVALID_EMAIL'];
        }

        // Check the MX record of the email
        if (!Utils::checkMXRecord($email)) {
            return [0, 'INVALID_MX'];
        }

        // Check if the e-mail has already been used
        $emailCheck = DBv2::prepare('SELECT `user_id` FROM `{prefix}users` WHERE `email` = :email');
        $emailCheck->execute([
            'email' => $email,
        ]);
        if ($emailCheck->rowCount() > 0) {
            return [0, 'EMAIL_EXISTS'];
        }

        // Check password entropy
        if (Utils::pwdEntropy($password) < Config::get('min_entropy')) {
            return [0, 'PASS_TOO_SHIT'];
        }

        // Passwords do not match
        if ($password != $confirmpass) {
            return [0, 'PASS_NOT_MATCH'];
        }

        // Set a few variables
        $requireActive = Config::get('require_activation');
        $ranks = $requireActive ? [1] : [2];

        // Create the user
        $user = User::create($username, $password, $email, $ranks);

        // Check if we require e-mail activation
        if ($requireActive) {
            // Send activation e-mail to user
            self::sendActivationMail($user->id);
        }

        // Return true with a specific message if needed
        return [1, ($requireActive ? 'EMAILSENT' : 'SUCCESS')];
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
        $user = DBv2::prepare('SELECT * FROM `{prefix}users` WHERE `username_clean` = :clean AND `email` = :email');
        $user->execute([
            'clean' => $usernameClean,
            'email' => $emailClean,
        ]);
        $user = $user->fetch(\PDO::FETCH_ASSOC);

        // Check if user exists
        if (count($user) < 2) {
            return [0, 'USER_NOT_EXIST'];
        }

        // Create user object
        $userObj = User::construct($user['user_id']);

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
        DBv2::prepare('UPDATE `{prefix}users` SET `password_hash` = :hash, `password_salt` = :salt, `password_algo` = :algo, `password_iter` = :iter, `password_chan` = :chan WHERE `user_id` = :id')
            ->execute([
            'hash' => $password[3],
            'salt' => $password[2],
            'algo' => $password[0],
            'iter' => $password[1],
            'chan' => time(),
            'id' => $uid,
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
        $user = DBv2::prepare('SELECT * FROM `{prefix}users` WHERE `username_clean` = :clean AND `email` = :email');
        $user->execute([
            'clean' => $usernameClean,
            'email' => $emailClean,
        ]);
        $user = $user->fetch(\PDO::FETCH_ASSOC);

        // Check if user exists
        if (count($user) < 2) {
            return [0, 'USER_NOT_EXIST'];
        }

        $userObj = User::construct($user['user_id']);

        // Check if a user is activated
        if (!$userObj->permission(Site::DEACTIVATED)) {
            return [0, 'USER_ALREADY_ACTIVE'];
        }

        // Send activation e-mail
        self::sendActivationMail($user['user_id']);

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
     * Check if a user exists.
     *
     * @param mixed $id The Username or ID.
     * @param mixed $unused Unused variable.
     *
     * @return mixed Returns the ID if it exists, false otherwise.
     */
    public static function userExists($id, $unused = null)
    {
        // Do database request
        $user = DBv2::prepare('SELECT * FROM `{prefix}users` WHERE `user_id` = :id OR `username_clean` = :clean');
        $user->execute([
            'id' => $id,
            'clean' => Utils::cleanString($id, true, true),
        ]);
        $user = $user->fetch();

        // Return count (which would return 0, aka false, if nothing was found)
        return $user ? $user->user_id : false;
    }

    /**
     * Get all available profile fields.
     *
     * @return array|null The fields.
     */
    public static function getProfileFields()
    {
        // Get profile fields
        $profileFields = DBv2::prepare('SELECT * FROM `{prefix}profilefields`');
        $profileFields->execute();
        $profileFields = $profileFields->fetchAll(\PDO::FETCH_ASSOC);

        // If there's nothing just return null
        if (!count($profileFields)) {
            return null;
        }

        // Create output array
        $fields = [];

        // Iterate over the fields and clean them up
        foreach ($profileFields as $field) {
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
        $optionFields = DBv2::prepare('SELECT * FROM `{prefix}optionfields`');
        $optionFields->execute();
        $optionFields = $optionFields->fetchAll(\PDO::FETCH_ASSOC);

        // If there's nothing just return null
        if (!count($optionFields)) {
            return null;
        }

        // Create output array
        $fields = [];

        $user = User::construct(self::checkLogin()[0]);

        // Iterate over the fields and clean them up
        foreach ($optionFields as $field) {
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
        $getAll = DBv2::prepare('SELECT * FROM `{prefix}users` WHERE `user_last_online` > :lo');
        $getAll->execute([
            'lo' => $time,
        ]);
        $getAll = $getAll->fetchAll();

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
        $getUser = DBv2::prepare('SELECT * FROM `{prefix}premium` WHERE `user_id` = :user');
        $getUser->execute([
            'user' => $id,
        ]);
        $getUser = $getUser->fetch(\PDO::FETCH_ASSOC);

        // Calculate the (new) start and expiration timestamp
        $start = isset($getUser['premium_start']) ? $getUser['premium_start'] : time();
        $expire = isset($getUser['premium_expire']) ? $getUser['premium_expire'] + $seconds : time() + $seconds;

        // If the user already exists do an update call, otherwise an insert call
        if (empty($getUser)) {
            DBv2::prepare('INSERT INTO `{prefix}premium` (`user_id`, `premium_start`, `premium_expire`) VALUES (:user, :start, :expire)')
                ->execute([
                'user' => $id,
                'start' => $start,
                'expire' => $expire,
            ]);
        } else {
            DBv2::prepare('UPDATE `{prefix}premium` SET `premium_expire` = :expire WHERE `user_id` = :id')
                ->execute([
                'expire' => $expire,
                'user_id' => $id,
            ]);
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
            DBv2::prepare('DELETE FROM `{prefix}premium` WHERE `user_id` = :user')
                ->execute([
                'user' => $user->id,
            ]);

            // Else remove the rank from them
            $user->removeRanks([$premiumRank]);
        }
    }

    /**
     * Get all users that registered from a certain IP.
     *
     * @param string $ip The IP.
     *
     * @return array The users.
     */
    public static function getUsersByIP($ip)
    {
        // Get the users
        $users = DBv2::prepare('SELECT * FROM `{prefix}users` WHERE `register_ip` = :rip OR `last_ip` = :lip');
        $users->execute([
            'rip' => $ip,
            'lip' => $ip,
        ]);
        $users = $users->fetchAll(\PDO::FETCH_ASSOC);

        // Return the array with users
        return $users;
    }

    /**
     * Get all ranks.
     *
     * @return array All ranks.
     */
    public static function getAllRanks()
    {
        // Execute query
        $getRanks = DBv2::prepare('SELECT * FROM `{prefix}ranks`');
        $getRanks->execute();
        $getRanks = $getRanks->fetchAll();

        // Define variable
        $ranks = [];

        // Reorder shit
        foreach ($getRanks as $rank) {
            $ranks[$rank->rank_id] = Rank::construct($rank->rank_id);
        }

        // and return an array with the ranks
        return $ranks;
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
        $notifications = DBv2::prepare('SELECT * FROM `{prefix}notifications` WHERE `user_id` = :user AND `alert_timestamp` > :time AND `alert_read` = :read');
        $notifications->execute([
            'user' => $uid,
            'time' => $time,
            'read' => $read,
        ]);
        $notifications = $notifications->fetchAll(\PDO::FETCH_ASSOC);

        // Mark the notifications as read
        if ($markRead) {
            // Iterate over all entries
            foreach ($notifications as $notification) {
                // If the notifcation is already read skip
                if ($notification['alert_read']) {
                    continue;
                }

                // Mark them as read
                self::markNotificationRead($notification['alert_id']);
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
        DBv2::prepare('UPDATE `{prefix}notifications` SET `alert_read` = :read WHERE `alert_id` = :id')
            ->execute([
            'read' => ($mode ? 1 : 0),
            'id' => $id,
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
        DBv2::prepare('INSERT INTO `{prefix}notifications` (`user_id`, `alert_timestamp`, `alert_read`, `alert_sound`, `alert_title`, `alert_text`, `alert_link`, `alert_img`, `alert_timeout`) VALUES (:user, :time, :read, :sound, :title, :text, :link, :img, :timeout)')
            ->execute([
            'user' => $user,
            'time' => time(),
            'read' => 0,
            'sound' => ($sound ? 1 : 0),
            'title' => $title,
            'text' => $text,
            'link' => $link,
            'img' => $img,
            'timeout' => $timeout,
        ]);
    }

    /**
     * Get the newest member's ID.
     *
     * @return int The user ID.
     */
    public static function getNewestUserId()
    {
        $get = DBv2::prepare('SELECT `user_id` FROM `{prefix}users` WHERE `rank_main` != :restricted ORDER BY `user_id` DESC LIMIT 1');
        $get->execute([
            'restricted' => Config::get('restricted_rank_id'),
        ]);
        $get = $get->fetch();

        return $get ? $get->user_id : 0;
    }
}
