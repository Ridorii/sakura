<?php
/**
 * Holds the forum object class.
 * @package Sakura
 */

namespace Sakura\Forum;

use Sakura\DB;
use Sakura\CurrentSession;

/**
 * Used to serve forums.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Forum
{
    /**
     * The ID of the forum.
     * @var int
     */
    public $id = 0;

    /**
     * The order of the forum.
     * @var int
     */
    public $order = 0;

    /**
     * The name of the forum.
     * @var string
     */
    public $name = "Forum";

    /**
     * The description of the forum.
     * @var string
     */
    public $description = "";

    /**
     * The link of the forum (if the type is 2).
     * @var string
     */
    public $link = "";

    /**
     * The ID of the parent forum.
     * @var int
     */
    public $category = 0;

    /**
     * The type of forum.
     * @var int
     */
    public $type = 0;

    /**
     * The icon of this forum.
     * @var string
     */
    public $icon = "";

    /**
     * Holds the permission handler.
     * @var ForumPerms
     */
    public $perms;

    /**
     * A cached instance of the first post in this forum.
     * @var Post
     */
    private $firstPostCache = null;

    /**
     * A cached instance of the last post in this forum.
     * @var Post
     */
    private $lastPostCache = null;

    /**
     * Cached instances of the subforums.
     * @var array
     */
    private $forumsCache = [];

    /**
     * Cached instances of the topics in this forum.
     * @var array
     */
    private $topicsCache = [];

    /**
     * Constructor.
     * @param int $forumId
     */
    public function __construct(int $forumId = 0)
    {
        // Get the row from the database
        $forumRow = DB::table('forums')
            ->where('forum_id', $forumId)
            ->first();

        // Populate the variables
        if ($forumRow) {
            $this->id = intval($forumRow->forum_id);
            $this->order = intval($forumRow->forum_order);
            $this->name = $forumRow->forum_name;
            $this->description = $forumRow->forum_desc;
            $this->link = $forumRow->forum_link;
            $this->category = intval($forumRow->forum_category);
            $this->type = intval($forumRow->forum_type);
            $this->icon = $forumRow->forum_icon;
        } elseif ($forumId !== 0) {
            $this->id = -1;
        }

        $this->perms = new ForumPerms($this, CurrentSession::$user);
    }

    /**
     * Gets all subforums of this forum.
     * @return array
     */
    public function forums()
    {
        // Check if forumsCache is populated
        if (!count($this->forumsCache)) {
            // Get all rows with the category id set to the forum id
            $forumRows = DB::table('forums')
                ->where('forum_category', $this->id)
                ->orderBy('forum_order')
                ->get(['forum_id']);

            // Create a storage array
            $forums = [];

            // Create new objects for each forum
            foreach ($forumRows as $forum) {
                $forums[$forum->forum_id] = new Forum($forum->forum_id);
            }

            $this->forumsCache = $forums;
        }

        return $this->forumsCache;
    }

    /**
     * Gets the topics in this forum.
     * @return array
     */
    public function topics()
    {
        // Check if topicsCache is populated
        if (!count($this->topicsCache)) {
            // Get all rows with the forum id for this forum
            $topicRows = DB::table('topics')
                ->where('forum_id', $this->id)
                ->orderBy('topic_type', 'desc')
                ->orderBy('topic_last_reply', 'desc')
                ->get(['topic_id']);

            // Create a storage array
            $topics = [];

            // Create new objects for each topic
            foreach ($topicRows as $topic) {
                $topics[$topic->topic_id] = new Topic($topic->topic_id);
            }

            $this->topicsCache = $topics;
        } else {
            $topics = $this->topicsCache;
        }

        // Return the topic objects
        return $topics;
    }

    /**
     * Gets the first post in this forum.
     * @return Post
     */
    public function firstPost()
    {
        // Check if firstPostCache is set
        if ($this->firstPostCache === null) {
            // Get the row
            $firstPost = DB::table('posts')
                ->where('forum_id', $this->id)
                ->orderBy('post_id')
                ->limit(1)
                ->first(['post_id']);

            // Create the post object
            $post = new Post($firstPost->post_id ?? 0);

            // Assign it to a "cache" variable
            $this->firstPostCache = $post;

            // Return the post object
            return $post;
        } else {
            return $this->firstPostCache;
        }
    }

    /**
     * Gets the last post in this forum.
     * @return Post
     */
    public function lastPost()
    {
        // Check if lastPostCache is set
        if ($this->lastPostCache === null) {
            // Get the row
            $lastPost = DB::table('posts')
                ->where('forum_id', $this->id)
                ->orderBy('post_id', 'desc')
                ->limit(1)
                ->first(['post_id']);

            // Create the post object
            $post = new Post($lastPost->post_id ?? 0);

            // Assign it to a "cache" variable
            $this->lastPostCache = $post;

            // Return the post object
            return $post;
        } else {
            return $this->lastPostCache;
        }
    }

    /**
     * Counts the amount of topics in this forum.
     * @return int
     */
    public function topicCount()
    {
        return DB::table('topics')
            ->where('forum_id', $this->id)
            ->count();
    }

    /**
     * Counts the amount of posts in this forum.
     * @return int
     */
    public function postCount()
    {
        return DB::table('posts')
            ->where('forum_id', $this->id)
            ->count();
    }

    /**
     * Checks if a user has read every post in the specified forum.
     * @param int $user
     * @return bool
     */
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

        // Check each topic
        foreach ($this->topics() as $topic) {
            if ($topic->unread($user)) {
                return true;
            }
        }

        // Return false if negative
        return false;
    }

    /**
     * Update the read status of all topics in this forum at once.
     * @param int $user
     */
    public function trackUpdateAll($user)
    {
        // Iterate over every forum
        foreach ($this->forums() as $forum) {
            // Update every forum
            $forum->trackUpdateAll($user);
        }

        // Iterate over every topic
        foreach ($this->topics() as $topic) {
            // Update every topic
            $topic->trackUpdate($user);
        }
    }
}
