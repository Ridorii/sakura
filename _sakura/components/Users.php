<?php
/*
 * User Management
 */

namespace Sakura;

class Users {

    // Empty user template
    public static $emptyUser = [
        'id'                => 0,
        'username'          => 'Deleted User',
        'username_clean'    => 'deleted user',
        'password_hash'     => '',
        'password_salt'     => '',
        'password_algo'     => 'nologin',
        'password_iter'     => 1000,
        'password_chan'     => 0,
        'password_new'      => '',
        'email'             => 'deleted@flashii.net',
        'rank_main'         => 0,
        'ranks'             => '[0]',
        'name_colour'       => '',
        'register_ip'       => '127.0.0.1',
        'last_ip'           => '127.0.0.1',
        'usertitle'         => 'Non-existent user account',
        'profile_md'        => '',
        'avatar_url'        => '',
        'background_url'    => '',
        'regdate'           => 0,
        'lastdate'          => 0,
        'lastunamechange'   => 0,
        'birthday'          => '',
        'profile_data'      => '[]'
    ];

    // Empty rank template
    public static $emptyRank = [
        'id'            => 0,
        'rankname'      => 'Non-existent Rank',
        'multi'         => 0,
        'colour'        => '#444',
        'description'   => 'A hardcoded dummy rank for fallback.'
    ];

    // Check if a user is logged in
    public static function checkLogin() {

        // Check if the cookies are set
        if(
            !isset($_COOKIE[Configuration::getConfig('cookie_prefix') .'id']) ||
            !isset($_COOKIE[Configuration::getConfig('cookie_prefix') .'session'])
        )
            return false;

        // Check if the session exists
        if(!$session = Session::checkSession(
            Session::$userId,
            Session::$sessionId
        ))
            return false;

        // Extend the cookie times if the remember flag is set
        if($session == 2) {

            setcookie(Configuration::getConfig('cookie_prefix') .'id',      Session::$userId,       time() + 604800, Configuration::getConfig('cookie_path'), Configuration::getConfig('cookie_domain'));
            setcookie(Configuration::getConfig('cookie_prefix') .'session', Session::$sessionId,    time() + 604800, Configuration::getConfig('cookie_path'), Configuration::getConfig('cookie_domain'));

        }

        // If everything went through return true
        return true;

    }

    // Log a user in
    public static function login($username, $password, $remember = false) {

        // Check if the user that's trying to log in actually exists
        if(!$uid = self::userExists($username, false))
            return [0, 'USER_NOT_EXIST'];

        // Get account data
        $userData = self::getUser($uid);

        // Validate password
        if($userData['password_algo'] == 'nologin') { // Disable logging in to an account

            return [0, 'NO_LOGIN'];

        } elseif($userData['password_algo'] == 'legacy') { // Shitty legacy method of sha512(strrev(sha512()))

            if(Main::legacyPasswordHash($password) != $userData['password_hash'])
                return [0, 'INCORRECT_PASSWORD'];

        } else { // PBKDF2 hashing

            if(!Hashing::validate_password($password, [
                $userData['password_algo'],
                $userData['password_iter'],
                $userData['password_salt'],
                $userData['password_hash']
            ]))
                return [0, 'INCORRECT_PASSWORD'];

        }

        // Check if the user is deactivated
        if(in_array(0, json_decode($userData['ranks'], true)))
            return [0, 'DEACTIVATED'];

        // Create a new session
        $sessionKey = Session::newSession($userData['id'], $remember);

        // Set cookies
        setcookie(Configuration::getConfig('cookie_prefix') .'id',      $userData['id'],    time() + 604800, Configuration::getConfig('cookie_path'), Configuration::getConfig('cookie_domain'));
        setcookie(Configuration::getConfig('cookie_prefix') .'session', $sessionKey,        time() + 604800, Configuration::getConfig('cookie_path'), Configuration::getConfig('cookie_domain'));

        // Successful login! (also has a thing for the legacy password system)
        return [1, ($userData['password_algo'] == 'legacy' ? 'LEGACY_SUCCESS' : 'LOGIN_SUCESS')];

    }

