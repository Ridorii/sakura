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
        'country'           => 'EU',
        'profile_data'      => '[]'
    ];

    // Empty rank template
    public static $emptyRank = [
        'id'            => 0,
        'rankname'      => 'Non-existent Rank',
        'multi'         => 0,
        'colour'        => '#444',
        'description'   => 'A hardcoded dummy rank for fallback.',
        'is_premium'    => 0
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

        // Update last online
        Database::update('users', [
            [
                'lastdate' => time()
            ],
            [
                'id' => [Session::$userId, '=']
            ]
        ]);

        // Redirect people that need to change their password to the new format
        if(self::getUser(Session::$userId)['password_algo'] == 'legacy' && $_SERVER['PHP_SELF'] != '/authenticate.php' && $_SERVER['PHP_SELF'] != '/imageserve.php')
            header('Location: /authenticate.php?legacy=true');

        // If everything went through return true
        return true;

    }

    // Log a user in
    public static function login($username, $password, $remember = false) {

        // Check if authentication is disallowed
        if(Configuration::getConfig('lock_authentication'))
            return [0, 'AUTH_LOCKED'];

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
        if(self::checkIfUserHasRanks([0, 1], $user, true))
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
        if(strlen($username) < 3)
            return [0, 'NAME_TOO_SHORT'];

        // Username too long
        if(strlen($username) > 16)
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

        // Check if the user is deactivated
        if(self::checkIfUserHasRanks([0, 1], $user, true))
            return [0, 'DEACTIVATED'];

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

        // Check if the user is deactivated
        if(self::checkIfUserHasRanks([0, 1], $user, true))
            return [0, 'DEACTIVATED'];

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
        if(!self::checkIfUserHasRanks([0, 1], $user, true))
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
        if(count($user) < 2 || !self::checkIfUserHasRanks([0, 1], $user, true))
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
        if(!self::checkIfUserHasRanks([0, 1], $user, true))
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
        if(self::checkIfUserHasRanks([0, 1], $user, true))
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

    // Check if a user has these ranks
    public static function checkIfUserHasRanks($ranks, $userid, $userIdIsUserData = false) {

        // Get the specified user
        $user = $userIdIsUserData ? $userid : self::getUser($userid);

        // Check if the main rank is the specified rank
        if(in_array($user['rank_main'], $ranks))
            return true;

        // If not go over all ranks and check if the user has them
        foreach($ranks as $rank) {

            // We check if $rank is in $user['ranks'] and if yes return true
            if(in_array($rank, $user['ranks']))
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

    // Get user's profile fields
    public static function getUserProfileData($id) {

        // Get profile fields
        $profileFields = Database::fetch('profilefields');

        // If there's nothing just return null
        if(!count($profileFields))
            return null;

        // Get the profile data JSON from the specified user's profile
        $profileData = Database::fetch('users', false, ['id' => [$id, '=']]);

        // Once again if nothing was returned just return null
        if(count($profileData) < 2 || $profileData['profile_data'] == null || !count(json_decode($profileData['profile_data'], true)))
            return null;

        // Decode the profile_data json
        $profileData = json_decode($profileData['profile_data'], true);

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

    // Check if user has Tenshi
    public static function checkUserTenshi($id) {

        // Get user's ranks
        $ranks = json_decode(self::getUser($id)['ranks'], true);

        // Check premium flag
        foreach($ranks as $rank) {

            // If premium rank was found return true
            if(self::getRank($rank)['is_premium'])
                return true;

        }

        // Else return false
        return false;

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

        // Define variable
        $users = [];

        // Reorder shit
        foreach($getUsers as $user) {

            // Skip if inactive and not include deactivated users
            if(!$includeInactive && $user['rank_main'] < 2)
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

}
