<?php
/*
 * User Management
 */

namespace Sakura;

use Sakura\Perms\Site;

/**
 * Class Users
 * @package Sakura
 */
class Users
{
    // Check if a user is logged in
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
                Config::get('cookie_path'),
                Config::get('cookie_domain')
            );

            // Unset Session ID
            setcookie(
                Config::get('cookie_prefix') . 'session',
                '',
                time() - 60,
                Config::get('cookie_path'),
                Config::get('cookie_domain')
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
                Config::get('cookie_path'),
                Config::get('cookie_domain')
            );

            // Session ID cookie
            setcookie(
                Config::get('cookie_prefix') . 'session',
                $sid,
                time() + 604800,
                Config::get('cookie_path'),
                Config::get('cookie_domain')
            );
        }

        // Update last online
        Database::update('users', [
            [
                'user_last_online' => time(),
            ],
            [
                'user_id' => [$uid, '='],
            ],
        ]);

        // Update the premium meta
        self::updatePremiumMeta($uid);

        // If everything went through return true
        return [$uid, $sid];
    }

    // Log a user in
    public static function login($username, $password, $remember = false, $cookies = true)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Check if we haven't hit the rate limit
        $rates = Database::fetch('login_attempts', true, [
            'attempt_ip' => [Main::getRemoteIP(), '='],
            'attempt_timestamp' => [time() - 1800, '>'],
            'attempt_success' => [0, '='],
        ]);

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
        switch ($user->password()['password_algo']) {
            // Disabled
            case 'disabled':
                return [0, 'NO_LOGIN'];

            // Default hashing method
            default:
                if (!Hashing::validatePassword($password, [
                    $user->password()['password_algo'],
                    $user->password()['password_iter'],
                    $user->password()['password_salt'],
                    $user->password()['password_hash'],
                ])) {
                    return [0, 'INCORRECT_PASSWORD', $user->id(), $user->password()['password_chan']];
                }
        }

        // Check if the user has the required privs to log in
        if ($user->permission(Site::DEACTIVATED)) {
            return [0, 'NOT_ALLOWED', $user->id()];
        }

        // Create a new session
        $session = new Session($user->id());

        // Generate a session key
        $sessionKey = $session->create($remember);

        // Set cookies
        if ($cookies) {
            // User ID cookie
            setcookie(
                Config::get('cookie_prefix') . 'id',
                $user->id(),
                time() + 604800,
                Config::get('cookie_path'),
                Config::get('cookie_domain')
            );

            // Session ID cookie
            setcookie(
                Config::get('cookie_prefix') . 'session',
                $sessionKey,
                time() + 604800,
                Config::get('cookie_path'),
                Config::get('cookie_domain')
            );
        }

        // Successful login! (also has a thing for the legacy password system)
        return [1, 'LOGIN_SUCCESS', $user->id()];
    }

    // Logout and kill the session
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
            Config::get('cookie_path'),
            Config::get('cookie_domain')
        );

        // Unset Session ID
        setcookie(
            Config::get('cookie_prefix') . 'session',
            '',
            time() - 60,
            Config::get('cookie_path'),
            Config::get('cookie_domain')
        );

        // Return true indicating a successful logout
        return true;
    }

    // Register user
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

        // Check if registration codes are required
        if (Config::get('require_registration_code')) {
            // Check if the code is valid
            if (!self::checkRegistrationCode($regkey)) {
                return [0, 'INVALID_REG_KEY'];
            }
        }

        // Check if the user agreed to the ToS
        if (!$tos) {
            return [0, 'TOS'];
        }

        // Verify the captcha if it's enabled
        if (Config::get('recaptcha')) {
            if (!Main::verifyCaptcha($captcha)['success']) {
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
        if (!Main::checkMXRecord($email)) {
            return [0, 'INVALID_MX'];
        }

        // Check password entropy
        if (Main::pwdEntropy($password) < Config::get('min_entropy')) {
            return [0, 'PASS_TOO_SHIT'];
        }

        // Passwords do not match
        if ($password != $confirmpass) {
            return [0, 'PASS_NOT_MATCH'];
        }

        // Set a few variables
        $usernameClean = Main::cleanString($username, true);
        $emailClean = Main::cleanString($email, true);
        $password = Hashing::createHash($password);
        $requireActive = Config::get('require_activation');
        $userRank = $requireActive ? [1] : [2];
        $userRankJson = json_encode($userRank);

        // Insert the user into the database
        Database::insert('users', [
            'username' => $username,
            'username_clean' => $usernameClean,
            'password_hash' => $password[3],
            'password_salt' => $password[2],
            'password_algo' => $password[0],
            'password_iter' => $password[1],
            'email' => $emailClean,
            'rank_main' => $userRank[0],
            'user_ranks' => $userRankJson,
            'register_ip' => Main::getRemoteIP(),
            'last_ip' => Main::getRemoteIP(),
            'user_registered' => time(),
            'user_last_online' => 0,
            'user_country' => Main::getCountryCode(),
            'user_data' => '[]',
        ]);

        // Get userid of the new user
        $uid = Database::fetch('users', false, ['username_clean' => [$usernameClean, '=']])['user_id'];

        // Check if we require e-mail activation
        if ($requireActive) {
            // Send activation e-mail to user
            self::sendActivationMail($uid);
        }

        // Check if registration codes are required
        if (Config::get('require_registration_code')) {
            // If we do mark the registration code that was used as used
            self::markRegistrationCodeUsed($regkey, $uid);
        }

        // Return true with a specific message if needed
        return [1, ($requireActive ? 'EMAILSENT' : 'SUCCESS')];
    }

    // Check if a user exists and then send the password forgot email
    public static function sendPasswordForgot($username, $email)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Clean username string
        $usernameClean = Main::cleanString($username, true);
        $emailClean = Main::cleanString($email, true);

        // Do database request
        $user = Database::fetch('users', false, [
            'username_clean' => [$usernameClean, '='],
            'email' => [$emailClean, '='],
        ]);

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
        $verk = Main::newActionCode('LOST_PASS', $user['user_id'], [
            'meta' => [
                'password_change' => 1,
            ],
        ]);

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
        Main::sendMail([$user['email'] => $user['username']], Config::get('sitename') . ' password restoration', $message);

        // Return success
        return [1, 'SUCCESS'];
    }

    // Reset password with key
    public static function resetPassword($verk, $uid, $newpass, $verpass)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Check password entropy
        if (Main::pwdEntropy($newpass) < Config::get('min_entropy')) {
            return [0, 'PASS_TOO_SHIT'];
        }

        // Passwords do not match
        if ($newpass != $verpass) {
            return [0, 'PASS_NOT_MATCH'];
        }

        // Check the verification key
        $action = Main::useActionCode('LOST_PASS', $verk, $uid);

        // Check if we got a negative return
        if (!$action[0]) {
            return [0, $action[1]];
        }

        // Hash the password
        $password = Hashing::createHash($newpass);
        $time = time();

        // Update the user
        Database::update('users', [
            [
                'password_hash' => $password[3],
                'password_salt' => $password[2],
                'password_algo' => $password[0],
                'password_iter' => $password[1],
                'password_chan' => $time,
            ],
            [
                'user_id' => [$uid, '='],
            ],
        ]);

        // Return success
        return [1, 'SUCCESS'];
    }

    // Check if a user exists and then resend the activation e-mail
    public static function resendActivationMail($username, $email)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Clean username string
        $usernameClean = Main::cleanString($username, true);
        $emailClean = Main::cleanString($email, true);

        // Do database request
        $user = Database::fetch('users', false, [
            'username_clean' => [$usernameClean, '='],
            'email' => [$emailClean, '='],
        ]);

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

    // Send the activation e-mail and do other required stuff
    public static function sendActivationMail($uid, $customKey = null)
    {

        // Get the user data
        $user = User::construct($uid);

        // User is already activated or doesn't even exist
        if (!$user->id() || !$user->permission(Site::DEACTIVATED)) {
            return false;
        }

        // Generate activation key
        $activate = ($customKey ? $customKey : Main::newActionCode('ACTIVATE', $user->id(), [
            'user' => [
                'rank_main' => 2,
                'user_ranks' => json_encode([2]),
            ],
        ]));

        // Create new urls object
        $urls = new Urls();

        // Build the e-mail
        $message = "Welcome to " . Config::get('sitename') . "!\r\n\r\n";
        $message .= "Please keep this e-mail for your records. Your account intormation is as follows:\r\n\r\n";
        $message .= "----------------------------\r\n\r\n";
        $message .= "Username: " . $user['username'] . "\r\n\r\n";
        $message .= "Your profile: http://" . Config::get('url_main') . $urls->format('USER_PROFILE', [$user['user_id']]) . "\r\n\r\n";
        $message .= "----------------------------\r\n\r\n";
        $message .= "Please visit the following link in order to activate your account:\r\n\r\n";
        $message .= "http://" . Config::get('url_main') . $urls->format('SITE_ACTIVATE') . "?mode=activate&u=" . $user['user_id'] . "&k=" . $activate . "\r\n\r\n";
        $message .= "Your password has been securely stored in our database and cannot be retrieved. ";
        $message .= "In the event that it is forgotten, you will be able to reset it using the email address associated with your account.\r\n\r\n";
        $message .= "Thank you for registering.\r\n\r\n";
        $message .= "--\r\n\r\nThanks\r\n\r\n" . Config::get('mail_signature');

        // Send the message
        Main::sendMail(
            [
                $user['email'] => $user['username'],
            ],
            Config::get('sitename') . ' Activation Mail',
            $message
        );

        // Return true indicating that the things have been sent
        return true;
    }

    // Activating a user
    public static function activateUser($uid, $requireKey = false, $key = null)
    {
        // Get the user data
        $user = User::construct($uid);

        // Check if user exists
        if (!$user->id()) {
            return [0, 'USER_NOT_EXIST'];
        }

        // Check if user is already activated
        if (!$user->permission(Site::DEACTIVATED)) {
            return [0, 'USER_ALREADY_ACTIVE'];
        }

        // Set default values for activation
        $rank = 2;
        $ranks = json_encode([2]);

        /* Check if a key is set (there's an option to not set one for user
        management reasons but you can't really get around this anyway) */
        if ($requireKey) {
            // Check the action code
            $action = Main::useActionCode('ACTIVATE', $key, $user->id());

            // Check if we got a negative return
            if (!$action[0]) {
                return [0, $action[1]];
            }

            // Assign the special values
            $instructionData = json_decode($action[2], true);
            $rank = $instructionData['user']['rank_main'];
            $ranks = $instructionData['user']['user_ranks'];
        }

        // Activate the account
        Database::update('users', [
            [
                'rank_main' => $rank,
                'user_ranks' => $ranks,
            ],
            [
                'user_id' => [$user->id(), '='],
            ],
        ]);

        // Return success
        return [1, 'SUCCESS'];
    }

    // Check if registration code is valid
    public static function checkRegistrationCode($code)
    {
        // Get registration key
        $keyRow = Database::fetch('regcodes', true, ['code' => [$code, '='], 'key_used' => [0, '=']]);

        // Check if it exists and return it
        return count($keyRow) ? $keyRow[0]['id'] : false;
    }

    // Mark registration code as used
    public static function markRegistrationCodeUsed($code, $uid = 0)
    {
        // Check if the code exists
        if (!$id = self::checkRegistrationCode($code)) {
            return false;
        }

        // Mark it as used
        Database::update('regcodes', [
            [
                'used_by' => $uid,
                'key_used' => 1,
            ],
            [
                'id' => [$id, '='],
            ],
        ]);

        // Return true because yeah
        return true;
    }

    // Create new registration code
    public static function createRegistrationCode($userId)
    {
        // Check if we're logged in
        if (!self::checkLogin()) {
            return false;
        }

        // Check if the user is not exceeding the maximum registration key amount
        if (Database::count(
            'regcodes',
            true,
            ['uid' => [$userId, '=']]
        )[0] >= Config::get('max_reg_keys')) {
            return false;
        }

        // Generate a code by MD5'ing some random bullshit
        $code = md5('SAKURA' . rand(0, 99999999) . $userId . 'NOOKLSISGOD');

        // Insert the key into the database
        Database::insert('regcodes', [
            'code' => $code,
            'created_by' => $userId,
            'used_by' => 0,
            'key_used' => 0,
        ]);

        // Return the code
        return $code;
    }

    // Check if a user exists
    public static function userExists($user, $id = true)
    {
        // Clean string
        $user = Main::cleanString($user, true);

        // Do database request
        $user = Database::fetch('users', true, [($id ? 'user_id' : 'username_clean') => [$user, '=']]);

        // Return count (which would return 0, aka false, if nothing was found)
        return count($user) ? $user[0]['user_id'] : false;
    }

    // Get the available profile fields
    public static function getProfileFields()
    {
        // Get profile fields
        $profileFields = Database::fetch('profilefields');

        // If there's nothing just return null
        if (!count($profileFields)) {
            return null;
        }

        // Create output array
        $fields = [];

        // Iterate over the fields and clean them up
        foreach ($profileFields as $field) {
            $fields[$field['field_id']] = $field;
            $fields[$field['field_id']]['field_identity'] = Main::cleanString($field['field_name'], true, true);
            $fields[$field['field_id']]['field_additional'] = json_decode($field['field_additional'], true);
        }

        // Return the yeahs
        return $fields;
    }

    // Get the available option fields
    public static function getOptionFields()
    {
        // Get option fields
        $optionFields = Database::fetch('optionfields');

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

    // Get all online users
    public static function checkAllOnline()
    {
        // Assign time - 500 to a variable
        $time = time() - Config::get('max_online_time');

        $return = [];

        // Get all online users in the past 5 minutes
        $getAll = Database::fetch('users', true, ['user_last_online' => [$time, '>']]);

        foreach ($getAll as $user) {
            $return[] = User::construct($user['user_id']);
        }

        // Return all the online users
        return $return;
    }

    // Add premium to a user
    public static function addUserPremium($id, $seconds)
    {
        // Check if there's already a record of premium for this user in the database
        $getUser = Database::fetch('premium', false, [
            'user_id' => [$id, '='],
        ]);

        // Calculate the (new) start and expiration timestamp
        $start = isset($getUser['premium_start']) ? $getUser['premium_start'] : time();
        $expire = isset($getUser['premium_expire']) ? $getUser['premium_expire'] + $seconds : time() + $seconds;

        // If the user already exists do an update call, otherwise an insert call
        if (empty($getUser)) {
            Database::insert('premium', [
                'user_id' => $id,
                'premium_start' => $start,
                'premium_expire' => $expire,
            ]);
        } else {
            Database::update('premium', [
                [
                    'premium_expire' => $expire,
                ],
                [
                    'user_id' => [$id, '='],
                ],
            ]);
        }

        // Return the expiration timestamp
        return $expire;
    }

    // Update the premium data
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
        if ($check[0] && !in_array($excepted, $user->ranks())) {
            // If so add the rank to them
            $user->addRanks([$premiumRank]);

            // Check if the user's default rank is standard user and update it to premium
            if ($user->mainRank() == 2) {
                $user->setMainRank($premiumRank);
            }
        } elseif (!$check[0]) {
            // Remove the expired entry
            Database::delete('premium', [
                'user_id' => [$user->id(), '='],
            ]);

            // Else remove the rank from them
            $user->removeRanks([$premiumRank]);
        }
    }

    // Get user(s) by IP
    public static function getUsersByIP($ip)
    {
        // Get users by registration IP
        $registeredFrom = Database::fetch('users', true, ['register_ip' => [$ip, '=']]);

        // Get users by last IP
        $lastFrom = Database::fetch('users', true, ['last_ip' => [$ip, '='], 'register_ip' => [$ip, '!=']]);

        // Merge the arrays
        $users = array_merge($registeredFrom, $lastFrom);

        // Return the array with users
        return $users;
    }

    // Get users in rank
    public static function getUsersInRank($rankId, $users = null, $excludeAbyss = true)
    {
        // Get all users (or use the supplied user list to keep server load down)
        if (!$users) {
            $users = self::getAllUsers();
        }

        // Make output array
        $rank = [];

        // Go over all users and check if they have the rank id
        foreach ($users as $user) {
            // If so store the user's row in the array
            if ($user->hasRanks([$rankId], $user->id())) {
                $rank[] = $user;
            }
        }

        // Then return the array with the user rows
        return $rank;
    }

    // Get all users
    public static function getAllUsers($includeInactive = true, $includeAbyss = false)
    {
        // Execute query
        $getUsers = Database::fetch('users', true);

        // Define variable
        $users = [];

        // Reorder shit
        foreach ($getUsers as $user) {
            $user = User::construct($user['user_id']);

            // Skip if inactive and not include deactivated users
            if (!$includeInactive && $user->permission(Site::DEACTIVATED)) {
                continue;
            }

            $users[$user->id()] = $user;
        }

        // and return an array with the users
        return $users;
    }

    // Get all ranks
    public static function getAllRanks()
    {
        // Execute query
        $getRanks = Database::fetch('ranks', true);

        // Define variable
        $ranks = [];

        // Reorder shit
        foreach ($getRanks as $rank) {
            $ranks[$rank['rank_id']] = Rank::construct($rank['rank_id']);
        }

        // and return an array with the ranks
        return $ranks;
    }

    // Get a user's notifications
    public static function getNotifications($uid = null, $timediff = 0, $excludeRead = true, $markRead = false)
    {
        // Prepare conditions
        $conditions = [];
        $conditions['user_id'] = [($uid ? $uid : self::checkLogin()[0]), '='];

        if ($timediff) {
            $conditions['alert_timestamp'] = [time() - $timediff, '>'];
        }

        if ($excludeRead) {
            $conditions['alert_read'] = [0, '='];
        }

        // Get notifications for the database
        $notifications = Database::fetch('notifications', true, $conditions);

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

    // Marking notifications as read
    public static function markNotificationRead($id, $mode = true)
    {
        // Execute an update statement
        Database::update('notifications', [
            [
                'alert_read' => ($mode ? 1 : 0),
            ],
            [
                'alert_id' => [$id, '='],
            ],
        ]);
    }

    // Adding a new notification
    public static function createNotification($user, $title, $text, $timeout = 60000, $img = 'FONT:fa-info-circle', $link = '', $sound = 0)
    {
        // Get current timestamp
        $time = time();

        // Insert it into the database
        Database::insert('notifications', [
            'user_id' => $user,
            'alert_timestamp' => $time,
            'alert_read' => 0,
            'alert_sound' => ($sound ? 1 : 0),
            'alert_title' => $title,
            'alert_text' => $text,
            'alert_link' => $link,
            'alert_img' => $img,
            'alert_timeout' => $timeout,
        ]);
    }

    // Get the ID of the newest user
    public static function getNewestUserId()
    {
        return Database::fetch('users', false, ['rank_main' => [Config::get('restricted_rank_id'), '!=']], ['user_id', true], ['1'])['user_id'];
    }
}
