<?php
/*
 * Post class
 */

namespace Sakura\Forum;

use Sakura\Main;
use Sakura\Database;
use Sakura\User;
use Sakura\BBcode;
use Sakura\Config;

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
    private $_permissions;

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
            $this->poster = User::construct($postRow['poster_id']);
            $this->ip = $postRow['poster_ip'];
            $this->time = $postRow['post_time'];
            $this->signature = $postRow['post_signature'];
            $this->subject = $postRow['post_subject'];
            $this->text = $postRow['post_text'];
            $this->editTime = $postRow['post_edit_time'];
            $this->editReason = $postRow['post_edit_reason'];
            $this->editUser = User::construct($postRow['post_edit_user']);
        }

        // Parse the markup
        $this->parsed = BBcode::toHTML(htmlentities($this->text));
    }

    // Create a new post
    public static function create($subject, $text, User $poster, $thread = 0, $forum = 0)
    {
        // Check if the data meets the requirements
        if (strlen($subject) < Config::get('forum_title_min')
            || strlen($subject) > Config::get('forum_title_max')
            || strlen($text) < Config::get('forum_text_min')
            || strlen($text) > Config::get('forum_text_max')) {
            return null;
        }

        // If no thread is specified create a new one
        if ($thread) {
            $thread = new Thread($thread);
        } else {
            $thread = Thread::create($forum, $subject);
        }

        // Stop if the thread ID is 0
        if ($thread->id == 0) {
            return null;
        }

        // Insert the post
        Database::insert('posts', [
            'topic_id' => $thread->id,
            'forum_id' => $thread->forum,
            'poster_id' => $poster->id(),
            'poster_ip' => Main::getRemoteIP(),
            'post_time' => time(),
            'post_signature' => '1',
            'post_subject' => $subject,
            'post_text' => $text,
        ]);

        // Get post id
        $id = Database::lastInsertID();

        // Update the last post date
        $thread->lastUpdate();

        // Return the object
        return new Post($id);
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
