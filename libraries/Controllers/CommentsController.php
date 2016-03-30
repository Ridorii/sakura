<?php
/**
 * Holds the comments controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Comment;
use Sakura\Config;
use Sakura\Perms\Site;

/**
 * Handles comment stuff.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class CommentsController extends Controller
{
    public function post($category = '', $reply = 0)
    {
        global $currentUser;

        $session = $_POST['session'] ?? '';

        // Check if the user can comment
        if ($session !== session_id()) {
            $error = "Your session expired, refresh the page!";
            return $this->json(compact('error'));
        }

        // Check if the user can comment
        if (!$currentUser->permission(Site::CREATE_COMMENTS)) {
            $error = "You aren't allowed to make comments!";
            return $this->json(compact('error'));
        }

        // Checks
        $text = $_POST['text'] ?? '';
        $length = strlen($text);
        $tooShort = $length < Config::get('comment_min_length');
        $tooLong = $length > Config::get('comment_max_length');

        if ($tooShort || $tooLong) {
            $fill = $tooShort ? "short" : "long";
            $error = "Your comment is too {$fill}!";

            return $this->json(compact('error'));
        }

        $text = $_POST['text'] ?? '';

        $comment = new Comment;

        $comment->category = $category;
        $comment->time = time();
        $comment->reply = (int) $reply;
        $comment->user = (int) $currentUser->id;
        $comment->text = $text;

        $comment->save();

        return $this->json($comment);
    }

    public function delete($id = 0)
    {
        global $currentUser;

        // Check if the user can delete comments
        if (!$currentUser->permission(Site::DELETE_COMMENTS)) {
            $error = "You aren't allowed to delete comments!";
            return $this->json(compact('error'));
        }

        $comment = new Comment($id);

        if (!$comment->id) {
            $error = "This comment doesn't exist!";
            return $this->json(compact('error'));
        }

        if ($currentUser->id !== $comment->user) {
            $error = "You aren't allowed to delete the comments of other people!";
            return $this->json(compact('error'));
        }

        $deleted = $comment->id;

        $comment->delete();

        return $this->json(compact('deleted'));
    }

    public function vote($id = 0)
    {
        global $currentUser;

        $vote = $_REQUEST['vote'] ?? 0;
        $vote = $vote != 0;

        // Check if the user can delete comments
        if (!$currentUser->permission(Site::VOTE_COMMENTS)) {
            $error = "You aren't allowed to vote on comments!";
            return $this->json(compact('error'));
        }

        $comment = new Comment($id);

        if (!$comment->id) {
            $error = "This comment doesn't exist!";
            return $this->json(compact('error'));
        }

        $comment->vote($currentUser->id, $vote);

        $upvotes = $comment->upvotes;
        $downvotes = $comment->downvotes;

        return $this->json(compact('upvotes', 'downvotes'));
    }
}
