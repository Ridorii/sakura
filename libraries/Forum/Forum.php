<?php
/*
 * Forum class
 */

namespace Sakura\Forum;

use Sakura\Database;
use Sakura\Users;
use Sakura\User;
use Sakura\Perms;

/**
 * Class Forum
 * @package Sakura
 */
class Forum
{
    // Variables
    public $id = 0;
    public $order = 0;
    public $name = "Forum";
    public $description = "";
    public $link = "";
    public $category = 0;
    public $type = 0;
    public $icon = "";
    private $_firstPost = null;
    private $_lastPost = null;
    private $_forums = [];
    private $_threads = [];
    private $_permissions;

    // Constructor
    public function __construct($forumId = 0)
    {
        // Get the row from the database
        $forumRow = Database::fetch('forums', false, ['forum_id' => [$forumId, '=']]);

        // Create permissions object
        $this->_permissions = new Perms(Perms::FORUM);

        // Populate the variables
        if ($forumRow) {
            $this->id = $forumRow['forum_id'];
            $this->order = $forumRow['forum_order'];
            $this->name = $forumRow['forum_name'];
            $this->description = $forumRow['forum_desc'];
            $this->link = $forumRow['forum_link'];
            $this->category = $forumRow['forum_category'];
            $this->type = $forumRow['forum_type'];
            $this->icon = $forumRow['forum_icon'];
        } elseif ($forumId != 0) {
            $this->id = -1;
        }
    }

    // Checking a permission
    public function permission($flag, $user, $raw = false)
    {
        // Set default permission value
        $perm = 0;

        // Get the permissions of the parent forum if there is one
        if ($this->category) {
            $perm = $perm | (new Forum($this->category))->permission($flag, $user, true);
        }

        // Bitwise OR it with the permissions for this forum
        $perm = $perm | $this->_permissions->user($user, ['forum_id' => [$this->id, '=']]);

        return $raw ? $perm : $this->_permissions->check($flag, $perm);
    }

    // Subforums
    public function forums()
    {
        // Check if _forums is populated
        if (!count($this->_forums)) {
            // Get all rows with the category id set to the forum id
            $forumRows = Database::fetch('forums', true, ['forum_category' => [$this->id, '=']], ['forum_order']);

            // Create a storage array
            $forums = [];

            // Create new objects for each forum
            foreach ($forumRows as $forum) {
                $forums[$forum['forum_id']] = new Forum($forum['forum_id']);
            }

            $this->_forums = $forums;
        } else {
            $forums = $this->_forums;
        }

        // Return the forum objects
        return $forums;
    }

    // Threads
    public function threads()
    {
        // Check if _threads is populated
        if (!count($this->_threads)) {
            // Get all rows with the forum id for this forum
            $announcements = Database::fetch('topics', true, ['forum_id' => [$this->id, '='], 'topic_type' => ['2', '=']], ['topic_last_reply', true]);
            $sticky = Database::fetch('topics', true, ['forum_id' => [$this->id, '='], 'topic_type' => ['1', '=']], ['topic_last_reply', true]);
            $regular = Database::fetch('topics', true, ['forum_id' => [$this->id, '='], 'topic_type' => ['0', '=']], ['topic_last_reply', true]);

            // Combine them into one array
            $threadRows = array_merge($announcements, $sticky, $regular);

            // Create a storage array
            $threads = [];

            // Create new objects for each thread
            foreach ($threadRows as $thread) {
                $threads[$thread['topic_id']] = new Thread($thread['topic_id']);
            }

            $this->_threads = $threads;
        } else {
            $threads = $this->_threads;
        }

        // Return the thread objects
        return $threads;
    }

    // First post
    public function firstPost()
    {
        // Check if _firstPost is set
        if ($this->_firstPost === null) {
            // Get the row
            $firstPost = Database::fetch('posts', false, ['forum_id' => [$this->id, '=']], ['post_id'], [1]);

            // Create the post object
            $post = new Post(empty($firstPost) ? 0 : $firstPost['post_id']);

            // Assign it to a "cache" variable
            $this->_firstPost = $post;

            // Return the post object
            return $post;
        } else {
            return $this->_firstPost;
        }
    }

    // Last post
    public function lastPost()
    {
        // Check if _lastPost is set
        if ($this->_lastPost === null) {
            // Get the row
            $lastPost = Database::fetch('posts', false, ['forum_id' => [$this->id, '=']], ['post_id', true], [1]);

            // Create the post object
            $post = new Post(empty($lastPost) ? 0 : $lastPost['post_id']);

            // Assign it to a "cache" variable
            $this->_lastPost = $post;

            // Return the post object
            return $post;
        } else {
            return $this->_lastPost;
        }
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

    // Read status
    public function unread($user)
    {
        // Return false if the user id is less than 1
        if ($user < 1) {
            return false;
        }

        // Check forums
        foreach ($this->forums() as $forum) {
            if ($forum->unread($user)) {
                return true;
            }
        }

        // Check each thread
        foreach ($this->threads() as $thread) {
            if ($thread->unread($user)) {
                return true;
            }
        }

        // Return false if negative
        return false;
    }

    // Mark all threads as read
    public function trackUpdateAll($user)
    {
        // Iterate over every forum
        foreach ($this->forums() as $forum) {
            // Update every forum
            $forum->trackUpdateAll($user);
        }

        // Iterate over every thread
        foreach ($this->threads() as $thread) {
            // Update every thread
            $thread->trackUpdate($user);
        }
    }
}
