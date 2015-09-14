<?php
/*
 * Session Handler
 */

namespace Sakura;

class Session
{
    // Current user data
    public static $userId;
    public static $sessionId;

    // Initiate new session
    public static function init()
    {

        // Start PHP session
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Assign user ID
        self::$userId =
        isset($_COOKIE[Configuration::getConfig('cookie_prefix') . 'id']) ?
        $_COOKIE[Configuration::getConfig('cookie_prefix') . 'id'] :
        0;

        // Assign session ID
        self::$sessionId =
        isset($_COOKIE[Configuration::getConfig('cookie_prefix') . 'session']) ?
        $_COOKIE[Configuration::getConfig('cookie_prefix') . 'session'] :
        '';

    }

    // Create new session
    public static function newSession($userId, $remember = false)
    {

        // Generate session key
        $session = sha1($userId . base64_encode('sakura' . mt_rand(0, 99999999)) . time());

        // Insert the session into the database
        Database::insert('sessions', [
            'userip' => Main::getRemoteIP(),
            'useragent' => Main::cleanString($_SERVER['HTTP_USER_AGENT']),
            'userid' => $userId,
            'skey' => $session,
            'started' => time(),
            'expire' => time() + 604800,
            'remember' => $remember ? '1' : '0',
        ]);

        // Return the session key
        return $session;

    }

    // Check session data (expiry, etc.)
    public static function checkSession($userId, $sessionId)
    {

        // Get session from database
        $session = Database::fetch('sessions', true, ['userid' => [$userId, '='], 'skey' => [$sessionId, '=']]);

        // Check if we actually got something in return
        if (!count($session)) {
            return false;
        }

        $session = $session[0];

        // Check if the session expired
        if ($session['expire'] < time()) {
            // If it is delete the session...
            self::deleteSession($session['id']);

            // ...and return false
            return false;
        }

        // Origin checking
        if ($ipCheck = Configuration::getConfig('session_check')) {
            // Split both IPs up
            $sessionIP = explode('.', $session['userip']);
            $userIP = explode('.', Main::getRemoteIP());

            // Take 1 off the ipCheck variable so it's equal to the array keys
            $ipCheck = $ipCheck - 1;

            // Check if the user's IP is similar to the session's registered IP
            switch ($ipCheck) {
                // 000.xxx.xxx.xxx
                case 3:
                    if ($userIP[3] !== $sessionIP[3]) {
                        return false;
                    }

                // xxx.000.xxx.xxx
                case 2:
                case 3:
                    if ($userIP[2] !== $sessionIP[2]) {
                        return false;
                    }

                // xxx.xxx.000.xxx
                case 1:
                case 2:
                case 3:
                    if ($userIP[1] !== $sessionIP[1]) {
                        return false;
                    }

                // xxx.xxx.xxx.000
                case 0:
                case 1:
                case 2:
                case 3:
                    if ($userIP[0] !== $sessionIP[0]) {
                        return false;
                    }
            }
        }

        // If the remember flag is set extend the session time
        if ($session['remember']) {
            Database::update('sessions', [['expire' => time() + 604800], ['id' => [$session['id'], '=']]]);
        }

        // Return 2 if the remember flag is set and return 1 if not
        return $session['remember'] ? 2 : 1;

    }

    // Delete a session
    public static function deleteSession($sessionId, $key = false)
    {

        // Check if the session exists
        if (!Database::fetch('sessions', [($key ? 'skey' : 'id'), true, [$sessionId, '=']])) {
            return false;
        }

        // Run the query
        Database::delete('sessions', [($key ? 'skey' : 'id') => [$sessionId, '=']]);

        // Return true if key was found and deleted
        return true;

    }
}
