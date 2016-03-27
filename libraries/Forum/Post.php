<?php
/**
 * Holds the post object class.
 *
 * @package Sakura
 */

namespace Sakura\Forum;

use Sakura\BBcode;
use Sakura\Config;
use Sakura\DB;
use Sakura\Net;
use Sakura\User;

/**
 * Used to serve, create and update posts.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Post
{
    /**
     * The ID of the post.
     *
     * @var int
     */
    public $id = 0;

    /**
     * The id of the thread this post is a part of.
     *
     * @var int
     */
    public $thread = 0;

    /**
     * The id of the forum this post is a part of.
     *
     * @var int
     */
    public $forum = 0;

    /**
     * The User object of the poster.
     *
     * @var User
     */
    public $poster = null;

    /**
     * The IP address from which this post was created.
     *
     * @var string
     */
    public $ip = "";

    /**
     * The UNIX timestamp from when this post was created.
     *
     * @var int
     */
    public $time = 0;

    /**
     * The subject of this post.
     *
     * @var string
     */
    public $subject = "";

    /**
     * The raw contents of this post.
     *
     * @var string
     */
    public $text = "";

    /**
     * The parsed contents of this post.
     *
     * @var string
     */
    public $parsed = "";

    /**
     * The UNIX timestamp of the last time this post was edited.
     *
     * @var int
     */
    public $editTime = 0;

    /**
     * The reason why this post was edited.
     *
     * @var string
     */
    public $editReason = "";

    /**
     * The User object of the user that last edited this post.
     *
     * @var User
     */
    public $editUser = null;

    /**
     * Constructor.
     *
     * @param int $postId ID of the post that should be constructed.
     */
    public function __construct($postId)
    {
        // Attempt to get the database row
        $postRow = DB::table('posts')
            ->where('post_id', $postId)
            ->get();

        // Assign data if a row was returned
        if ($postRow) {
            $postRow = $postRow[0];
            $this->id = $postRow->post_id;
            $this->thread = $postRow->topic_id;
            $this->forum = $postRow->forum_id;
            $this->poster = User::construct($postRow->poster_id);
            $this->ip = $postRow->poster_ip;
            $this->time = $postRow->post_time;
            $this->subject = $postRow->post_subject;
            $this->text = $postRow->post_text;
            $this->editTime = $postRow->post_edit_time;
            $this->editReason = $postRow->post_edit_reason;
            $this->editUser = User::construct($postRow->post_edit_user);
        }

        // Parse the markup
        $this->parsed = BBcode::toHTML(htmlentities($this->text));
    }

    /**
     * Creating a new post.
     *
     * @param string $subject The subject of the thread.
     * @param string $text The raw contents of the post.
     * @param User $poster The User object of the poster.
     * @param int $thread The ID of the thread this post is a reply to.
     * @param mixed $forum The forum this post is a reply in.
     *
     * @return null|self Either null, indicating a failure, or the Post object.
     */
    public static function create($subject, $text, User $poster, $thread = 0, $forum = 0)
    {
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
        $id = DB::table('posts')
            ->insertGetId([
                'topic_id' => $thread->id,
                'forum_id' => $thread->forum,
                'poster_id' => $poster->id,
                'poster_ip' => Net::ip(),
                'post_time' => time(),
                'post_subject' => $subject,
                'post_text' => $text,
            ]);

        // Update the last post date
        $thread->lastUpdate();

        // Return the object
        return new Post($id);
    }

    /**
     * Commit the changes to the Database.
     *
     * @return null|Post Either null, indicating a failure, or the Post object.
     */
    public function update()
    {
        // Check if the data meets the requirements
        if (strlen($this->subject) < Config::get('forum_title_min')
            || strlen($this->subject) > Config::get('forum_title_max')
            || strlen($this->text) < Config::get('forum_text_min')
            || strlen($this->text) > Config::get('forum_text_max')) {
            return null;
        }

        // Create a thread object
        $thread = new Thread($this->thread);

        // Update the post
        DB::table('posts')
            ->where('post_id', $this->id)
            ->update([
                'topic_id' => $thread->id,
                'forum_id' => $thread->forum,
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

    public function delete()
    {
        DB::table('posts')
            ->where('post_id', $this->id)
            ->delete();
    }

    /**
     * Check if a user has read this post before.
     *
     * @param mixed $user The id of the user in question.
     *
     * @return bool A boolean indicating the read status.
     */
    public function unread($user)
    {
        // Attempt to get track row from the database
        $track = DB::table('topics_track')
            ->where('user_id', $user)
            ->where('topic_id', $this->thread)
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
