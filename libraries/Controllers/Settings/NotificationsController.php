<?php
/**
 * Holds the notifications section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

/**
 * Notification settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class NotificationsController extends Controller
{
    public function history()
    {
        return $this->go('notifications.history');
    }
}
