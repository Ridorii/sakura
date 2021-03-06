<?php
/**
 * Holds the comments controller.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Comment;
use Sakura\Config;
use Sakura\CurrentSession;

/**
 * Handles comment stuff.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class CommentsController extends Controller
{
    /**
     * Posts a comment.
     * @param string $category
     * @param int $reply
     * @return string
     */
    public function post($category = '', $reply = 0)
    {
        // Check if the user can comment
        if (session_check()) {
            $error = "Your session expired, refresh the page!";
            return $this->json(compact('error'));
        }

        // Check if the user can comment
        if (!CurrentSession::$user->perms->commentsCreate) {
            $error = "You aren't allowed to make comments!";
            return $this->json(compact('error'));
        }

        // Checks
        $text = $_POST['text'] ?? '';
        $length = strlen($text);
        $tooShort = $length < config('comments.min_length');
        $tooLong = $length > config('comments.max_length');

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
        $comment->user = (int) CurrentSession::$user->id;
        $comment->text = $text;

        $comment->save();

        return $this->json($comment);
    }

    /**
     * Delete a comment.
     * @param int $id
     * @return string
     */
    public function delete($id = 0)
    {
        // Check if the user can delete comments
        if (!CurrentSession::$user->perms->commentsDelete) {
            $error = "You aren't allowed to delete comments!";
            return $this->json(compact('error'));
        }

        $comment = new Comment($id);

        if (!$comment->id) {
            $error = "This comment doesn't exist!";
            return $this->json(compact('error'));
        }

        if (CurrentSession::$user->id !== $comment->user) {
            $error = "You aren't allowed to delete the comments of other people!";
            return $this->json(compact('error'));
        }

        $deleted = $comment->id;

        $comment->delete();

        return $this->json(compact('deleted'));
    }

    /**
     * Cast a vote.
     * @param int $id
     * @return string
     */
    public function vote($id = 0)
    {
        $vote = $_REQUEST['vote'] ?? 0;
        $vote = $vote != 0;

        // Check if the user can delete comments
        if (!CurrentSession::$user->perms->commentsVote) {
            $error = "You aren't allowed to vote on comments!";
            return $this->json(compact('error'));
        }

        $comment = new Comment($id);

        if (!$comment->id) {
            $error = "This comment doesn't exist!";
            return $this->json(compact('error'));
        }

        $comment->vote(CurrentSession::$user->id, $vote);

        $upvotes = $comment->upvotes;
        $downvotes = $comment->downvotes;

        return $this->json(compact('upvotes', 'downvotes'));
    }
}
