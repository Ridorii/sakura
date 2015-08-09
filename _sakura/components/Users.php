<?php
/*
 * User Management
 */

namespace Sakura;

class Users {

    // Empty user template
    public static $emptyUser = [
        'id'                => 0,
        'username'          => 'Sakura User',
        'username_clean'    => 'sakura user',
        'password_hash'     => '',
        'password_salt'     => '',
        'password_algo'     => 'nologin',
        'password_iter'     => 1000,
        'password_chan'     => 0,
        'password_new'      => '',
        'email'             => 'sakura@localhost',
        'rank_main'         => 0,
        'ranks'             => '[0]',
        'name_colour'       => '',
        'register_ip'       => '127.0.0.1',
        'last_ip'           => '127.0.0.1',
        'usertitle'         => 'Internal fallback account',
        'regdate'           => 0,
        'lastdate'          => 0,
        'lastunamechange'   => 0,
        'birthday'          => '',
        'posts'             => 0,
        'country'           => 'EU',
        'userData'          => '[]'
    ];

    // Empty rank template
    public static $emptyRank = [
        'id'            => 0,
        'rankname'      => 'Sakura Rank',
        'multi'         => 0,
        'colour'        => '#444',
        'description'   => 'A hardcoded dummy rank for fallback.'
    ];

    // Check if a user is logged in
    public static function checkLogin($uid = null, $sid = null, $bypassCookies = false) {

        // Set $uid and $sid if they're null
        if($uid == null)
            $uid = Session::$userId;

        // ^
        if($sid == null)
            $sid = Session::$sessionId;

        // Check if cookie bypass is false
        if(!$bypassCookies) {

            // Check if the cookies are set
            if(!isset($_COOKIE[Configuration::getConfig('cookie_prefix') .'id']) || !isset($_COOKIE[Configuration::getConfig('cookie_prefix') .'session']))
                return false;

        }

        // Check if the session exists
        if(!$session = Session::checkSession($uid, $sid))
            return false;

        // Check if the user is activated
        if(Permissions::check('SITE', 'DEACTIVATED', $uid, 1))
            return false;

        // Extend the cookie times if the remember flag is set
        if($session == 2 && !$bypassCookies) {

            setcookie(Configuration::getConfig('cookie_prefix') .'id',      $uid,   time() + 604800, Configuration::getConfig('cookie_path'), Configuration::getConfig('cookie_domain'));
            setcookie(Configuration::getConfig('cookie_prefix') .'session', $sid,   time() + 604800, Configuration::getConfig('cookie_path'), Configuration::getConfig('cookie_domain'));

        }

        // Update last online
        Database::update('users', [
            [
                'lastdate' => time()
            ],
            [
                'id' => [$uid, '=']
            ]
        ]);

        // Update the premium meta
        Users::updatePremiumMeta($uid);

        // Redirect people that need to change their password to the new format
        if(self::getUser($uid)['password_algo'] == 'legacy' && $_SERVER['PHP_SELF'] != '/authenticate.php' && $_SERVER['PHP_SELF'] != '/imageserve.php')
            header('Location: /authenticate.php?legacy=true');

        // If everything went through return true
        return true;

    }

