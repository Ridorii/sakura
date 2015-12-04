<?php
/*
 * User session container
 */

namespace Sakura;

/**
 * Class Session
 * @package Sakura
 */
class Session
{
    // Current user data
    public $userId = 0;
    public $sessionId = "";

    // Initialise new session
    public function __construct($userId, $sessionId = null)
    {
        // Set the supposed session data
        $this->userId = $userId;
        $this->sessionId = $sessionId;

        // Check if a PHP session was already started and if not start one
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    // Destroy this session
    public function destroy()
    {
        // Invalidate the session key
        Database::delete('sessions', [
            'session_key' => [$this->sessionId, '='],
            'user_id' => [$this->userId, '='],
        ]);

        // Unset userId and sessionId
        unset($this->userId);
        unset($this->sessionId);

        // Destroy the session
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    // Destroy all sessions keys for this user
    public function destroyAll()
    {
        // Delete all database entries with this user in it
        Database::delete('sessions', ['user_id' => [$this->userId, '=']]);

        // Destroy this session to finish it off
        $this->destroy();
    }

    // Create a new session
    public function create($permanent)
    {
        // Generate session key
        $session = hash('sha256', $this->userId . base64_encode('sakura' . mt_rand(0, 99999999)) . time());

        // Insert the session into the database
        Database::insert('sessions', [
            'user_id' => $this->userId,
            'user_ip' => Main::getRemoteIP(),
            'user_agent' => Main::cleanString(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'No user agent header.'),
            'session_key' => $session,
            'session_start' => time(),
            'session_expire' => time() + 604800,
            'session_remember' => $permanent ? '1' : '0',
        ]);

        // Return the session key
        return $session;
    }

    // Validate an apparently existing session
    public function validate()
    {
        // Get session from database
        $session = Database::fetch('sessions', false, [
            'user_id' => [$this->userId, '='],
            'session_key' => [$this->sessionId, '='],
        ]);

        // Check if we actually got something in return
        if (!$session) {
            return 0;
        }

        // Check if the session expired
        if ($session['session_expire'] < time()) {
            // ...and return false
            return 0;
        }

        // IP Check
        $ipCheck = Config::get('session_check');

        // Origin checking
        if ($ipCheck) {
            // Split both IPs up
            $sessionIP = explode('.', $session['user_ip']);
            $userIP = explode('.', Main::getRemoteIP());

            // Take 1 off the ipCheck variable so it's equal to the array keys
            $ipCheck = $ipCheck - 1;

            // Check if the user's IP is similar to the session's registered IP
            switch ($ipCheck) {
                // 000.xxx.xxx.xxx
                case 3:
                    if ($userIP[3] !== $sessionIP[3]) {
                        return 0;
                    }

                // xxx.000.xxx.xxx
                case 2:
                case 3:
                    if ($userIP[2] !== $sessionIP[2]) {
                        return 0;
                    }

                // xxx.xxx.000.xxx
                case 1:
                case 2:
                case 3:
                    if ($userIP[1] !== $sessionIP[1]) {
                        return 0;
                    }

                // xxx.xxx.xxx.000
                case 0:
                case 1:
                case 2:
                case 3:
                    if ($userIP[0] !== $sessionIP[0]) {
                        return 0;
                    }
            }
        }

        // If the remember flag is set extend the session time
        if ($session['session_remember']) {
            Database::update('sessions', [
                [
                    'session_expire' => time() + 604800,
                ],
                [
                    'session_id' => [$session['session_id'], '='],
                ],
            ]);
        }

        // Return 2 if the remember flag is set and return 1 if not
        return $session['session_remember'] ? 2 : 1;
    }
}