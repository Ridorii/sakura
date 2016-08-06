<?php
/**
 * Holds the post object class.
 * @package Sakura
 */

namespace Sakura\Forum;

use Sakura\BBcode;
use Sakura\DB;
use Sakura\Exception;
use Sakura\Net;
use Sakura\User;

/**
 * Used to serve, create and update posts.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Post
{
    /**
     * The ID of the post.
     * @var int
     */
    public $id = 0;

    /**
     * The id of the topic this post is a part of.
     * @var int
     */
    public $topic = 0;

    /**
     * The id of the forum this post is a part of.
     * @var int
     */
    public $forum = 0;

    /**
     * The User object of the poster.
     * @var User
     */
    public $poster = null;

    /**
     * The IP address from which this post was created.
     * @var string
     */
    public $ip = "";

    /**
     * The UNIX timestamp from when this post was created.
     * @var int
     */
    public $time = 0;

    /**
     * The subject of this post.
     * @var string
     */
    public $subject = "";

    /**
     * The raw contents of this post.
     * @var string
     */
    public $text = "";

    /**
     * The parsed contents of this post.
     * @var string
     */
    public $parsed = "";

    /**
     * The UNIX timestamp of the last time this post was edited.
     * @var int
     */
    public $editTime = 0;

    /**
     * The reason why this post was edited.
     * @var string
     */
    public $editReason = "";

    /**
     * The User object of the user that last edited this post.
     * @var User
     */
    public $editUser = null;

    /**
     * Constructor.
     * @param int $postId
     */
    public function __construct($postId)
    {
        // Attempt to get the database row
        $postRow = DB::table('posts')
            ->where('post_id', $postId)
            ->first();

        // Assign data if a row was returned
        if ($postRow) {
            $this->id = intval($postRow->post_id);
            $this->topic = intval($postRow->topic_id);
            $this->forum = intval($postRow->forum_id);
            $this->poster = User::construct($postRow->poster_id);
            $this->time = intval($postRow->post_time);
            $this->subject = $postRow->post_subject;
            $this->text = $postRow->post_text;
            $this->editTime = intval($postRow->post_edit_time);
            $this->editReason = $postRow->post_edit_reason;
            $this->editUser = User::construct($postRow->post_edit_user);

            // Temporary backwards compatible IP storage system
            try {
                $this->ip = Net::ntop($postRow->poster_ip);
            } catch (Exception $e) {
                $this->ip = $postRow->poster_ip;
                $this->update();
            }
        }

        // Parse the markup
        $this->parsed = BBcode::toHTML(htmlentities($this->text));
    }

    /**
     * Creating a new post.
     * @param string $subject
     * @param string $text
     * @param User $poster
     * @param int $topic
     * @param int $forum
     * @return Post
     */
    public static function create($subject, $text, User $poster, $topic = 0, $forum = 0)
    {
        // If no topic is specified create a new one
        if ($topic) {
            $topic = new Topic($topic);
        } else {
            $topic = Topic::create($forum, $subject);
        }

        // Stop if the topic ID is 0
        if ($topic->id == 0) {
            return null;
        }

        // Insert the post
        $id = DB::table('posts')
            ->insertGetId([
                'topic_id' => $topic->id,
                'forum_id' => $topic->forum,
                'poster_id' => $poster->id,
                'poster_ip' => Net::pton(Net::ip()),
                'post_time' => time(),
                'post_subject' => $subject,
                'post_text' => $text,
            ]);

        // Update the last post date
        $topic->lastUpdate();

        // Return the object
        return new Post($id);
    }

    /**
     * Commit the changes to the Database.
     * @return Post
     */
    public function update()
    {
        // Create a topic object
        $topic = new Topic($this->topic);

        // Update the post
        DB::table('posts')
            ->where('post_id', $this->id)
            ->update([
                'topic_id' => $topic->id,
                'forum_id' => $topic->forum,
                'poster_id' => $this->poster->id,
                'poster_ip' => Net::pton(Net::ip()),
                'post_time' => $this->time,
                'post_subject' => $this->subject,
                'post_text' => $this->text,
                'post_edit_time' => $this->editTime,
                'post_edit_reason' => $this->editReason,
                'post_edit_user' => $this->editUser->id,
            ]);

        // Return a new post object
        return new Post($this->id);
    }

    /**
     * delete this.
     */
    public function delete()
    {
        DB::table('posts')
            ->where('post_id', $this->id)
            ->delete();
    }

    /**
     * Check if a user has read this post before.
     * @param mixed $user
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
            ->where('topic_id', $this->topic)
            ->where('mark_time', '>', $this->time)
            ->count();

        // If nothing was returned it's obvious that the status is unread
        if (!$track) {
            return true;
        }

        // Else just return false meaning everything is read
        return false;
    }
}