    // Logout and kill the session
    public static function logout() {

        // Check if user is logged in
        if(!self::checkLogin())
            return false;

        // Remove the active session from the database
        if(!Session::deleteSession(Session::$sessionId, true))
            return false;

        // Set cookies
        setcookie(Configuration::getConfig('cookie_prefix') .'id',      0,  time() - 60, Configuration::getConfig('cookie_path'), Configuration::getConfig('cookie_domain'));
        setcookie(Configuration::getConfig('cookie_prefix') .'session', '', time() - 60, Configuration::getConfig('cookie_path'), Configuration::getConfig('cookie_domain'));

        // Return true indicating a successful logout
        return true;

    }

    // Register user
    public static function register($username, $password, $confirmpass, $email, $tos, $captcha = null, $regkey = null) {

        // Check if registration is even enabled
        if(Configuration::getConfig('disable_registration'))
            return [0, 'DISABLED'];

        // Check if registration codes are required
        if(Configuration::getConfig('require_registration_code')) {

            // Check if the code is valid
            if(!self::checkRegistrationCode($regkey))
                return [0, 'INVALID_REG_KEY'];

        }

        // Check if the user agreed to the ToS
        if(!$tos)
            return [0, 'TOS'];

        // Verify the captcha if it's enabled
        if(Configuration::getConfig('recaptcha')) {

            if(!Main::verifyCaptcha($captcha)['success'])
                return [0, 'CAPTCHA_FAIL'];

        }

        // Check if the username already exists
        if(self::userExists($username, false))
            return [0, 'USER_EXISTS'];

        // Username too short
        if(strlen($username) < 3)
            return [0, 'NAME_TOO_SHORT'];

        // Username too long
        if(strlen($username) > 16)
            return [0, 'NAME_TOO_LONG'];

        // Password too short
        if(strlen($password) < 8)
            return [0, 'PASS_TOO_SHORT'];

        // Password too long
        if(strlen($password) > 256)
            return [0, 'PASS_TOO_LONG'];

        // Passwords do not match
        if($password != $confirmpass)
            return [0, 'PASS_NOT_MATCH'];

        // Check if the given email address is formatted properly
        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            return [0, 'INVALID_EMAIL'];

        // Check the MX record of the email
        if(!Main::checkMXRecord($email))
            return [0, 'INVALID_MX'];

        // Set a few variables
        $usernameClean  = Main::cleanString($username, true);
        $password       = Hashing::create_hash($password);
        $requireActive  = Configuration::getConfig('require_activation');
        $userRank       = $requireActive ? [0] : [1];
        $userRankJson   = json_encode($userRank);

        // Insert the user into the database
        Database::insert('users', [
            'username'          => $username,
            'username_clean'    => $usernameClean,
            'password_hash'     => $password[3],
            'password_salt'     => $password[2],
            'password_algo'     => $password[0],
            'password_iter'     => $password[1],
            'email'             => $email,
            'rank_main'         => $userRank[0],
            'ranks'             => $userRankJson,
            'register_ip'       => Main::getRemoteIP(),
            'last_ip'           => Main::getRemoteIP(),
            'regdate'           => time(),
            'lastdate'          => 0,
            'lastunamechange'   => time(),
            'country'           => Main::getCountryCode(),
            'profile_data'      => '[]'
        ]);

        // Get userid of the new user
        $uid = Database::fetch('users', false, ['username_clean' => [$usernameClean, '=']])['id'];

        // Check if we require e-mail activation
        if($requireActive) {

            // Send activation e-mail to user
            self::sendActivationMail($uid);

        }

        // Check if registration codes are required
        if(Configuration::getConfig('require_registration_code')) {

            // If we do mark the registration code that was used as used
            self::markRegistrationCodeUsed($regkey, $uid);

        }

        // Return true with a specific message if needed
        return [1, ($requireActive ? 'EMAILSENT' : 'SUCCESS')];

    }