    // Log a user in
    public static function login($username, $password, $remember = false, $cookies = true) {

        // Check if authentication is disallowed
        if(Configuration::getConfig('lock_authentication'))
            return [0, 'AUTH_LOCKED'];

        // Check if the user that's trying to log in actually exists
        if(!$uid = self::userExists($username, false))
            return [0, 'USER_NOT_EXIST'];

        // Get account data
        $user = self::getUser($uid);

        // Validate password
        if($user['password_algo'] == 'nologin') { // Disable logging in to an account

            return [0, 'NO_LOGIN'];

        } elseif($user['password_algo'] == 'legacy') { // Shitty legacy method of sha512(strrev(sha512()))

            if(Main::legacyPasswordHash($password) != $user['password_hash'])
                return [0, 'INCORRECT_PASSWORD'];

        } else { // PBKDF2 hashing

            if(!Hashing::validate_password($password, [
                $user['password_algo'],
                $user['password_iter'],
                $user['password_salt'],
                $user['password_hash']
            ]))
                return [0, 'INCORRECT_PASSWORD', $user['password_chan']];

        }

        // Check if the user has the required privs to log in
        if(Permissions::check('SITE', 'DEACTIVATED', $user['id'], 1))
            return [0, 'NOT_ALLOWED'];

        // Create a new session
        $sessionKey = Session::newSession($user['id'], $remember);

        // Set cookies
        if($cookies) {

            setcookie(Configuration::getConfig('cookie_prefix') .'id',      $user['id'],    time() + 604800, Configuration::getConfig('cookie_path'), Configuration::getConfig('cookie_domain'));
            setcookie(Configuration::getConfig('cookie_prefix') .'session', $sessionKey,    time() + 604800, Configuration::getConfig('cookie_path'), Configuration::getConfig('cookie_domain'));

        }

        // Successful login! (also has a thing for the legacy password system)
        return [1, ($user['password_algo'] == 'legacy' ? 'LEGACY_SUCCESS' : 'LOGIN_SUCESS')];

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

        // Check if authentication is disallowed
        if(Configuration::getConfig('lock_authentication'))
            return [0, 'AUTH_LOCKED'];

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
        if(strlen($username) < Configuration::getConfig('username_min_length'))
            return [0, 'NAME_TOO_SHORT'];

        // Username too long
        if(strlen($username) > Configuration::getConfig('username_max_length'))
            return [0, 'NAME_TOO_LONG'];

        // Check if the given email address is formatted properly
        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            return [0, 'INVALID_EMAIL'];

        // Check the MX record of the email
        if(!Main::checkMXRecord($email))
            return [0, 'INVALID_MX'];

        // Check password entropy
        if(Main::pwdEntropy($password) < Configuration::getConfig('min_entropy'))
            return [0, 'PASS_TOO_SHIT'];

        // Passwords do not match
        if($password != $confirmpass)
            return [0, 'PASS_NOT_MATCH'];

        // Set a few variables
        $usernameClean  = Main::cleanString($username, true);
        $emailClean     = Main::cleanString($email, true);
        $password       = Hashing::create_hash($password);
        $requireActive  = Configuration::getConfig('require_activation');
        $userRank       = $requireActive ? [1] : [2];
        $userRankJson   = json_encode($userRank);

        // Insert the user into the database
        Database::insert('users', [
            'username'          => $username,
            'username_clean'    => $usernameClean,
            'password_hash'     => $password[3],
            'password_salt'     => $password[2],
            'password_algo'     => $password[0],
            'password_iter'     => $password[1],
            'email'             => $emailClean,
            'rank_main'         => $userRank[0],
            'ranks'             => $userRankJson,
            'register_ip'       => Main::getRemoteIP(),
            'last_ip'           => Main::getRemoteIP(),
            'regdate'           => time(),
            'lastdate'          => 0,
            'lastunamechange'   => time(),
            'country'           => Main::getCountryCode(),
            'userData'          => '[]'
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

    // Check if a user exists and then send the password forgot email
    public static function sendPasswordForgot($username, $email) {

        // Check if authentication is disallowed
        if(Configuration::getConfig('lock_authentication'))
            return [0, 'AUTH_LOCKED'];

        // Clean username string
        $usernameClean  = Main::cleanString($username, true);
        $emailClean     = Main::cleanString($email, true);

        // Do database request
        $user = Database::fetch('users', false, [
            'username_clean'    => [$usernameClean, '='],
            'email'             => [$emailClean,    '=']
        ]);

        // Check if user exists
        if(count($user) < 2)
            return [0, 'USER_NOT_EXIST'];

        // Check if the user has the required privs to log in
        if(Permissions::check('SITE', 'DEACTIVATED', $user['id'], 1))
            return [0, 'NOT_ALLOWED'];

        // Generate the verification key
        $verk = Main::newActionCode('LOST_PASS', $user['id'], [
            'meta' => [
                'password_change' => 1
            ]
        ]);

        // Build the e-mail
        $message  = "Hello ". $user['username'] .",\r\n\r\n";
        $message .= "You are receiving this notification because you have (or someone pretending to be you has) requested a password reset link to be sent for your account on \"". Configuration::getConfig('sitename') ."\". If you did not request this notification then please ignore it, if you keep receiving it please contact the site administrator.\r\n\r\n";
        $message .= "To use this password reset key you need to go to a special page. To do this click the link provided below.\r\n\r\n";
        $message .= "http://". Configuration::getLocalConfig('urls', 'main') ."/forgotpassword?pw=true&uid=". $user['id'] ."&key=". $verk ."\r\n\r\n";
        $message .= "If successful you should be able to change your password here.\r\n\r\n";
        $message .= "Alternatively if the above method fails for some reason you can go to http://". Configuration::getLocalConfig('urls', 'main') ."/forgotpassword?pw=true&uid=". $user['id'] ." and use the key listed below:\r\n\r\n";
        $message .= "Verification key: ". $verk ."\r\n\r\n";
        $message .= "You can of course change this password yourself via the profile page. If you have any difficulties please contact the site administrator.\r\n\r\n";
        $message .= "--\r\n\r\nThanks\r\n\r\n". Configuration::getConfig('mail_signature');

        // Send the message
        Main::sendMail([$user['email'] => $user['username']], Configuration::getConfig('sitename') .' password restoration', $message);

        // Return success
        return [1, 'SUCCESS'];

    }

    // [Flashwave 2015-04-25] Prepare for 5 million password changing functions

    // Change legacy passwords after logging in
    public static function changeLegacy($oldpass, $newpass, $verpass) {

        // Check if user is logged in because I just know someone is going to meme around it
        if(!self::checkLogin())
            return [0, 'USER_NOT_LOGIN'];

        // Get user data
        $user = Users::getUser(Session::$userId);

        // Check if the user has the required privs to log in
        if(Permissions::check('SITE', 'DEACTIVATED', $user['id'], 1))
            return [0, 'NOT_ALLOWED'];

        // Check if the account is disabled
        if('nologin' == $user['password_algo'])
            return [0, 'NO_LOGIN'];

        // Check if old pass is correct
        if(Main::legacyPasswordHash($oldpass) != $user['password_hash'])
            return [0, 'INCORRECT_PASSWORD'];

        // Check password entropy
        if(Main::pwdEntropy($newpass) < Configuration::getConfig('min_entropy'))
            return [0, 'PASS_TOO_SHIT'];

        // Passwords do not match
        if($newpass != $verpass)
            return [0, 'PASS_NOT_MATCH'];

        // Hash the password
        $password   = Hashing::create_hash($newpass);
        $time       = time();

        // Update the user
        Database::update('users', [
            [
                'password_hash' => $password[3],
                'password_salt' => $password[2],
                'password_algo' => $password[0],
                'password_iter' => $password[1],
                'password_chan' => $time
            ],
            [
                'id' => [Session::$userId, '=']
            ]
        ]);

        // Return success
        return [1, 'SUCCESS'];

    }

    // Reset password with key
    public static function resetPassword($verk, $uid, $newpass, $verpass) {

        // Check if authentication is disallowed
        if(Configuration::getConfig('lock_authentication'))
            return [0, 'AUTH_LOCKED'];

        // Check password entropy
        if(Main::pwdEntropy($newpass) < Configuration::getConfig('min_entropy'))
            return [0, 'PASS_TOO_SHIT'];

        // Passwords do not match
        if($newpass != $verpass)
            return [0, 'PASS_NOT_MATCH'];

        // Check the verification key
        $action = Main::useActionCode('LOST_PASS', $verk, $uid);

        // Check if we got a negative return
        if(!$action[0])
            return [0, $action[1]];

        // Hash the password
        $password   = Hashing::create_hash($newpass);
        $time       = time();

        // Update the user
        Database::update('users', [
            [
                'password_hash' => $password[3],
                'password_salt' => $password[2],
                'password_algo' => $password[0],
                'password_iter' => $password[1],
                'password_chan' => $time
            ],
            [
                'id' => [$uid, '=']
            ]
        ]);

        // Return success
        return [1, 'SUCCESS'];

    }

    // Check if a user exists and then resend the activation e-mail
    public static function resendActivationMail($username, $email) {

        // Check if authentication is disallowed
        if(Configuration::getConfig('lock_authentication'))
            return [0, 'AUTH_LOCKED'];

        // Clean username string
        $usernameClean  = Main::cleanString($username, true);
        $emailClean     = Main::cleanString($email, true);

        // Do database request
        $user = Database::fetch('users', false, [
            'username_clean'    => [$usernameClean, '='],
            'email'             => [$emailClean,    '=']
        ]);

        // Check if user exists
        if(count($user) < 2)
            return [0, 'USER_NOT_EXIST'];

        // Check if a user is activated
        if(!Permissions::check('SITE', 'DEACTIVATED', $user['id'], 1))
            return [0, 'USER_ALREADY_ACTIVE'];

        // Send activation e-mail
        self::sendActivationMail($user['id']);

        // Return success
        return [1, 'SUCCESS'];

    }

    // Send the activation e-mail and do other required stuff
    public static function sendActivationMail($uid, $customKey = null) {

        // Get the user data
        $user = Database::fetch('users', false, ['id' => [$uid, '=']]);

        // User is already activated or doesn't even exist
        if(count($user) < 2 || !Permissions::check('SITE', 'DEACTIVATED', $user['id'], 1))
            return false;

        // Generate activation key
        $activate = ($customKey ? $customKey : Main::newActionCode('ACTIVATE', $uid, [
            'user' => [
                'rank_main' => 2,
                'ranks'     => json_encode([2])
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
        $message .= "--\r\n\r\nThanks\r\n\r\n". Configuration::getConfig('mail_signature');

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
        if(!Permissions::check('SITE', 'DEACTIVATED', $user['id'], 1))
            return [0, 'USER_ALREADY_ACTIVE'];

        // Set default values for activation
        $rank   = 2;
        $ranks  = json_encode([2]);

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
        if(Permissions::check('SITE', 'DEACTIVATED', $user['id'], 1))
            return [0, 'USER_ALREADY_DEACTIVE'];

        // Deactivate the account
        Database::update('users', [
            [
                'rank_main' => 2,
                'ranks'     => json_encode([2])
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

    // Set the default rank of a user
    public static function setDefaultRank($uid, $rid, $userIdIsUserData = false) {

        // Get the specified user
        $user = $userIdIsUserData ? $uid : self::getUser($uid);

        // Decode the json
        $ranks = json_decode($user['ranks'], true);

        // Check if the rank we're trying to set is actually there
        if(!in_array($rid, $ranks))
            return false;

        // Update the row
        Database::update('users', [
            [
                'rank_main' => $rid
            ],
            [
                'id' => [$uid, '=']
            ]
        ]);

        // Return true if everything was successful
        return true;

    }

    // Add a rank to a user
    public static function addRanksToUser($ranks, $uid, $userIdIsUserData = false) {

        // Get the specified user
        $user = $userIdIsUserData ? $uid : self::getUser($uid);

        // Decode the array
        $current = json_decode($user['ranks'], true);

        // Go over all the new ranks
        foreach($ranks as $rank) {

            // Check if the user already has this rank and set it if not
            if(!in_array($rank, $current))
                $current[] = (int)$rank;

        }

        // Encode the array
        $current = json_encode($current);

        // Update the row
        Database::update('users', [
            [
                'ranks' => $current
            ],
            [
                'id' => [$uid, '=']
            ]
        ]);

        // Return true because
        return true;

    }

    // Removing ranks from a user
    public static function removeRanksFromUser($ranks, $uid, $userIdIsUserData = false) {

        // Get the specified user
        $user = $userIdIsUserData ? $uid : self::getUser($uid);

        // Get the ranks
        $current = json_decode($user['ranks'], true);

        // Check the current ranks for ranks in the set array
        foreach($current as $key => $rank) {

            // Unset the rank
            if(in_array($rank, $ranks))
                unset($current[$key]);

        }

        // Encode the array
        $current = json_encode($current);

        // Update the row
        Database::update('users', [
            [
                'ranks' => $current
            ],
            [
                'id' => [$uid, '=']
            ]
        ]);

        // Return true
        return true;

    }

    // Check if a user has these ranks
    public static function checkIfUserHasRanks($ranks, $userid, $userIdIsUserData = false) {

        // Get the specified user
        $user = $userIdIsUserData ? $userid : self::getUser($userid);

        // Check if the main rank is the specified rank
        if(in_array($user['rank_main'], $ranks))
            return true;

        // Decode the json for the user's ranks
        $uRanks = json_decode($user['ranks'], true);

        // If not go over all ranks and check if the user has them
        foreach($ranks as $rank) {

            // We check if $rank is in $user['ranks'] and if yes return true
            if(in_array($rank, $uRanks))
                return true;

        }

        // If all fails return false
        return false;

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

    // Get the available profile fields
    public static function getProfileFields() {

        // Get profile fields
        $profileFields = Database::fetch('profilefields');

        // If there's nothing just return null
        if(!count($profileFields))
            return null;

        // Create output array
        $fields = [];

        // Iterate over the fields and clean them up
        foreach($profileFields as $field) {

            $fields[$field['id']]           = $field;
            $fields[$field['id']]['ident']  = Main::cleanString($field['name'], true, true);
            $fields[$field['id']]['addit']  = json_decode($field['additional'], true);

        }

        // Return the yeahs
        return $fields;

    }

    // Get user's profile fields
    public static function getUserProfileFields($id, $inputIsData = false) {

        // Get profile fields
        $profileFields = Database::fetch('profilefields');

        // If there's nothing just return null
        if(!count($profileFields))
            return null;

        // Assign the profileData variable
        $profileData = ($inputIsData ? $id : self::getUser($id)['userData']);

        // Once again if nothing was returned just return null
        if(count($profileData) < 1 || $profileData == null || empty($profileData['profileFields']))
            return null;

        // Redeclare profileData
        $profileData = $profileData['profileFields'];

        // Create output array
        $profile = [];

        // Check if profile fields aren't fake
        foreach($profileFields as $field) {

            // Completely strip all special characters from the field name
            $fieldName = Main::cleanString($field['name'], true, true);

            // Check if the user has the current field set otherwise continue
            if(!array_key_exists($fieldName, $profileData))
                continue;

            // Assign field to output with value
            $profile[$fieldName]            = array();
            $profile[$fieldName]['name']    = $field['name'];
            $profile[$fieldName]['value']   = $profileData[$fieldName];
            $profile[$fieldName]['islink']  = $field['islink'];

            // If the field is set to be a link add a value for that as well
            if($field['islink'])
                $profile[$fieldName]['link'] = str_replace('{{ VAL }}', $profileData[$fieldName], $field['linkformat']);

            // Check if we have additional options as well
            if($field['additional'] != null) {

                // Decode the json of the additional stuff
                $additional = json_decode($field['additional'], true);

                // Go over all additional forms
                foreach($additional as $subName => $subField) {

                    // Check if the user has the current field set otherwise continue
                    if(!array_key_exists($subName, $profileData))
                        continue;

                    // Assign field to output with value
                    $profile[$fieldName][$subName] = $profileData[$subName];

                }

            }

        }

        // Return appropiate profile data
        return $profile;

    }

    // Updating the profile data of a user
    public static function updateUserDataField($id, $data) {

        // We retrieve the current content from the database
        $current = self::getUser($id)['userData'];

        // Merge the arrays
        $data = array_merge($current, $data);

        // Encode the json
        $data = json_encode($data);

        // Store it in the database
        Database::update('users', [
            [
                'userData' => $data
            ],
            [
                'id' => [$id, '=']
            ]
        ]);

    }

    // Getting the profile page of a user
    public static function getProfilePage($id, $inputIsData = false) {

        // Check if the input is the data
        if($inputIsData) {

            // Reassign data
            $data = $id;

        } else {

            // Get user data
            $user = self::getUser($id);

            // Decode the userData json
            $data = json_decode($user['userData'], true);

        }

        // Check if the profilePage key exists
        if(!array_key_exists('profilePage', $data))
            return false;

        // TODO: implement BBcodes

        // Parse the markdown
        $profilePage = Main::mdParse(base64_decode($data['profilePage'][0]));

        // Return the parsed profile page
        return $profilePage;

    }

    // Check if a user is online
    public static function checkUserOnline($id) {

        // Get user
        $user = self::getUser($id);

        // Return false if the user doesn't exist because a user that doesn't exist can't be online
        if(empty($user))
            return false;

        // Return true if the user was online in the last 5 minutes
        return ($user['lastdate'] > (time() - 500));

    }

    // Get all online users
    public static function checkAllOnline() {

        // Assign time - 500 to a variable
        $time = time() - 500;

        // Get all online users in the past 5 minutes
        $getAll = Database::fetch('users', true, ['lastdate' => [$time, '>']]);

        // Return all the online users
        return $getAll;

    }

    // Add premium to a user
    public static function addUserPremium($id, $seconds) {

        // Check if there's already a record of premium for this user in the database
        $getUser = Database::fetch('premium', false, [
            'uid' => [$id, '=']
        ]);

        // Calculate the (new) start and expiration timestamp
        $start  = isset($getUser['startdate'])  ? $getUser['startdate']             : time();
        $expire = isset($getUser['expiredate']) ? $getUser['expiredate'] + $seconds : time() + $seconds;

        // If the user already exists do an update call, otherwise an insert call
        if(empty($getUser)) {

            Database::insert('premium', [
                'uid'           => $id,
                'startdate'     => $start,
                'expiredate'    => $expire
            ]);

        } else {

            Database::update('premium', [
                [
                    'expiredate'    => $expire
                ],
                [
                    'uid' => [$id, '=']
                ]
            ]);

        }

        // Return the expiration timestamp
        return $expire;

    }

    // Remove the premium status of a user
    public static function removeUserPremium($id) {

        Database::delete('premium', [
            'uid' => [$id, '=']
        ]);

    }

    // Check if user has Premium
    public static function checkUserPremium($id) {

        // Check if the user has static premium
        if(Permissions::check('SITE', 'STATIC_PREMIUM', $id, 1))
            return [2, 0, time() + 1];

        // Attempt to retrieve the premium record from the database
        $getRecord = Database::fetch('premium', false, [
            'uid' => [$id, '=']
        ]);

        // If nothing was returned just return false
        if(empty($getRecord))
            return [0];

        // Check if the Tenshi hasn't expired
        if($getRecord['expiredate'] < time()) {

            self::removeUserPremium($id);
            self::updatePremiumMeta($id);
            return [0, $getRecord['startdate'], $getRecord['expiredate']];

        }

        // Else return the start and expiration date
        return [1, $getRecord['startdate'], $getRecord['expiredate']];

    }

    // Update the premium data
    public static function updatePremiumMeta($id) {

        // Get the ID for the premium user rank from the database
        $premiumRank = Configuration::getConfig('premium_rank_id');

        // Run the check
        $check = self::checkUserPremium($id);

        // Check if the user has premium
        if($check[0] == 1) {

            // If so add the rank to them
            self::addRanksToUser([$premiumRank], $id);

            // Check if the user's default rank is standard user and update it to premium
            if(self::getUser($id)['rank_main'] == 2)
                self::setDefaultRank($id, $premiumRank);

        } elseif($check[0] == 0 && count($check) > 1) {

            // Else remove the rank from them
            self::removeRanksFromUser([$premiumRank], $id);

        }

    }

    // Get user data by id
    public static function getUser($id) {

        // Execute query
        $user = Database::fetch('users', false, ['id' => [$id, '=']]);

        // Return false if no user was found
        if(empty($user))
            $user = self::$emptyUser;

        // Parse the json in the additional section
        $user['userData'] = json_decode($user['userData'], true);

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

    // Get users in rank
    public static function getUsersInRank($rankId, $users = null, $excludeAbyss = true) {

        // Get all users (or use the supplied user list to keep server load down)
        if(!$users)
            $users = self::getAllUsers();

        // Make output array
        $rank = array();

        // Go over all users and check if they have the rank id
        foreach($users as $user) {

            // If so store the user's row in the array
            if(self::checkIfUserHasRanks([$rankId], $user, true) && ($excludeAbyss ? $user['password_algo'] != 'nologin' : true))
                $rank[] = $user;

        }

        // Then return the array with the user rows
        return $rank;

    }

    // Get all users
    public static function getAllUsers($includeInactive = true, $includeAbyss = false) {

        // Execute query
        $getUsers = Database::fetch('users', true);

        // Define variable
        $users = [];

        // Reorder shit
        foreach($getUsers as $user) {

            // Skip abyss
            if(!$includeAbyss && $user['password_algo'] == 'nologin')
                continue;

            // Skip if inactive and not include deactivated users
            if(!$includeInactive && Permissions::check('SITE', 'DEACTIVATED', $user['id'], 1))
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

        // Define variable
        $ranks = [];

        // Reorder shit
        foreach($getRanks as $rank)
            $ranks[$rank['id']] = $rank;

        // and return an array with the ranks
        return $ranks;

    }

    // Get all warnings issued to a user (or all warnings a user issued)
    public static function getWarnings($uid = 0, $iid = false) {

        // Do the database query
        $warnings = Database::fetch('warnings', true, ($uid ? [
            ($iid ? 'iid' : 'uid') => [$uid, '=']
        ] : null));

        // Return all the warnings
        return $warnings;

    }

    // Get a user's notifications
    public static function getNotifications($uid = null, $timediff = 0, $excludeRead = true, $markRead = false) {

        // Prepare conditions
        $conditions = array();
        $conditions['uid'] = [($uid ? $uid : Session::$userId), '='];
        if($timediff)
            $conditions['timestamp'] = [time() - $timediff, '>'];
        if($excludeRead)
            $conditions['notif_read'] = [0, '='];

        // Get notifications for the database
        $notifications = Database::fetch('notifications', true, $conditions);

        // Mark the notifications as read
        if($markRead) {

            // Iterate over all entries
            foreach($notifications as $notification) {

                // If the notifcation is already read skip
                if($notification['notif_read'])
                    continue;

                // Mark them as read
                self::markNotificationRead($notification['id']);

            }

        }

        // Return the notifications
        return $notifications;

    }

    // Marking notifications as read
    public static function markNotificationRead($id, $mode = true) {

        // Execute an update statement
        Database::update('notifications', [
            [
                'notif_read' => ($mode ? 1 : 0)
            ],
            [
                'id' => [$id, '=']
            ]
        ]);

    }

    // Adding a new notification
    public static function createNotification($user, $title, $text, $timeout = 60000, $img = 'FONT:fa-info-circle', $link = '', $sound = 0) {

        // Get current timestamp
        $time = time();

        // Insert it into the database
        Database::insert('notifications', [
            'uid'           => $user,
            'timestamp'     => $time,
            'notif_read'    => 0,
            'notif_sound'   => ($sound ? 1 : 0),
            'notif_title'   => $title,
            'notif_text'    => $text,
            'notif_link'    => $link,
            'notif_img'     => $img,
            'notif_timeout' => $timeout
        ]);

    }

    // Getting a user's PMs
    public static function getPrivateMessages($from = false) {

        // Get all messages from the database
        $messages = Database::fetch('messages', true, [
            ($from ? 'from_user' : 'to_user') => [Session::$userId, '=']
        ]);

        // Prepare a storage array
        $store = array();

        // Go over each message and check if they are for the current user
        foreach($messages as $message) {

            // Store the message
            $store[$message['id']] = $message;

            // Store user data as well
            $store[$message['id']]['data']['from']['user']  = ($_MSG_USR = self::getUser($message['from_user']));
            $store[$message['id']]['data']['from']['rank']  = self::getRank($_MSG_USR['rank_main']);
            $store[$message['id']]['data']['to']['user']    = ($_MSG_USR = self::getUser($message['to_user']));
            $store[$message['id']]['data']['to']['rank']    = self::getRank($_MSG_USR['rank_main']);

        }

        // Return store array
        return $store;

    }

    // Get friends
    public static function getFriends($uid = null, $timestamps = false, $getData = false, $checkOnline = false) {

        // Assign $uid
        if(!$uid)
            $uid = Session::$userId;

        // Get all friends
        $getFriends = Database::fetch('friends', true, [
            'uid' => [$uid, '=']
        ]);

        // Create the friends array
        $friends = [];

        // Iterate over the raw database return
        foreach($getFriends as $key => $friend) {

            // Add friend to array
            $friends[($timestamps ? $friend['fid'] : $key)] = $getData ? ([

                'user' => ($_UDATA = self::getUser($friend['fid'])),
                'rank' => self::getRank($_UDATA['rank_main'])

            ]) : $friend[($timestamps ? 'timestamp' : 'fid')];

        }

        // Check who is online and who isn't
        if($checkOnline) {

            // Check each user
            foreach($friends as $key => $friend) {

                $friends[self::checkUserOnline($getData ? $friend['user']['id'] : $friend) ? 'online' : 'offline'][] = $friend;

            }

        }

        // Return formatted array
        return $friends;

    }

    // Get non-mutual friends
    public static function getPendingFriends($uid = null, $getData = false) {

        // Assign $of automatically if it's not set
        if(!$uid)
            $uid = Session::$userId;

        // Get all friend entries from other people involved the current user
        $friends = Database::fetch('friends', true, [
            'fid' => [$uid, '=']
        ]);

        // Create pending array
        $pending = [];

        // Check if the friends are mutual
        foreach($friends as $friend) {

            // Check if the friend is mutual
            if(!self::checkFriend($friend['uid'], $uid)) {

                $pending[] = $getData ? ([

                    'user' => ($_UDATA = self::getUser($friend['uid'])),
                    'rank' => self::getRank($_UDATA['rank_main'])

                ]) : $friend;

            }

        }

        // Return the pending friends
        return $pending;

    }

    // Check if a friend is mutual
    public static function checkFriend($fid, $uid = null) {

        // Assign $uid
        if(!$uid)
            $uid = Session::$userId;

        // Get the user's friends
        $self = self::getFriends($uid);

        // Check if the friend is actually in the user's array
        if(!in_array($fid, $self))
            return 0;

        // Get the friend's friends
        $friend = self::getFriends($fid);

        // Check if the friend is actually in the user's array
        if(in_array($uid, $friend))
            return 2;

        // Return true if all went through
        return 1;

    }

    // Adding a friend
    public static function addFriend($uid) {

        // Validate that the user exists
        if(!self::getUser($uid))
            return [0, 'USER_NOT_EXIST'];

        // Check if the user already has this user a friend
        if(Database::fetch('friends', false, ['fid' => [$uid, '='], 'uid' => [Session::$userId, '=']]))
            return [0, 'ALREADY_FRIENDS'];

        // Add friend
        Database::insert('friends', [
            'uid'       => Session::$userId,
            'fid'       => $uid,
            'timestamp' => time()
        ]);

        // Return true because yay
        return [1, Users::checkFriend($uid) == 2 ? 'FRIENDS' : 'NOT_MUTUAL'];

    }

    // Removing a friend
    public static function removeFriend($uid, $deleteRequest = false) {

        // Check if the user has this user a friend
        if(!Database::fetch('friends', false, ['fid' => [$uid, '='], 'uid' => [Session::$userId, '=']]))
            return [0, 'ALREADY_REMOVED'];

        // Remove friend
        Database::delete('friends', [
            'uid' => [Session::$userId, '='],
            'fid' => [$uid, '=']
        ]);

        // Attempt to remove the request
        if($deleteRequest) {

            Database::delete('friends', [
                'fid' => [Session::$userId, '='],
                'uid' => [$uid, '=']
            ]);

        }

        // Return true because yay
        return [1, 'REMOVED'];

    }

}
