<?php
/**
 * Holds the controller for posts.
 * @package Sakura
 */

namespace Sakura\Controllers\Forum;

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Sakura\CurrentSession;
use Sakura\DB;
use Sakura\Forum\Forum;
use Sakura\Forum\Post;
use Sakura\Forum\Topic;
use Sakura\Perms;
use Sakura\Perms\Forum as ForumPerms;

/**
 * Topic controller.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class PostController extends Controller
{
    /**
     * Finds the topic a post is associated with.
     * @param int $id
     * @return string
     */
    public function find($id = 0)
    {
        $post = new Post($id);
        $topic = new Topic($post->topic);
        $forum = new Forum($topic->forum);

        // Check if the forum exists
        if ($post->id === 0
            || $topic->id === 0
            || !$forum->permission(ForumPerms::VIEW, CurrentSession::$user->id)) {
            throw new HttpRouteNotFoundException();
        }

        $topicLink = route('forums.topic', $topic->id);

        // Get all post ids from the database
        $postIds = DB::table('posts')
            ->where('topic_id', $topic->id)
            ->get(['post_id']);
        $postIds = array_column($postIds, 'post_id');

        // Find in array
        $postAt = ceil(array_search($post->id, $postIds) / 10);

        // Only append the page variable if it's more than 1
        if ($postAt > 1) {
            $topicLink .= "?page={$postAt}";
        }

        redirect("{$topicLink}#p{$post->id}");
    }

    /**
     * Gets the raw contents of a post.
     * @param int $id
     * @return string
     */
    public function raw($id = 0)
    {
        $post = new Post($id);
        $topic = new Topic($post->topic);
        $forum = new Forum($topic->forum);

        // Check if the forum exists
        if ($post->id === 0
            || $topic->id === 0
            || !$forum->permission(ForumPerms::VIEW, CurrentSession::$user->id)) {
            return "";
        }

        return $post->text;
    }

    /**
     * Edit a post.
     * @param int $id
     * @return string
     */
    public function edit($id = 0)
    {
        $title = $_POST['title'] ?? null;
        $text = $_POST['text'] ?? null;

        $post = new Post($id);
        $topic = new Topic($post->topic);
        $forum = new Forum($topic->forum);

        // Check permissions
        $noAccess = $post->id === 0
        || $topic->id === 0
        || !$forum->permission(ForumPerms::VIEW, CurrentSession::$user->id);

        $noEdit = (
            $post->poster->id === CurrentSession::$user->id
            ? !CurrentSession::$user->permission(ForumPerms::EDIT_OWN, Perms::FORUM)
            : !$forum->permission(ForumPerms::EDIT_ANY, CurrentSession::$user->id)
        ) || (
            $topic->status === 1
            && !$forum->permission(ForumPerms::LOCK, CurrentSession::$user->id)
        );

        // Check if the forum exists
        if ($noAccess || $noEdit) {
            throw new HttpMethodNotAllowedException();
        }

        $titleLength = strlen($title);
        $textLength = strlen($text);
        $titleMin = config('forum.min_title_length');
        $titleMax = config('forum.max_title_length');
        $textMin = config('forum.min_post_length');
        $textMax = config('forum.max_post_length');

        // Checks
        $titleTooShort = $title !== null
        && $post->id === $topic->firstPost()->id
        && $titleLength < $titleMin;
        $titleTooLong = $title !== null
        && $post->id === $topic->firstPost()->id
        && $titleLength > $titleMax;
        $textTooShort = $textLength < $textMin;
        $textTooLong = $textLength > $textMax;

        // Check requirments
        if ($titleTooShort
            || $titleTooLong
            || $textTooShort
            || $textTooLong) {
            $message = "";

            if ($titleTooShort) {
                $message = "This title is too short!";
            } elseif ($titleTooLong) {
                $message = "This title is too long!";
            } elseif ($textTooShort) {
                $message = "Please make your post a little bit longer!";
            } elseif ($textTooLong) {
                $message = "Your post is too long, you're gonna have to cut a little!";
            }

            $redirect = route('forums.post', $post->id);

            if (!isset($_SESSION['replyText'])) {
                $_SESSION['replyText'] = [];
            }

            $_SESSION['replyText']["t{$forum->id}"] = $text;

            return view('global/information', compact('message', 'redirect'));
        }

        unset($_SESSION['replyText']["t{$forum->id}"]);

        if ($post->id !== $topic->firstPost()->id || $title === null) {
            $title = "Re: {$topic->title}";
        } else {
            $topic->title = $title;
            $topic->update();
        }

        // Create the post
        $post->subject = $title;
        $post->text = $text;
        $post->editTime = time();
        $post->editReason = '';
        $post->editUser = CurrentSession::$user;
        $post = $post->update();

        $postLink = route('forums.post', $post->id);

        redirect($postLink);
    }

    /**
     * Deletes a post.
     * @param int $id
     * @return string
     */
    public function delete($id = 0)
    {
        $post = new Post($id);
        $topic = new Topic($post->topic);
        $forum = new Forum($topic->forum);

        // Check permissions
        $noAccess = $post->id === 0
        || $topic->id === 0
        || !$forum->permission(ForumPerms::VIEW, CurrentSession::$user->id);

        $noDelete = (
            $post->poster->id === CurrentSession::$user->id
            ? !CurrentSession::$user->permission(ForumPerms::DELETE_OWN, Perms::FORUM)
            : !$forum->permission(ForumPerms::DELETE_ANY, CurrentSession::$user->id)
        ) || (
            $topic->status === 1
            && !$forum->permission(ForumPerms::LOCK, CurrentSession::$user->id)
        );

        // Check if the forum exists
        if ($noAccess || $noDelete) {
            throw new HttpMethodNotAllowedException();
        }

        // Check if the topic only has 1 post
        if ($topic->replyCount() === 1) {
            // Delete the entire topic
            $topic->delete();
        } else {
            // Just delete the post (replace this with soft deleting)
            $post->purge();
        }
    }
}