    // Send the activation e-mail and do other required stuff
    public static function sendActivationMail($uid, $customKey = null) {

        // Get the user data
        $user = Database::fetch('users', false, ['id' => [$uid, '=']]);

        // User is already activated or doesn't even exist
        if(!count($user) > 1 || $user['rank_main'])
            return false;

        // Generate activation key
        $activate = ($customKey ? $customKey : Main::newActionCode('ACTIVATE', $uid, [
            'user' => [
                'rank_main' => 1,
                'ranks'     => json_encode([1])
            ]
        ]));

        // Build the e-mail
        $message  = "Welcome to ". Configuration::getConfig('sitename') ."!\r\n\r\n";
        $message .= "Please keep this e-mail for your records. Your account intormation is as follows:\r\n\r\n";
        $message .= "----------------------------\r\n\r\n";
        $message .= "Username: ". $user['username'] ."\r\n\r\n";
        $message .= "Your profile: http://". Configuration::getLocalConfig('urls', 'main') ."/u/". $user['id'] ."\r\n\r\n";
        $message .= "----------------------------\r\n\r\n";
        $message .= "Please visit the following link in order to activate your account:\r\n\r\n";
        $message .= "http://". Configuration::getLocalConfig('urls', 'main') ."/activate?mode=activate&u=". $user['id'] ."&k=". $activate ."\r\n\r\n";
        $message .= "Your password has been securely stored in our database and cannot be retrieved. ";
        $message .= "In the event that it is forgotten, you will be able to reset it using the email address associated with your account.\r\n\r\n";
        $message .= "Thank you for registering.\r\n\r\n";
        $message .= "--\r\n\r\nSincerely\r\n\r\n". Configuration::getConfig('mail_signature');

        // Send the message
        Main::sendMail([$user['email'] => $user['username']], Configuration::getConfig('sitename') .' Activation Mail', $message);

        // Return true indicating that the things have been sent
        return true;

    }

    // Activating a user
    public static function activateUser($uid, $requireKey = false, $key = null) {

        // Get the user data
        $user = Database::fetch('users', false, ['id' => [$uid, '=']]);

        // Check if user exists
        if(!count($user) > 1)
            return [0, 'USER_NOT_EXIST'];

        // Check if user is already activated
        if($user['rank_main'])
            return [0, 'USER_ALREADY_ACTIVE'];

        // Set default values for activation
        $rank = 1;
        $ranks = json_encode([1]);

        // Check if a key is set (there's an option to not set one for user management reasons but you can't really get around this anyway)
        if($requireKey) {

            // Check the action code
            $action = Main::useActionCode('ACTIVATE', $key, $uid);

            // Check if we got a negative return
            if(!$action[0])
                return [0, $action[1]];

            // Assign the special values
            $instructionData    = json_decode($action[2], true);
            $rank               = $instructionData['user']['rank_main'];
            $ranks              = $instructionData['user']['ranks'];

        }

        // Activate the account
        Database::update('users', [
            [
                'rank_main' => $rank,
                'ranks'     => $ranks
            ],
            [
                'id' => [$uid, '=']
            ]
        ]);

        // Return success
        return [1, 'SUCCESS'];

    }

    // Deactivating a user
    public static function deactivateUser($uid) {

        // Get the user data
        $user = Database::fetch('users', false, ['id' => [$uid, '=']]);

        // Check if user exists
        if(!count($user) > 1)
            return [0, 'USER_NOT_EXIST'];

        // Check if user is already deactivated
        if(!$user['rank_main'])
            return [0, 'USER_ALREADY_DEACTIVE'];

        // Deactivate the account
        Database::update('users', [
            [
                'rank_main' => 0,
                'ranks'     => json_encode([0])
            ],
            [
                'id' => [$uid, '=']
            ]
        ]);

        // Return success
        return [1, 'SUCCESS'];

    }

