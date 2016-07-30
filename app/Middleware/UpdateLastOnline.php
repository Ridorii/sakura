<?php
/**
 * Holds the last online update middleware.
 *
 * @package Sakura
 */

namespace Sakura\Middleware;

use Sakura\ActiveUser;

/**
 * Updates when the last online time of a user.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class UpdateLastOnline implements Middleware
{
    public function run()
    {
        if (ActiveUser::$user->id !== 0) {
            ActiveUser::$user->updateOnline();
        }
    }
}
