<?php
/**
 * Holds information about the currently active session
 * @package Sakura
 */

namespace Sakura;

/**
 * Information about the current active user and session.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class CurrentSession
{
    /**
     * The user object of the currently active user.
     * @var User
     */
    public static $user = null;

    /**
     * The currently active session object.
     * @var Session
     */
    public static $session = null;

    /**
     * Prepare the current session backend.
     * @param int $user
     * @param string $session
     * @param string $ip
     */
    public static function start($user, $session, $ip)
    {
        // Check if a PHP session was already started and if not start one
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Create a session object
        self::$session = new Session($session);

        // Create a user object
        $user = User::construct($user);

        // Check if the session exists and check if the user is activated
        if (self::$session->validate($user->id, $ip)
            && $user->activated) {
            // Assign the user object
            self::$user = $user;
        } else {
            self::$user = User::construct(0);
        }
    }

    /**
     * Stop the current session
     */
    public static function stop()
    {
        self::$session->delete();
        session_regenerate_id(true);
        session_destroy();
    }

    /**
     * Create a new sakura session.
     * @param int $user
     * @param string $ip
     * @param string $country
     * @param string $agent
     * @param bool $remember
     * @param int $length
     * @return Session
     */
    public static function create($user, $ip, $country, $agent = null, $remember = false, $length = 604800)
    {
        return Session::create($user, $ip, $country, $agent, $remember, $length);
    }
}
