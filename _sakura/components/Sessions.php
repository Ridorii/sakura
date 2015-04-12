<?php
/*
 * Session Handler
 */

namespace Sakura;

class Session {

    // Current user data
    public static $userId;
    public static $sessionId;

    // Initiate new session
    public static function init() {

        // Start PHP session
        if(session_status() != PHP_SESSION_ACTIVE)
            session_start();

        // Assign user and session IDs
        self::$userId       = isset($_COOKIE[Configuration::getConfig('cookie_prefix') .'id'])      ? isset($_COOKIE[Configuration::getConfig('cookie_prefix') .'id'])      : 0;
        self::$sessionId    = isset($_COOKIE[Configuration::getConfig('cookie_prefix') .'session']) ? isset($_COOKIE[Configuration::getConfig('cookie_prefix') .'session']) : '';

    }

    // Create new session
    public static function newSession($userID, $remember = false) {

        // Generate session key
        $session = sha1($userID . base64_encode('sakura'. mt_rand(0, 99999999)) . time());

        // Insert the session into the database
        Database::insert('sessions', [
            'userip'    => $_SERVER['REMOTE_ADDR'],
            'useragent' => Main::cleanString($_SERVER['HTTP_USER_AGENT']),
            'userid'    => $userID,
            'skey'      => $session,
            'started'   => time(),
            'expire'    => time() + 604800,
            'remember'  => $remember
        ]);

        // Return the session key
        return $session;

    }

    // Check session data (expiry, etc.)
    public static function checkSession($userId, $sessionId) {

        

    }

}
