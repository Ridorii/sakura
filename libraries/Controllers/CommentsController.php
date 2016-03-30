<?php
/**
 * Holds the comments controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Comment;
use Sakura\Template;

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
        
        // Set json content type
        header('Content-Type: application/json; charset=utf-8');

        // Check if the user can comment
        if (!$currentUser->permission(Site::CREATE_COMMENTS)) {
            $error = "You aren't allowed to make comments!";
            return $this->json(compact('error'));
        }

        // Checks
        $length = strlen($content);
        $tooShort = $length < Config::get('comment_min_length');
        $tooLong = $length > Config::get('comment_max_length');

        if ($tooShort || $tooLong) {
            $fill = $tooShort ? "short" : "long";
            $error = "Your comment is too {$fill}!";
            
            return $this->json(compact('error'));
        }

        $text = isset($_POST['text']) ? $_POST['text'] : '';

        $comment = new Comment;

        $comment->category = $category;
        $comment->time = time();
        $comment->reply = (int) $reply;
        $comment->user = $currentUser->id;
        $comment->text = $text;

        $comment->save();

        return $this->json($comment);
    }

    public function edit($id = 0)
    {
        //
    }

    public function delete($id = 0)
    {
        //
    }

    public function vote($id = 0)
    {
        //
    }
}
