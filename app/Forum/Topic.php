<?php
/**
 * Holds the topic object class.
 * @package Sakura
 */

namespace Sakura\Forum;

use Sakura\DB;

/**
 * Used to serve, create and update topics.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Topic
{
    /**
     * The ID of this topic.
     * @var int
     */
    public $id = 0;

    /**
     * The ID of the forum this topic is a part of.
     * @var int
     */
    public $forum = 0;

    /**
     * Is this forum hidden from the listing?
     * @var bool
     */
    public $hidden = false;

    /**
     * The title of the topic.
     * @var string
     */
    public $title = "";

    /**
     * The UNIX timestamp of when this topic was created.
     * @var int
     */
    public $time = 0;

    /**
     * The UNIX timestamp of when this topic should be autolocked (currently unused).
     * @var int
     */
    public $timeLimit = 0;

    /**
     * The amount of times this topic has been viewed.
     * @var int
     */
    public $views = 0;

    /**
     * The status of this topic.
     * 0 - Unlocked
     * 1 - Locked
     * @var int
     */
    public $status = 0;

    /**
     * The UNIX timestamp of when the status was last changed.
     * @var int
     */
    public $statusChange = 0;

    /**
     * The topic type
     * 0 - Normal topic
     * 1 - Sticky topic
     * 2 - Announcement
     * @var int
     */
    public $type = 0;

    /**
     * The ID of the forum this topic was a part of before the last move.
     * @var int
     */
    public $oldForum = 0;

    /**
     * The post object cache.
     * @var array
     */
    private $postsCache = [];

    /**
     * A cached instance of opening post.
     * @var Post
     */
    private $firstPostCache = null;

    /**
     * A cached instance of the last reply.
     * @var Post
     */
    private $lastPostCache = null;

    /**
     * Constructor.
     * @param int $topicId
     */
    public function __construct($topicId)
    {
        // Attempt to get the database row
        $topicRow = DB::table('topics')
            ->where('topic_id', $topicId)
            ->first();

        // Assign data if a row was returned
        if ($topicRow) {
            $this->id = intval($topicRow->topic_id);
            $this->forum = intval($topicRow->forum_id);
            $this->hidden = boolval($topicRow->topic_hidden);
            $this->title = $topicRow->topic_title;
            $this->time = intval($topicRow->topic_time);
            $this->timeLimit = intval($topicRow->topic_time_limit);
            $this->views = intval($topicRow->topic_views);
            $this->status = intval($topicRow->topic_status);
            $this->statusChange = intval($topicRow->topic_status_change);
            $this->type = intval($topicRow->topic_type);
            $this->oldForum = intval($topicRow->topic_old_forum);
        }
    }

    /**
     * Create a new topic.
     * @param int $forum
     * @param string $title
     * @param int $status
     * @param int $type
     * @return Topic
     */
    public static function create($forum, $title, $status = 0, $type = 0)
    {
        // Create the database entry
        $id = DB::table('topics')
            ->insertGetId([
                'forum_id' => $forum,
                'topic_title' => $title,
                'topic_time' => time(),
                'topic_status' => $status,
                'topic_type' => $type,
            ]);

        // Return the topic object
        return new Topic($id);
    }

    /**
     * Delete the current topic.
     */
    public function delete()
    {
        // Delete all posts
        DB::table('posts')
            ->where('topic_id', $this->id)
            ->delete();

        // Delete topic meta
        DB::table('topics')
            ->where('topic_id', $this->id)
            ->delete();
    }

    /**
     * Move the topic.
     * @param int $forum
     * @param bool $setOld
     */
    public function move($forum, $setOld = true)
    {
        // Update all posts
        DB::table('posts')
            ->where('topic_id', $this->id)
            ->update(['forum_id' => $forum]);

        // Update topic meta
        DB::table('topics')
            ->where('topic_id', $this->id)
            ->update([
                'forum_id' => $forum,
                'topic_old_forum' => ($setOld ? $this->forum : 0),
            ]);
    }

    /**
     * Update the topic data.
     * @return Topic
     */
    public function update()
    {
        // Update row
        DB::table('topics')
            ->where('topic_id', $this->id)
            ->update([
                'topic_hidden' => $this->hidden,
                'topic_title' => $this->title,
                'topic_time_limit' => $this->timeLimit,
                'topic_status' => $this->status,
                'topic_status_change' => $this->statusChange,
                'topic_type' => $this->type,
                'topic_old_forum' => $this->oldForum,
            ]);

        // Return new object
        return new Topic($this->id);
    }

    /**
     * Get the replies to this topic.
     * @return array
     */
    public function posts()
    {
        // Check if postsCache is something
        if (!count($this->postsCache)) {
            // Get all rows with the topic id
            $postRows = DB::table('posts')
                ->where('topic_id', $this->id)
                ->get(['post_id']);

            // Create a storage array
            $posts = [];

            // Create new post objects for each post
            foreach ($postRows as $post) {
                $posts[$post->post_id] = new Post($post->post_id);
            }

            $this->postsCache = $posts;
        } else {
            $posts = $this->postsCache;
        }

        // Return the post objects
        return $posts;
    }

    /**
     * Get the opening post.
     * @return Post
     */
    public function firstPost()
    {
        // Check if the cache var is set
        if ($this->firstPostCache !== null) {
            return $this->firstPostCache;
        }

        // Get the row from the database
        $post = DB::table('posts')
            ->where('topic_id', $this->id)
            ->orderBy('post_id')
            ->limit(1)
            ->first(['post_id']);

        // Create the post class
        $post = new Post($post->post_id ?? 0);

        // Assign it to the cache var
        $this->firstPostCache = $post;

        // Return
        return $post;
    }

    /**
     * Get the latest reply.
     * @return Post
     */
    public function lastPost()
    {
        // Check if the cache var is set
        if ($this->lastPostCache !== null) {
            return $this->lastPostCache;
        }

        // Get the row from the database
        $post = DB::table('posts')
            ->where('topic_id', $this->id)
            ->orderBy('post_id', 'desc')
            ->limit(1)
            ->first(['post_id']);

        // Create the post class
        $post = new Post($post->post_id ?? 0);

        // Assign it to the cache var
        $this->lastPostCache = $post;

        // Return
        return $post;
    }

    /**
     * Get the amount of replies.
     * @return int
     */
    public function replyCount()
    {
        return DB::table('posts')
            ->where('topic_id', $this->id)
            ->count();
    }

    /**
     * Check if a user has read this topic before.
     * @param int $user
     * @return bool
     */
    public function unread($user)
    {
        // Return false if the user id is less than 1
        if ($user < 1) {
            return false;
        }

        // Attempt to get track row from the database
        $track = DB::table('topics_track')
            ->where('user_id', $user)
            ->where('topic_id', $this->id)
            ->where('mark_time', '>', $this->lastPost()->time)
            ->count();

        // If nothing was returned it's obvious that the status is unread
        if (!$track) {
            return true;
        }

        // Else just return false meaning everything is read
        return false;
    }

    /**
     * Update the read status.
     * @param int $user
     */
    public function trackUpdate($user)
    {
        // Check if we already have a track record
        $track = DB::table('topics_track')
            ->where('user_id', $user)
            ->where('topic_id', $this->id)
            ->where('forum_id', $this->forum)
            ->count();

        // Adding a second to this to avoid own posts getting marked unread
        $time = time() + 1;

        // If so update it
        if ($track) {
            DB::table('topics_track')
                ->where('user_id', $user)
                ->where('topic_id', $this->id)
                ->update(['mark_time' => $time]);
        } else {
            // If not create a new record
            DB::table('topics_track')
                ->insert([
                    'user_id' => $user,
                    'topic_id' => $this->id,
                    'forum_id' => $this->forum,
                    'mark_time' => $time,
                ]);
        }
    }

    /**
     * Update the view count.
     */
    public function viewsUpdate()
    {
        DB::table('topics')
            ->where('topic_id', $this->id)
            ->increment('topic_views');
    }

    /**
     * Update the timestamp of when this topic was last replied to.
     */
    public function lastUpdate()
    {
        DB::table('topics')
            ->where('topic_id', $this->id)
            ->update(['topic_last_reply' => time()]);
    }
}
