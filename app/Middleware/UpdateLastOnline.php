<?php
/**
 * Holds the last online update middleware.
 * @package Sakura
 */

namespace Sakura\Middleware;

use Sakura\CurrentSession;

/**
 * Updates when the last online time of a user.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class UpdateLastOnline implements MiddlewareInterface
{
    /**
     * Update the last online information for the active user.
     */
    public function run()
    {
        if (CurrentSession::$user->id !== 0) {
            CurrentSession::$user->updateOnline();
        }
    }
}
