<?php
/**
 * Holds the thread object class.
 * 
 * @package Sakura
 */

namespace Sakura\Forum;

use Sakura\Database;
use Sakura\DB;
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
        $threadRow = DB::prepare('SELECT * FROM `{prefix}topics` WHERE `topic_id` = :id');
        $threadRow->execute([
            'id' => $threadId,
        ]);
        $threadRow = $threadRow->fetch();

        // Assign data if a row was returned
        if ($threadRow) {
            $this->id = $threadRow->topic_id;
            $this->forum = $threadRow->forum_id;
            $this->hidden = (bool) $threadRow->topic_hidden;
            $this->title = $threadRow->topic_title;
            $this->time = $threadRow->topic_time;
            $this->timeLimit = $threadRow->topic_time_limit;
            $this->views = $threadRow->topic_views;
            $this->status = $threadRow->topic_status;
            $this->statusChange = $threadRow->topic_status_change;
            $this->type = $threadRow->topic_type;
            $this->oldForum = $threadRow->topic_old_forum;
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
     * @return self The new thread instance.
     */
    public static function create($forum, $title, $status = 0, $type = 0)
    {
        // Create the database entry
        DB::prepare('INSERT INTO `{prefix}topics` (`forum_id`, `topic_title`, `topic_time`, `topic_status`, `topic_type`) VALUES (:forum, :title, :time, :status, :type)')
            ->execute([
            'forum' => $forum,
            'title' => $title,
            'time' => time(),
            'status' => $status,
            'type' => $type,
        ]);

        // Return the thread object
        return new Thread(DB::lastID());
    }

    /**
     * Delete the current thread.
     */
    public function delete()
    {
        // Delete all posts
        DB::prepare('DELETE FROM `{prefix}posts` WHERE `topic_id` = :id')
            ->execute([
            'id' => $this->id,
        ]);

        // Delete thread meta
        DB::prepare('DELETE FROM `{prefix}topics` WHERE `topic_id` = :id')
            ->execute([
            'id' => $this->id,
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
        DB::prepare('UPDATE `{prefix}posts` SET `forum_id` = :forum WHERE `topic_id` = :thread')
            ->execute([
            'forum' => $forum,
            'thread' => $this->id,
        ]);

        // Update thread meta
        DB::prepare('UPDATE `{prefix}topics` SET `forum_id` = :forum, `topic_old_forum` = :old WHERE `topic_id` = :thread')
            ->execute([
            'forum' => $forum,
            'old' => ($setOld ? $this->forum : 0),
            'thread' => $this->id,
        ]);
    }

    /**
     * Update the thread data.
     * 
     * @return self The updated thread.
     */
    public function update()
    {
        // Update row
        DB::prepare('UPDATE `{prefix}topics` SET `topic_hidden` = :hidden, `topic_title` = :title, `topic_time_limit` = :limit, `topic_status` = :status, `topic_status_change` = :change, `topic_type` = :type, `topic_old_forum` = :old WHERE `topic_id` = :id')
            ->execute([
                'hidden' => $this->hidden,
                'title' => $this->title,
                'limit' => $this->timeLimit,
                'status' => $this->status,
                'change' => $this->statusChange,
                'type' => $this->type,
                'old' => $this->oldForum,
                'id' => $this->id,
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
            $postRows = DB::prepare('SELECT `post_id` FROM `{prefix}posts` WHERE `topic_id` = :thread');
            $postRows->execute([
                'thread' => $this->id,
            ]);
            $postRows = $postRows->fetchAll();

            // Create a storage array
            $posts = [];

            // Create new post objects for each post
            foreach ($postRows as $post) {
                $posts[$post->post_id] = new Post($post->post_id);
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
        $post = DB::prepare('SELECT `post_id` FROM `{prefix}posts` WHERE `topic_id` = :thread ORDER BY `post_id` LIMIT 1');
        $post->execute([
            'thread' => $this->id,
        ]);
        $post = $post->fetch();

        // Create the post class
        $post = new Post($post ? $post->post_id : 0);

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
        $post = DB::prepare('SELECT `post_id` FROM `{prefix}posts` WHERE `topic_id` = :thread ORDER BY `post_id` DESC LIMIT 1');
        $post->execute([
            'thread' => $this->id,
        ]);
        $post = $post->fetch();

        // Create the post class
        $post = new Post($post ? $post->post_id : 0);

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
        $count = DB::prepare('SELECT * FROM `{prefix}posts` WHERE `topic_id` = :thread');
        $count->execute([
            'thread' => $this->id,
        ]);
        return $count->rowCount();
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
        $track = DB::prepare('SELECT * FROM `{prefix}topics_track` WHERE `user_id` = :user AND `topic_id` = :thread AND `mark_time` > :last');
        $track->execute([
            'user' => $user,
            'thread' => $this->id,
            'last' => $this->lastPost()->time,
        ]);

        // If nothing was returned it's obvious that the status is unread
        if (!$track->rowCount()) {
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
        $track = DB::prepare('SELECT * FROM `{prefix}topics_track` WHERE `user_id` = :user AND `topic_id` = :thread AND `forum_id` = :forum');
        $track->execute([
            'user' => $user,
            'thread' => $this->id,
            'forum' => $this->forum,
        ]);

        // If so update it
        if ($track->rowCount()) {
            DB::prepare('UPDATE `{prefix}topics_track` SET `mark_time` = :time WHERE `user_id` = :user AND `topic_id` = :thread')
                ->execute([
                'user' => $user,
                'thread' => $this->id,
                'time' => time(),
            ]);
        } else {
            // If not create a new record
            DB::prepare('INSERT INTO `{prefix}topics_track` (`user_id`, `topic_id`, `forum_id`, `mark_time`) VALUES (:user, :thread, :forum, :time)')
                ->execute([
                'user' => $user,
                'thread' => $this->id,
                'forum' => $this->forum,
                'time' => time(),
            ]);
        }
    }

    /**
     * Update the view count.
     */
    public function viewsUpdate()
    {
        DB::prepare('UPDATE `{prefix}topics` SET `topic_views` = :views WHERE `topic_id` = :thread')
            ->execute([
            'views' => $this->views + 1,
            'thread' => $this->id,
        ]);
    }

    /**
     * Update the timestamp of when this thread was last replied to.
     */
    public function lastUpdate()
    {
        DB::prepare('UPDATE `{prefix}topics` SET `topic_last_reply` = :last WHERE `topic_id` = :thread')
            ->execute([
            'last' => time(),
            'thread' => $this->id,
        ]);
    }
}
