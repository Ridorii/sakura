<?php
/**
 * Holds the friends controller.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\CurrentSession;
use Sakura\Notification;
use Sakura\Perms\Site;
use Sakura\User;

/**
 * Friendly controller.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class FriendsController extends Controller
{
    /**
     * Add a notification.
     * @param User $friend
     * @param User $user
     * @param string $title
     * @param string $text
     */
    private function addNotification($friend, $user, $title, $text = "")
    {
        $alert = new Notification;

        $alert->user = $friend->id;
        $alert->time = time();
        $alert->title = $title;
        $alert->text = $text;
        $alert->image = route('file.avatar', $user->id);
        $alert->timeout = 60000;
        $alert->link = route('user.profile', $user->id);

        $alert->save();
    }

    /**
     * Add a friend.
     * @param int $id
     * @return string
     */
    public function add($id = 0)
    {
        $user = CurrentSession::$user;

        // Check if the user can comment
        if (session_check()) {
            $error = "Your session expired, refresh the page!";
            return $this->json(compact('error'));
        }

        $friend = User::construct($id);

        if ($friend->permission(Site::DEACTIVATED)
            || $user->permission(Site::DEACTIVATED)) {
            $error = "The user you tried to add does not exist!";
            return $this->json(compact('error'));
        }

        if ($friend->id === $user->id) {
            $error = "You can't be friends with yourself, stop trying to bend reality!";
            return $this->json(compact('error'));
        }

        if ($user->isFriends($friend->id)) {
            $error = "You are already friends with this person!";
            return $this->json(compact('error'));
        }

        // Add friend
        $user->addFriend($friend->id);

        $level = $user->isFriends($friend->id);

        $mutual = $level === 2;

        $alertTitle = $mutual
        ? "{$user->username} accepted your friend request!"
        : "{$user->username} added you as a friend!";

        $alertText = $mutual
        ? ""
        : "Click here to add them as well.";

        $this->addNotification(
            $friend,
            $user,
            $alertTitle,
            $alertText
        );

        $message = $mutual
        ? "You are now mutual friends with {$friend->username}!"
        : "A friend request has been sent to {$friend->username}!";

        return $this->json(compact('message', 'level'));
    }

    /**
     * Removes a friend.
     * @param int $id
     * @return string
     */
    public function remove($id = 0)
    {
        $user = CurrentSession::$user;

        // Check if the user can comment
        if (session_check()) {
            $error = "Your session expired, refresh the page!";
            return $this->json(compact('error'));
        }

        $friend = User::construct($id);

        if ($friend->permission(Site::DEACTIVATED)
            || $user->permission(Site::DEACTIVATED)) {
            $error = "The user you tried to remove does not exist!";
            return $this->json(compact('error'));
        }

        if (!$user->isFriends($friend->id)) {
            $error = "You aren't even friends with that person!";
            return $this->json(compact('error'));
        }

        // Add friend
        $user->removeFriend($friend->id);

        $level = $user->isFriends($friend->id);

        $alertTitle = "{$user->username} removed you from their friends!";

        $this->addNotification(
            $friend,
            $user,
            $alertTitle
        );

        $message = "Removed {$friend->username} from your friends!";

        return $this->json(compact('message', 'level'));
    }
}
