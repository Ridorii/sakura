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
        self::$userId       = isset($_COOKIE[Configuration::getConfig('cookie_prefix') .'id'])      ? $_COOKIE[Configuration::getConfig('cookie_prefix') .'id']         : 0;
        self::$sessionId    = isset($_COOKIE[Configuration::getConfig('cookie_prefix') .'session']) ? $_COOKIE[Configuration::getConfig('cookie_prefix') .'session']    : '';

    }

    // Create new session
    public static function newSession($userID, $remember = false) {

        // Generate session key
        $session = sha1($userID . base64_encode('sakura'. mt_rand(0, 99999999)) . time());

        // Insert the session into the database
        Database::insert('sessions', [
            'userip'    => Main::getRemoteIP(),
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

        // Get session from database
        $session = Database::fetch('sessions', true, ['userid' => [$userId, '='], 'skey' => [$sessionId, '=']]);

        // Check if we actually got something in return
        if(!count($session))
            return false;
        else
            $session = $session[0];

        // Check if the session expired
        if($session['expire'] < time()) {

            // If it is delete the session...
            self::deleteSession($session['id']);

            // ...and return false
            return false;

        }

        // If the remember flag is set extend the session time
        if($session['remember'])
            Database::update('sessions', [['expire' => time() + 604800], ['id' => [$session['id'], '=']]]);

        // Return 2 if the remember flag is set and return 1 if not
        return $session['remember'] ? 2 : 1;

    }

    // Delete a session
    public static function deleteSession($sessionId, $key = false) {

        // Check if the session exists
        if(!Database::fetch('sessions', [($key ? 'skey' : 'id'), true, [$sessionId, '=']]))
            return false;

        // Run the query
        Database::delete('sessions', [($key ? 'skey' : 'id'), [$sessionId, '=']]);

        // Return true if key was found and deleted
        return true;

    }

}
