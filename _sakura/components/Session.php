<?php
/*
 * User session container
 */

namespace Sakura;

class Session
{
    // Current user data
    public $userId;
    public $sessionId;

    // Initialise new session
    public function ___construct()
    {

        // Check if a PHP session was already started and if not start one
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }

    }
}
