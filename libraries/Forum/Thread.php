<?php
/*
 * Thread class
 */

namespace Sakura\Forum;

use Sakura\Database;
use Sakura\Main;

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
    private $_posts = [];
    private $_firstPost = null;
    private $_lastPost = null;

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
    }

    // Posts
    public function posts()
    {
        // Check if _posts is something
        if (!count($this->_posts)) {
            // Get all rows with the thread id
            $postRows = Database::fetch('posts', true, ['topic_id' => [$this->id, '=']]);

            // Create a storage array
            $posts = [];

            // Create new post objects for each post
            foreach ($postRows as $post) {
                $posts[$post['post_id']] = new Post($post['post_id']);
            }

            $this->_posts = $posts;
        } else {
            $posts = $this->_posts;
        }

        // Return the post objects
        return $posts;
    }

    // Get the opening post
    public function firstPost()
    {
        // Check if the cache var is set
        if ($this->_firstPost !== null) {
            return $this->_firstPost;
        }

        // Get the row from the database
        $post = Database::fetch('posts', false, ['topic_id' => [$this->id, '=']], ['post_id'], [1]);

        // Create the post class
        $post = new Post($post ? $post['post_id'] : 0);

        // Assign it to the cache var
        $this->_firstPost = $post;

        // Return
        return $post;
    }

    // Get the last reply
    public function lastPost()
    {
        // Check if the cache var is set
        if ($this->_lastPost !== null) {
            return $this->_firstPost;
        }

        // Get the row from the database
        $post = Database::fetch('posts', false, ['topic_id' => [$this->id, '=']], ['post_id', true], [1]);

        // Create the post class
        $post = new Post($post ? $post['post_id'] : 0);

        // Assign it to the cache var
        $this->_lastPost = $post;

        // Return
        return $post;
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

    // Read status
    public function unread($user)
    {
        // Attempt to get track row from the database
        $track = Database::fetch('topics_track', false, ['user_id' => [$user, '='], 'topic_id' => [$this->id, '='], 'mark_time' => [$this->lastPost()->time, '>']]);

        // If nothing was returned it's obvious that the status is unread
        if (!$track) {
            return true;
        }

        // Else just return false meaning everything is read
        return false;
    }

    // Update read status
    public function trackUpdate($user)
    {
        // Check if we already have a track record
        $track = Database::fetch('topics_track', false, ['user_id' => [$user, '='], 'topic_id' => [$this->id, '='], 'forum_id' => [$this->forum, '=']]);

        // If so update it
        if ($track) {
            Database::update('topics_track', [
                [
                    'mark_time' => time(),
                ],
                [
                    'user_id' => [$user, '='],
                    'topic_id' => [$this->id, '='],
                ],
            ]);
        } else {
            // If not create a new record
            Database::insert('topics_track', [
                'user_id' => $user,
                'topic_id' => $this->id,
                'forum_id' => $this->forum,
                'mark_time' => time(),
            ]);
        }
    }

    // Update views
    public function viewsUpdate()
    {
        Database::update('topics', [
            [
                'topic_views' => $this->views + 1,
            ],
            [
                'topic_id' => [$this->id, '='],
            ],
        ]);
    }
}
