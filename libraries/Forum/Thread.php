<?php
/**
 * Holds the thread object class.
 * 
 * @package Sakura
 */

namespace Sakura\Forum;

use Sakura\Database;
use Sakura\Utils;

/**
 * Used to serve, create and update threads.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Thread
{
    /**
     * The ID of this thread.
     * 
     * @var int
     */
    public $id = 0;

    /**
     * The ID of the forum this thread is a part of.
     * 
     * @var int
     */
    public $forum = 0;

    /**
     * Is this forum hidden from the listing?
     * 
     * @var bool
     */
    public $hidden = false;

    /**
     * The title of the thread.
     * 
     * @var string
     */
    public $title = "";

    /**
     * The UNIX timestamp of when this thread was created.
     * 
     * @var int
     */
    public $time = 0;

    /**
     * The UNIX timestamp of when this thread should be autolocked (currently unused).
     * 
     * @var int
     */
    public $timeLimit = 0;

    /**
     * The amount of times this thread has been viewed.
     * 
     * @var int
     */
    public $views = 0;

    /**
     * The status of this thread.
     * 0 - Unlocked
     * 1 - Locked
     * 
     * @var int
     */
    public $status = 0;

    /**
     * The UNIX timestamp of when the status was last changed.
     * 
     * @var int
     */
    public $statusChange = 0;

    /**
     * The thread type
     * 0 - Normal thread
     * 1 - Sticky thread
     * 2 - Announcement
     * 
     * @var int
     */
    public $type = 0;

    /**
     * The ID of the forum this thread was a part of before the last move.
     * 
     * @var int
     */
    public $oldForum = 0;

    /**
     * The post object cache.
     * 
     * @var array
     */
    private $_posts = [];

    /**
     * A cached instance of opening post.
     * 
     * @var Post
     */
    private $_firstPost = null;

    /**
     * A cached instance of the last reply.
     * 
     * @var Post
     */
    private $_lastPost = null;

    /**
     * Constructor.
     * 
     * @param mixed $threadId ID of the thread that should be constructed.
     */
    public function __construct($threadId)
    {
        // Attempt to get the database row
        $threadRow = Database::fetch('topics', false, ['topic_id' => [$threadId, '=']]);

        // Assign data if a row was returned
        if ($threadRow) {
            $this->id = $threadRow['topic_id'];
            $this->forum = $threadRow['forum_id'];
            $this->hidden = (bool) $threadRow['topic_hidden'];
            $this->title = $threadRow['topic_title'];
            $this->time = $threadRow['topic_time'];
            $this->timeLimit = $threadRow['topic_time_limit'];
            $this->views = $threadRow['topic_views'];
            $this->status = $threadRow['topic_status'];
            $this->statusChange = $threadRow['topic_status_change'];
            $this->type = $threadRow['topic_type'];
            $this->oldForum = $threadRow['topic_old_forum'];
        }
    }

    /**
     * Create a new thread.
     * 
     * @param mixed $forum ID of the forum this thread is part of.
     * @param mixed $title Title of the thread.
     * @param mixed $status Status of the thread.
     * @param mixed $type Type of thread.
     * 
     * @return Thread The new thread instance.
     */
    public static function create($forum, $title, $status = 0, $type = 0)
    {
        // Create the database entry
        Database::insert('topics', [
            'forum_id' => $forum,
            'topic_title' => $title,
            'topic_time' => time(),
            'topic_status' => $status,
            'topic_type' => $type,
        ]);

        // Return the thread object
        return new Thread(Database::lastInsertID());
    }

    /**
     * Delete the current thread.
     */
    public function delete()
    {
        // Delete all posts
        Database::delete('posts', [
            'topic_id' => [$this->id, '='],
        ]);

        // Delete thread meta
        Database::delete('topics', [
            'topic_id' => [$this->id, '='],
        ]);
    }

    /**
     * Move the thread.
     * 
     * @param mixed $forum The new forum ID.
     * @param mixed $setOld Remember the forum ID prior to the move for restoration.
     */
    public function move($forum, $setOld = true)
    {
        // Update all posts
        Database::update('posts', [
            [
                'forum_id' => $forum,
            ],
            [
                'topic_id' => [$this->id, '='],
            ]
        ]);

        // Update thread meta
        Database::update('topics', [
            [
                'forum_id' => $forum,
                'topic_old_forum' => ($setOld ? $this->forum : 0),
            ],
            [
                'topic_id' => [$this->id, '='],
            ]
        ]);
    }

    /**
     * Update the thread data.
     * 
     * @return Thread The updated thread.
     */
    public function update()
    {
        // Update row
        Database::update('topics', [
            [
                'topic_hidden' => $this->hidden,
                'topic_title' => $this->title,
                'topic_time_limit' => $this->timeLimit,
                'topic_status' => $this->status,
                'topic_status_change' => $this->statusChange,
                'topic_type' => $this->type,
                'topic_old_forum' => $this->oldForum,
            ],
            [
                'topic_id' => [$this->id, '='],
            ]
        ]);

        // Return new object
        return new Thread($this->id);
    }

    /**
     * Get the replies to this thread.
     * 
     * @return array Array containing Post instances.
     */
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

    /**
     * Get the opening post.
     * 
     * @return Post A Post instance of the opening post.
     */
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

    /**
     * Get the latest reply.
     * 
     * @return Post A Post instance of the latest reply.
     */
    public function lastPost()
    {
        // Check if the cache var is set
        if ($this->_lastPost !== null) {
            return $this->_lastPost;
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

    /**
     * Get the amount of replies.
     * 
     * @return int The number of replies to this thread.
     */
    public function replyCount()
    {
        return Database::count('posts', ['topic_id' => [$this->id, '=']])[0];
    }

    /**
     * Check if a user has read this thread before.
     * 
     * @param mixed $user The id of the user in question.
     * 
     * @return bool A boolean indicating the read status.
     */
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

    /**
     * Update the read status.
     * 
     * @param mixed $user The id of the user in question.
     */
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

    /**
     * Update the view count.
     */
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

    /**
     * Update the timestamp of when this thread was last replied to.
     */
    public function lastUpdate()
    {
        Database::update('topics', [
            [
                'topic_last_reply' => time(),
            ],
            [
                'topic_id' => [$this->id, '='],
            ],
        ]);
    }
}
