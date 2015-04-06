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

    }

}
