<?php
/**
 * Holds the notification controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Notification;
use Sakura\Perms\Site;
use Sakura\User;

/**
 * Notification stuff.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class NotificationsController extends Controller
{
    /**
     * Get the notification JSON object for the currently authenticated user.
     *
     * @return string The JSON object.
     */
    public function notifications()
    {
        // TODO: add friend on/offline messages
        global $currentUser;

        // Set json content type
        header('Content-Type: application/json; charset=utf-8');

        return $this->json($currentUser->notifications());
    }

    /**
     * Mark a notification as read.
     *
     * @param int The ID of the notification.
     *
     * @return string Not entirely set on this one yet but 1 for success and 0 for fail.
     */
    public function mark($id = 0)
    {
        global $currentUser;

        // Check permission
        if ($currentUser->permission(Site::DEACTIVATED)) {
            return '0';
        }

        // Create the notification object
        $alert = new Notification($id);

        // Verify that the currently authed user is the one this alert is for
        if ($alert->user !== $currentUser->id) {
            return '0';
        }

        // Toggle the read status and save
        $alert->toggleRead();
        $alert->save();

        return '1';
    }
}
