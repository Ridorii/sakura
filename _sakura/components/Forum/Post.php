<?php
/*
 * Post class
 */

namespace Sakura\Forum;

use Sakura\Main;
use Sakura\Database;
use Sakura\User;
use Sakura\BBcode;

/**
 * Class Post
 * @package Sakura
 */
class Post
{
    // Variables
    public $id = 0;
    public $thread = 0;
    public $forum = 0;
    public $poster = [];
    public $ip = "";
    public $time = 0;
    public $parse = 0;
    public $signature = 0;
    public $emotes = 0;
    public $subject = "";
    public $text = "";
    public $parsed = "";
    public $editTime = 0;
    public $editReason = "";
    public $editUser = [];

    // Constructor
    public function __construct($postId)
    {
        // Attempt to get the database row
        $postRow = Database::fetch('posts', false, ['post_id' => [$postId, '=']]);
        
        // Assign data if a row was returned
        if ($postRow) {
            $this->id = $postRow['post_id'];
            $this->thread = $postRow['topic_id'];
            $this->forum = $postRow['forum_id'];
            $this->poster = new User($postRow['poster_id']);
            $this->ip = $postRow['poster_ip'];
            $this->time = $postRow['post_time'];
            $this->signature = $postRow['post_signature'];
            $this->subject = $postRow['post_subject'];
            $this->text = $postRow['post_text'];
            $this->editTime = $postRow['post_edit_time'];
            $this->editReason = $postRow['post_edit_reason'];
            $this->editUser = new User($postRow['post_edit_user']);
        }

        // Parse the markup
        $this->parsed = (new BBcode(htmlentities($this->text)))->toHTML();
    }

    // Time elapsed since creation
    public function timeElapsed()
    {
        return Main::timeElapsed($this->time);
    }

    // Time elapsed since last edit
    public function editTimeElapsed()
    {
        return Main::timeElapsed($this->editTime);
    }
}
