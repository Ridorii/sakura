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
        'password_algo'     => 'sha256',
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
        'rankname'     => 'Non-existent Rank',
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
        if($userData['password_algo'] == 'legacy') { // Shitty legacy method of sha512(strrev(sha512()))

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
    public static function getAllUsers() {

        // Execute query
        $getUsers = Database::fetch('users', true);

        // Reorder shit
        foreach($getUsers as $user)
            $users[$user['id']] = $user;

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
