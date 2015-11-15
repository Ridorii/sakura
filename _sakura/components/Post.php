<?php
/*
 * Post class
 */

namespace Sakura;

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
            $this->parse = $postRow['post_parse'];
            $this->signature = $postRow['post_signature'];
            $this->emotes = $postRow['post_emotes'];
            $this->subject = $postRow['post_subject'];
            $this->text = $postRow['post_text'];
            $this->editTime = $postRow['post_edit_time'];
            $this->editReason = $postRow['post_edit_reason'];
            $this->editUser = new User($postRow['post_edit_user']);
        }

        // Parse the markup
        $this->parsed = Forums::parseMarkUp($this->text, $this->parse, $this->emotes);
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
