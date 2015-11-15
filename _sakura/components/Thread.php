<?php
/*
 * Thread class
 */

namespace Sakura;

/**
 * Class Thread
 * @package Sakura
 */
class Thread
{
    // Variables
    public $id = 0;
    public $forum = 0;
    public $hidden = 0;
    public $title = "";
    public $time = 0;
    public $timeLimit = 0;
    public $views = 0;
    public $status = 0;
    public $statusChange = 0;
    public $type = 0;
    public $firstPost = null;
    public $lastPost = null;
    public $posts = [];

    // Constructor
    public function __construct($threadId)
    {
        // Attempt to get the database row
        $threadRow = Database::fetch('topics', false, ['topic_id' => [$threadId, '=']]);

        // Assign data if a row was returned
        if ($threadRow) {
            $this->id = $threadRow['topic_id'];
            $this->forum = $threadRow['forum_id'];
            $this->hidden = $threadRow['topic_hidden'];
            $this->title = $threadRow['topic_title'];
            $this->time = $threadRow['topic_time'];
            $this->timeLimit = $threadRow['topic_time_limit'];
            $this->views = $threadRow['topic_views'];
            $this->status = $threadRow['topic_status'];
            $this->statusChange = $threadRow['topic_status_change'];
            $this->type = $threadRow['topic_type'];
        }

        // Populate the posts array
        $this->posts = $this->getPosts();

        // Get first post
        $this->firstPost = $this->posts ? array_values($this->posts)[0] : (new Thread(0));

        // And the last post
        $this->lastPost = $this->posts ? end($this->posts) : (new Thread(0));
    }

    // Posts
    public function getPosts()
    {
        // Get all rows with the thread id
        $postRows = Database::fetch('posts', true, ['topic_id' => [$this->id, '=']]);

        // Create a storage array
        $posts = [];

        // Create new post objects for each post
        foreach ($postRows as $post) {
            $posts[$post['post_id']] = new Post($post['post_id']);
        }

        // Return the post objects
        return $posts;
    }

    // Reply count
    public function replyCount()
    {
        return Database::count('posts', ['topic_id' => [$this->id, '=']])[0];
    }

    // Time elapsed since creation
    public function timeElapsed()
    {
        return Main::timeElapsed($this->time);
    }

    // Time elapsed since status change
    public function statusChangeElapsed()
    {
        return Main::timeElapsed($this->statusChange);
    }
}
