<?php
/*
 * Forum class
 */

namespace Sakura;

/**
 * Class Forum
 * @package Sakura
 */
class Forum
{
    // Variables
    public $id = 0;
    public $name = "Forum";
    public $description = "";
    public $link = "";
    public $category = 0;
    public $type = 0;
    public $icon = "";
    public $firstPost = null;
    public $lastPost = null;
    public $forums = [];
    public $threads = [];

    // Constructor
    public function __construct($forumId = 0)
    {
        // Get the row from the database
        $forumRow = Database::fetch('forums', false, ['forum_id' => [$forumId, '=']]);

        // Populate the variables
        if ($forumRow) {
            $this->id = $forumRow['forum_id'];
            $this->name = $forumRow['forum_name'];
            $this->description = $forumRow['forum_desc'];
            $this->link = $forumRow['forum_link'];
            $this->category = $forumRow['forum_category'];
            $this->type = $forumRow['forum_type'];
            $this->icon = $forumRow['forum_icon'];
        } elseif ($forumId != 0) {
            $this->id = -1;
        }

        // Populate the forums array
        $this->forums = $this->getForums();

        // and the threads array
        $this->threads = $this->getThreads();

        // and the first post
        $this->firstPost = $this->getFirstPost();

        // and finally the last post
        $this->lastPost = $this->getLastPost();
    }

    // Subforums
    public function getForums()
    {
        // Get all rows with the category id set to the forum id
        $forumRows = Database::fetch('forums', true, ['forum_category' => [$this->id, '=']]);

        // Create a storage array
        $forums = [];

        // Create new objects for each forum
        foreach ($forumRows as $forum) {
            $forums[$forum['forum_id']] = new Forum($forum['forum_id']);
        }

        // Return the forum objects
        return $forums;
    }

    // Threads
    public function getThreads()
    {
        // Get all rows with the forum id for this forum
        $threadRows = Database::fetch('topics', true, ['forum_id' => [$this->id, '=']]);

        // Create a storage array
        $threads = [];

        // Create new objects for each thread
        foreach ($threadRows as $thread) {
            $threads[$thread['topic_id']] = new Thread($thread['topic_id']);
        }

        // Return the thread objects
        return $threads;
    }

    // First post
    public function getFirstPost()
    {
        // Get the row
        $firstPost = Database::fetch('posts', false, ['forum_id' => [$this->id, '=']], ['post_id'], [1]);

        // Create the post object
        $post = new Post(empty($firstPost) ? 0 : $firstPost['post_id']);

        // Return the post object
        return $post;
    }

    // Last post
    public function getLastPost()
    {
        // Get the row
        $lastPost = Database::fetch('posts', false, ['forum_id' => [$this->id, '=']], ['post_id', true], [1]);

        // Create the post object
        $post = new Post(empty($lastPost) ? 0 : $lastPost['post_id']);

        // Return the post object
        return $post;
    }

    // Thread count
    public function threadCount()
    {
        return Database::count('topics', ['forum_id' => [$this->id, '=']])[0];
    }

    // Post count
    public function postCount()
    {
        return Database::count('posts', ['forum_id' => [$this->id, '=']])[0];
    }
}
