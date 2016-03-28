<?php
/**
 * Holds the comments controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Comment;

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

        // todo: make not shit

        $text = isset($_POST['text']) ? $_POST['text'] : '';

        $comment = new Comment;

        $comment->category = $category;
        $comment->time = time();
        $comment->reply = (int) $reply;
        $comment->user = $currentUser->id;
        $comment->text = $text;

        $comment->save();
    }
}
