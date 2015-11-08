<?php
/*
 * User Management
 */

namespace Sakura;

/**
 * Class Users
 * @package Sakura
 */
class Users
{
    // Empty rank template
    public static $emptyRank = [
        'rank_id' => 0,
        'rank_name' => 'Rank',
        'rank_hierarchy' => 0,
        'rank_multiple' => null,
        'rank_hidden' => 1,
        'rank_colour' => '#444',
        'rank_description' => '',
        'rank_title' => '',
    ];

    // Check if a user is logged in
    public static function checkLogin($uid = null, $sid = null)
    {
        // Assign $uid and $sid
        $uid = $uid ? $uid : (isset($_COOKIE[Config::getConfig('cookie_prefix') . 'id'])
            ? $_COOKIE[Config::getConfig('cookie_prefix') . 'id']
            : 0);
        $sid = $sid ? $sid : (isset($_COOKIE[Config::getConfig('cookie_prefix') . 'session'])
            ? $_COOKIE[Config::getConfig('cookie_prefix') . 'session']
            : 0);

        // Get session
        $session = new Session($uid, $sid);

        // Validate the session
        $sessionValid = $session->validate();

        // Check if the session exists and check if the user is activated
        if ($sessionValid == 0 || Permissions::check('SITE', 'DEACTIVATED', $uid, 1)) {
            // Unset User ID
            setcookie(
                Config::getConfig('cookie_prefix') . 'id',
                0,
                time() - 60,
                Config::getConfig('cookie_path'),
                Config::getConfig('cookie_domain')
            );

            // Unset Session ID
            setcookie(
                Config::getConfig('cookie_prefix') . 'session',
                '',
                time() - 60,
                Config::getConfig('cookie_path'),
                Config::getConfig('cookie_domain')
            );

            return false;
        }

        // Extend the cookie times if the remember flag is set
        if ($sessionValid == 2) {
            // User ID cookie
            setcookie(
                Config::getConfig('cookie_prefix') . 'id',
                $uid,
                time() + 604800,
                Config::getConfig('cookie_path'),
                Config::getConfig('cookie_domain')
            );

            // Session ID cookie
            setcookie(
                Config::getConfig('cookie_prefix') . 'session',
                $sid,
                time() + 604800,
                Config::getConfig('cookie_path'),
                Config::getConfig('cookie_domain')
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
        if (Config::getConfig('lock_authentication')) {
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
        $user = new User($uid);

        // Validate password
        switch ($user->password()['password_algo']) {
            // Abyssing
            case 'nologin':
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
        if (Permissions::check('SITE', 'DEACTIVATED', $user->id(), 1)) {
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
                Config::getConfig('cookie_prefix') . 'id',
                $user->id(),
                time() + 604800,
                Config::getConfig('cookie_path'),
                Config::getConfig('cookie_domain')
            );

            // Session ID cookie
            setcookie(
                Config::getConfig('cookie_prefix') . 'session',
                $sessionKey,
                time() + 604800,
                Config::getConfig('cookie_path'),
                Config::getConfig('cookie_domain')
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
            Config::getConfig('cookie_prefix') . 'id',
            0,
            time() - 60,
            Config::getConfig('cookie_path'),
            Config::getConfig('cookie_domain')
        );

        // Unset Session ID
        setcookie(
            Config::getConfig('cookie_prefix') . 'session',
            '',
            time() - 60,
            Config::getConfig('cookie_path'),
            Config::getConfig('cookie_domain')
        );

        // Return true indicating a successful logout
        return true;
    }

    // Register user
    public static function register($username, $password, $confirmpass, $email, $tos, $captcha = null, $regkey = null)
    {
        // Check if authentication is disallowed
        if (Config::getConfig('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Check if registration is even enabled
        if (Config::getConfig('disable_registration')) {
            return [0, 'DISABLED'];
        }

        // Check if registration codes are required
        if (Config::getConfig('require_registration_code')) {
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
        if (Config::getConfig('recaptcha')) {
            if (!Main::verifyCaptcha($captcha)['success']) {
                return [0, 'CAPTCHA_FAIL'];
            }
        }

        // Check if the username already exists
        if (self::userExists($username, false)) {
            return [0, 'USER_EXISTS'];
        }

        // Username too short
        if (strlen($username) < Config::getConfig('username_min_length')) {
            return [0, 'NAME_TOO_SHORT'];
        }

        // Username too long
        if (strlen($username) > Config::getConfig('username_max_length')) {
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
        if (Main::pwdEntropy($password) < Config::getConfig('min_entropy')) {
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
        $requireActive = Config::getConfig('require_activation');
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
        if (Config::getConfig('require_registration_code')) {
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
        if (Config::getConfig('lock_authentication')) {
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

        // Check if the user has the required privs to log in
        if (Permissions::check('SITE', 'DEACTIVATED', $user['user_id'], 1)) {
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
        $message .= "You are receiving this notification because you have (or someone pretending to be you has) requested a password reset link to be sent for your account on \"" . Config::getConfig('sitename') . "\". If you did not request this notification then please ignore it, if you keep receiving it please contact the site administrator.\r\n\r\n";
        $message .= "To use this password reset key you need to go to a special page. To do this click the link provided below.\r\n\r\n";
        $message .= "http://" . Config::getConfig('url_main') . $urls->format('SITE_FORGOT_PASSWORD') . "?pw=true&uid=" . $user['user_id'] . "&key=" . $verk . "\r\n\r\n";
        $message .= "If successful you should be able to change your password here.\r\n\r\n";
        $message .= "Alternatively if the above method fails for some reason you can go to http://" . Config::getConfig('url_main') . $urls->format('SITE_FORGOT_PASSWORD') . "?pw=true&uid=" . $user['user_id'] . " and use the key listed below:\r\n\r\n";
        $message .= "Verification key: " . $verk . "\r\n\r\n";
        $message .= "You can of course change this password yourself via the profile page. If you have any difficulties please contact the site administrator.\r\n\r\n";
        $message .= "--\r\n\r\nThanks\r\n\r\n" . Config::getConfig('mail_signature');

        // Send the message
        Main::sendMail([$user['email'] => $user['username']], Config::getConfig('sitename') . ' password restoration', $message);

        // Return success
        return [1, 'SUCCESS'];
    }

    // Reset password with key
    public static function resetPassword($verk, $uid, $newpass, $verpass)
    {
        // Check if authentication is disallowed
        if (Config::getConfig('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Check password entropy
        if (Main::pwdEntropy($newpass) < Config::getConfig('min_entropy')) {
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
        if (Config::getConfig('lock_authentication')) {
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

        // Check if a user is activated
        if (!Permissions::check('SITE', 'DEACTIVATED', $user['user_id'], 1)) {
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
        $user = Database::fetch('users', false, ['user_id' => [$uid, '=']]);

        // User is already activated or doesn't even exist
        if (count($user) < 2 || !Permissions::check('SITE', 'DEACTIVATED', $user['user_id'], 1)) {
            return false;
        }

        // Generate activation key
        $activate = ($customKey ? $customKey : Main::newActionCode('ACTIVATE', $uid, [
            'user' => [
                'rank_main' => 2,
                'user_ranks' => json_encode([2]),
            ],
        ]));

        // Create new urls object
        $urls = new Urls();

        // Build the e-mail
        $message = "Welcome to " . Config::getConfig('sitename') . "!\r\n\r\n";
        $message .= "Please keep this e-mail for your records. Your account intormation is as follows:\r\n\r\n";
        $message .= "----------------------------\r\n\r\n";
        $message .= "Username: " . $user['username'] . "\r\n\r\n";
        $message .= "Your profile: http://" . Config::getConfig('url_main') . $urls->format('USER_PROFILE', [$user['user_id']]) . "\r\n\r\n";
        $message .= "----------------------------\r\n\r\n";
        $message .= "Please visit the following link in order to activate your account:\r\n\r\n";
        $message .= "http://" . Config::getConfig('url_main') . $urls->format('SITE_ACTIVATE') . "?mode=activate&u=" . $user['user_id'] . "&k=" . $activate . "\r\n\r\n";
        $message .= "Your password has been securely stored in our database and cannot be retrieved. ";
        $message .= "In the event that it is forgotten, you will be able to reset it using the email address associated with your account.\r\n\r\n";
        $message .= "Thank you for registering.\r\n\r\n";
        $message .= "--\r\n\r\nThanks\r\n\r\n" . Config::getConfig('mail_signature');

        // Send the message
        Main::sendMail(
            [
                $user['email'] => $user['username'],
            ],
            Config::getConfig('sitename') . ' Activation Mail',
            $message
        );

        // Return true indicating that the things have been sent
        return true;
    }

    // Activating a user
    public static function activateUser($uid, $requireKey = false, $key = null)
    {
        // Get the user data
        $user = Database::fetch('users', false, ['user_id' => [$uid, '=']]);

        // Check if user exists
        if (!count($user) > 1) {
            return [0, 'USER_NOT_EXIST'];
        }

        // Check if user is already activated
        if (!Permissions::check('SITE', 'DEACTIVATED', $user['user_id'], 1)) {
            return [0, 'USER_ALREADY_ACTIVE'];
        }

        // Set default values for activation
        $rank = 2;
        $ranks = json_encode([2]);

        /* Check if a key is set (there's an option to not set one for user
        management reasons but you can't really get around this anyway) */
        if ($requireKey) {
            // Check the action code
            $action = Main::useActionCode('ACTIVATE', $key, $uid);

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
                'user_id' => [$uid, '='],
            ],
        ]);

        // Return success
        return [1, 'SUCCESS'];
    }

    // Deactivating a user
    public static function deactivateUser($uid)
    {
        // Get the user data
        $user = Database::fetch('users', false, ['user_id' => [$uid, '=']]);

        // Check if user exists
        if (!count($user) > 1) {
            return [0, 'USER_NOT_EXIST'];
        }

        // Check if user is already deactivated
        if (Permissions::check('SITE', 'DEACTIVATED', $user['user_id'], 1)) {
            return [0, 'USER_ALREADY_DEACTIVE'];
        }

        // Deactivate the account
        Database::update('users', [
            [
                'rank_main' => 2,
                'user_ranks' => json_encode([2]),
            ],
            [
                'user_id' => [$uid, '='],
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
        )[0] >= Config::getConfig('max_reg_keys')) {
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

    // Set the default rank of a user
    public static function setDefaultRank($uid, $rid, $userIdIsUserData = false)
    {
        return (new User($uid))->setMainRank($rid);
    }

    // Add a rank to a user
    public static function addRanksToUser($ranks, $uid, $userIdIsUserData = false)
    {
        // Define $current
        $current = [];

        // Go over all the new ranks
        foreach ($ranks as $rank) {
            // Check if the user already has this rank and set it if not
            if (!in_array($rank, $current)) {
                $current[] = (int) $rank;
            }
        }

        // Encode the array
        $current = json_encode($current);

        // Update the row
        Database::update('users', [
            [
                'user_ranks' => $current,
            ],
            [
                'user_id' => [$uid, '='],
            ],
        ]);

        // Return true because
        return true;
    }

    // Removing ranks from a user
    public static function removeRanksFromUser($ranks, $uid, $userIdIsUserData = false)
    {
        // Get the specified user
        $user = new User($uid);

        $current = $user->ranks();

        // Check the current ranks for ranks in the set array
        foreach ($current as $key => $rank) {
            // Unset the rank
            if (in_array($rank, $user->ranks())) {
                unset($current[$key]);
            }
        }

        // Encode the array
        $current = json_encode($current);

        // Update the row
        Database::update('users', [
            [
                'user_ranks' => $current,
            ],
            [
                'user_id' => [$uid, '='],
            ],
        ]);

        // Return true
        return true;
    }

    // Check if a user has these ranks
    public static function checkIfUserHasRanks($ranks, $userid, $userIdIsUserData = false)
    {
        return (new User($userid))->checkIfUserHasRanks($ranks);
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

        // Iterate over the fields and clean them up
        foreach ($optionFields as $field) {
            if (!Permissions::check('SITE', $field['option_permission'], self::checkLogin()[0], 1)) {
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
        $time = time() - Config::getConfig('max_online_time');

        $return = [];

        // Get all online users in the past 5 minutes
        $getAll = Database::fetch('users', true, ['user_last_online' => [$time, '>']]);

        foreach ($getAll as $user) {
            $return[] = new User($user['user_id']);
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

    // Remove the premium status of a user
    public static function removeUserPremium($id)
    {
        Database::delete('premium', [
            'user_id' => [$id, '='],
        ]);
    }

    // Check if user has Premium
    public static function checkUserPremium($id)
    {
        // Check if the user has static premium
        if (Permissions::check('SITE', 'STATIC_PREMIUM', $id, 1)) {
            return [2, 0, time() + 1];
        }

        // Attempt to retrieve the premium record from the database
        $getRecord = Database::fetch('premium', false, [
            'user_id' => [$id, '='],
        ]);

        // If nothing was returned just return false
        if (empty($getRecord)) {
            return [0];
        }

        // Check if the Tenshi hasn't expired
        if ($getRecord['premium_expire'] < time()) {
            self::removeUserPremium($id);
            self::updatePremiumMeta($id);
            return [0, $getRecord['premium_start'], $getRecord['premium_expire']];
        }

        // Else return the start and expiration date
        return [1, $getRecord['premium_start'], $getRecord['premium_expire']];
    }

    // Update the premium data
    public static function updatePremiumMeta($id)
    {
        // Get the ID for the premium user rank from the database
        $premiumRank = Config::getConfig('premium_rank_id');

        // Run the check
        $check = self::checkUserPremium($id);

        // Check if the user has premium
        if ($check[0] == 1) {
            // If so add the rank to them
            self::addRanksToUser([$premiumRank], $id);

            // Check if the user's default rank is standard user and update it to premium
            if (((new User($id))->mainRank()) == 2) {
                self::setDefaultRank($id, $premiumRank);
            }
        } elseif ($check[0] == 0 && count($check) > 1) {
            // Else remove the rank from them
            self::removeRanksFromUser([$premiumRank], $id);
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
            if (self::checkIfUserHasRanks([$rankId], $user->id())
                && ($excludeAbyss ? $user->password()['password_algo'] != 'nologin' : true)) {
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
            // Skip abyss
            if (!$includeAbyss && $user['password_algo'] == 'nologin') {
                continue;
            }

            // Skip if inactive and not include deactivated users
            if (!$includeInactive && Permissions::check('SITE', 'DEACTIVATED', $user['user_id'], 1)) {
                continue;
            }

            $users[$user['user_id']] = new User($user['user_id']);
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
            $ranks[$rank['rank_id']] = new Rank($rank['rank_id']);
        }

        // and return an array with the ranks
        return $ranks;
    }

    // Get all warnings issued to a user (or all warnings a user issued)
    public static function getWarnings($uid = 0, $iid = false)
    {
        // Do the database query
        $warnings = Database::fetch('warnings', true, ($uid ? [
            ($iid ? 'moderator_id' : 'user_id') => [$uid, '='],
        ] : null));

        // Return all the warnings
        return $warnings;
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

    // Getting a user's PMs
    public static function getPrivateMessages($from = false)
    {
        return [];
    }

    // Get friends
    public static function getFriends($uid = null, $timestamps = false, $getData = false, $checkOnline = false)
    {
        // Assign $uid
        if (!$uid) {
            $uid = Users::checkLogin()[0];
        }

        // Get all friends
        $getFriends = Database::fetch('friends', true, [
            'user_id' => [$uid, '='],
        ]);

        // Create the friends array
        $friends = [];

        // Iterate over the raw database return
        foreach ($getFriends as $key => $friend) {
            // Add friend to array
            $friends[($timestamps ? $friend['friend_id'] : $key)] = $getData ? ([

                'user' => ($_UDATA = new User($friend['friend_id'])),
                'rank' => new Rank($_UDATA->mainRank()),

            ]) : $friend[($timestamps ? 'friend_timestamp' : 'friend_id')];
        }

        // Check who is online and who isn't
        if ($checkOnline) {
            // Check each user
            foreach ($friends as $key => $friend) {
                $friends[
                    (new User($getData ? $friend['user']->id() : $friend))->checkOnline() ? 'online' : 'offline'
                ][] = $friend;
            }
        }

        // Return formatted array
        return $friends;
    }

    // Get non-mutual friends
    public static function getPendingFriends($uid = null, $getData = false)
    {
        // Assign $of automatically if it's not set
        if (!$uid) {
            $uid = self::checkLogin()[0];
        }

        // Get all friend entries from other people involved the current user
        $friends = Database::fetch('friends', true, [
            'friend_id' => [$uid, '='],
        ]);

        // Create pending array
        $pending = [];

        // Check if the friends are mutual
        foreach ($friends as $friend) {
            // Create user object
            $user = new User($uid);

            // Check if the friend is mutual
            if (!$user->checkFriends($friend['user_id'])) {
                $pending[] = $getData ? ([

                    'user' => ($_UDATA = new User($friend['user_id'])),
                    'rank' => new Rank($_UDATA->mainRank()),

                ]) : $friend;
            }
        }

        // Return the pending friends
        return $pending;
    }

    // Get the ID of the newest user
    public static function getNewestUserId()
    {
        return Database::fetch('users', false, ['password_algo' => ['nologin', '!=']], ['user_id', true], ['1'])['user_id'];
    }
}
