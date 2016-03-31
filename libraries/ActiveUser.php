<?php
/**
 * Holds information about the currently active session
 *
 * @package Sakura
 */

namespace Sakura;

use Sakura\Perms\Site;

/**
 * Information about the current active user and session.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class ActiveUser
{
    public static $user = null;
    public static $session = null;

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

            // Update last online
            DB::table('users')
                ->where('user_id', self::$user->id)
                ->update([
                    'user_last_online' => time(),
                ]);
        } else {
            self::$user = User::construct(0);
        }
    }
}
