<?php
/**
 * Holds the friends controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Notification;
use Sakura\Perms\Site;
use Sakura\Router;
use Sakura\User;

/**
 * Friendly controller.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class FriendsController extends Controller
{
    private function addNotification($friend, $user, $title, $text = "")
    {
        $alert = new Notification;

        $alert->user = $friend->id;
        $alert->time = time();
        $alert->title = $title;
        $alert->text = $text;
        $alert->image = Router::route('file.avatar', $user->id);
        $alert->timeout = 60000;
        $alert->link = Router::route('user.profile', $user->id);

        $alert->save();
    }

    public function add($id = 0)
    {
        global $currentUser;

        $session = $_POST['session'] ?? '';

        // Check if the user can comment
        if ($session !== session_id()) {
            $error = "Your session expired, refresh the page!";
            return $this->json(compact('error'));
        }

        $friend = User::construct($id);

        if ($friend->permission(Site::DEACTIVATED)
            || $currentUser->permission(Site::DEACTIVATED)) {
            $error = "The user you tried to add does not exist!";
            return $this->json(compact('error'));
        }

        if ($friend->id === $currentUser->id) {
            $error = "You can't be friends with yourself, stop trying to bend reality!";
            return $this->json(compact('error'));
        }

        if ($currentUser->isFriends($friend->id)) {
            $error = "You are already friends with this person!";
            return $this->json(compact('error'));
        }

        // Add friend
        $currentUser->addFriend($friend->id);

        $level = $currentUser->isFriends($friend->id);

        $mutual = $level === 2;

        $alertTitle = $mutual
        ? "{$currentUser->username} accepted your friend request!"
        : "{$currentUser->username} added you as a friend!";

        $alertText = $mutual
        ? ""
        : "Click here to add them as well.";

        $this->addNotification(
            $friend,
            $currentUser,
            $alertTitle,
            $alertText
        );

        $message = $mutual
        ? "You are now mutual friends with {$friend->username}!"
        : "A friend request has been sent to {$friend->username}!";

        return $this->json(compact('message', 'level'));
    }

    public function remove($id = 0)
    {
        global $currentUser;

        $session = $_POST['session'] ?? '';

        // Check if the user can comment
        if ($session !== session_id()) {
            $error = "Your session expired, refresh the page!";
            return $this->json(compact('error'));
        }

        $friend = User::construct($id);

        if ($friend->permission(Site::DEACTIVATED)
            || $currentUser->permission(Site::DEACTIVATED)) {
            $error = "The user you tried to remove does not exist!";
            return $this->json(compact('error'));
        }

        if (!$currentUser->isFriends($friend->id)) {
            $error = "You aren't even friends with that person!";
            return $this->json(compact('error'));
        }

        // Add friend
        $currentUser->removeFriend($friend->id);

        $level = $currentUser->isFriends($friend->id);

        $alertTitle = "{$currentUser->username} removed you from their friends!";

        $this->addNotification(
            $friend,
            $currentUser,
            $alertTitle
        );

        $message = "Removed {$friend->username} from your friends!";

        return $this->json(compact('message', 'level'));
    }
}