    // Check if registration code is valid
    public static function checkRegistrationCode($code) {

        // Get registration key
        $keyRow = Database::fetch('regcodes', true, ['code' => [$code, '='], 'key_used' => [0, '=']]);

        // Check if it exists and return it
        return count($keyRow) ? $keyRow[0]['id'] : false;

    }

    // Mark registration code as used
    public static function markRegistrationCodeUsed($code, $uid = 0) {

        // Check if the code exists
        if(!$id = self::checkRegistrationCode($code))
            return false;

        // Mark it as used
        Database::update('regcodes', [
            [
                'used_by'   => $uid,
                'key_used'  => 1
            ],
            [
                'id' => [$id, '=']
            ]
        ]);

        // Return true because yeah
        return true;

    }

    // Create new registration code
    public static function createRegistrationCode() {

        // Check if we're logged in
        if(!self::checkLogin())
            return false;

        // Check if the user is not exceeding the maximum registration key amount
        if(count(Database::fetch('regcodes', true, ['uid' => [Session::$userId, '=']])) >= Configuration::getConfig('max_reg_keys'))
            return false;

        // Generate a code by MD5'ing some random bullshit
        $code = md5('SAKURA'. rand(0, 99999999) . Session::$userId .'NOOKLSISGOD');

        // Insert the key into the database
        Database::insert('regcodes', [
            'code'          => $code,
            'created_by'    => Session::$userId,
            'used_by'       => 0,
            'key_used'      => 0
        ]);

        // Return the code
        return $code;

    }

    // Check if a user exists
    public static function userExists($user, $id = true) {

        // Clean string
        $user = Main::cleanString($user, true);

        // Do database request
        $user = Database::fetch('users', true, [($id ? 'id' : 'username_clean') => [$user, '=']]);

        // Return count (which would return 0, aka false, if nothing was found)
        return count($user) ? $user[0]['id'] : false;

    }

    // Get user data by id
    public static function getUser($id) {

        // Execute query
        $user = Database::fetch('users', false, ['id' => [$id, '=']]);

        // Return false if no user was found
        if(empty($user))
            return self::$emptyUser;

        // If user was found return user data
        return $user;

    }

    // Get rank data by id
    public static function getRank($id) {

        // Execute query
        $rank = Database::fetch('ranks', false, ['id' => [$id, '=']]);

        // Return false if no rank was found
        if(empty($rank))
            return self::$emptyRank;

        // If rank was found return rank data
        return $rank;

    }

    // Get user(s) by IP
    public static function getUsersByIP($ip) {

        // Get users by registration IP
        $registeredFrom = Database::fetch('users', true, ['register_ip' => [$ip, '=']]);

        // Get users by last IP
        $lastFrom = Database::fetch('users', true, ['last_ip' => [$ip, '='], 'register_ip' => [$ip, '!=']]);

        // Merge the arrays
        $users = array_merge($registeredFrom, $lastFrom);

        // Return the array with users
        return $users;

    }

    // Get all users
    public static function getAllUsers($includeInactive = true) {

        // Execute query
        $getUsers = Database::fetch('users', true);

        // Reorder shit
        foreach($getUsers as $user) {

            // Skip if inactive and not include deactivated users
            if(!$includeInactive && $user['rank_main'] == 0)
                continue;

            $users[$user['id']] = $user;

        }

        // and return an array with the users
        return $users;

    }

    // Get all ranks
    public static function getAllRanks() {

        // Execute query
        $getRanks = Database::fetch('ranks', true);

        // Reorder shit
        foreach($getRanks as $rank)
            $ranks[$rank['id']] = $rank;

        // and return an array with the ranks
        return $ranks;

    }

}
