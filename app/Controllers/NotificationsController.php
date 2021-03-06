<?php
/**
 * Holds the notification controllers.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\CurrentSession;
use Sakura\Notification;

/**
 * Notification stuff.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class NotificationsController extends Controller
{
    /**
     * Get the notification JSON object for the currently authenticated user.
     * @return string
     */
    public function notifications()
    {
        return $this->json(CurrentSession::$user->notifications());
    }

    /**
     * Mark a notification as read.
     * Not entirely set on this one yet but 1 for success and 0 for fail.
     * @param int
     * @return string
     */
    public function mark($id = 0)
    {
        if (!CurrentSession::$user->activated) {
            return '0';
        }

        // Create the notification object
        $alert = new Notification($id);

        // Verify that the currently authed user is the one this alert is for
        if ($alert->user !== CurrentSession::$user->id) {
            return '0';
        }

        // Toggle the read status and save
        $alert->toggleRead();
        $alert->save();

        return '1';
    }
}
