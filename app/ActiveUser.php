<?php
/**
 * Holds information about the currently active session
 * @package Sakura
 */

namespace Sakura;

use Sakura\Perms\Site;

/**
 * Information about the current active user and session.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class ActiveUser
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
     * Attempt to validate a session.
     * @param int $userId
     * @param string $sessionId
     */
    public static function init($userId, $sessionId)
    {
        // Create a session object
        self::$session = new Session($userId, $sessionId);

        // Create a user object
        $user = User::construct($userId);

        // Check if the session exists and check if the user is activated
        if (self::$session->validate() > 0
            && !$user->permission(Site::DEACTIVATED)) {
            // Assign the user object
            self::$user = $user;
        } else {
            self::$user = User::construct(0);
        }
    }
}
