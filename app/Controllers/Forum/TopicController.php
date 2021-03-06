<?php
/**
 * Holds the controller for topics.
 * @package Sakura
 */

namespace Sakura\Controllers\Forum;

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Sakura\CurrentSession;
use Sakura\Forum\Forum;
use Sakura\Forum\Post;
use Sakura\Forum\Topic;

/**
 * Topic controller.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class TopicController extends Controller
{
    /**
     * Views a topic.
     * @param int $id
     * @throws HttpRouteNotFoundException
     * @return string
     */
    public function view($id = 0)
    {
        $topic = new Topic($id);
        $forum = new Forum($topic->forum);

        // Check if the forum exists
        if ($topic->id === 0
            || !$forum->perms->view) {
            throw new HttpRouteNotFoundException;
        }

        $topic->trackUpdate(CurrentSession::$user->id);
        $topic->viewsUpdate();

        return view('forum/topic', compact('forum', 'topic'));
    }

    /**
     * Checks if a user can moderate the topic.
     * @param int $id
     * @throws HttpRouteNotFoundException
     * @return array
     */
    private function modBase($id)
    {
        $topic = new Topic($id);
        $forum = new Forum($topic->forum);

        if ($topic->id !== 0
            || $forum->perms->view
            || session_check()) {
            return compact('topic', 'forum');
        }

        throw new HttpRouteNotFoundException;
    }

    /**
     * Sticky a topic.
     * @param int $id
     * @throws HttpMethodNotAllowedException
     * @return string
     */
    public function sticky($id)
    {
        extract($this->modBase($id));

        if (!$forum->perms->changeType) {
            throw new HttpMethodNotAllowedException;
        }

        $topic->type = $topic->type !== 1 ? 1 : 0;
        $topic->update();

        redirect(route('forums.topic', $topic->id));
    }

    /**
     * Announce a topic.
     * @param int $id
     * @throws HttpMethodNotAllowedException
     * @return string
     */
    public function announce($id)
    {
        extract($this->modBase($id));

        if (!$forum->perms->changeType) {
            throw new HttpMethodNotAllowedException;
        }

        $topic->type = $topic->type !== 2 ? 2 : 0;
        $topic->update();

        redirect(route('forums.topic', $topic->id));
    }

    /**
     * Lock a topic.
     * @param int $id
     * @throws HttpMethodNotAllowedException
     * @return string
     */
    public function lock($id)
    {
        extract($this->modBase($id));

        if (!$forum->perms->changeStatus) {
            throw new HttpMethodNotAllowedException;
        }

        $topic->status = $topic->status !== 1 ? 1 : 0;
        $topic->update();

        redirect(route('forums.topic', $topic->id));
    }

    /**
     * Delete an entire topic.
     * @param int $id
     * @throws HttpMethodNotAllowedException
     * @return string
     */
    public function delete($id)
    {
        extract($this->modBase($id));

        $trash = intval(config('forum.trash'));

        if ($topic->forum === $trash
            && $forum->perms->deleteAny) {
            $redirect = route('forums.forum', $trash);
            $topic->delete();
        } elseif ($forum->perms->topicMove) {
            $redirect = route('forums.topic', $topic->id);
            $topic->move($trash);
        } else {
            throw new HttpMethodNotAllowedException;
        }

        redirect($redirect);
    }

    /**
     * Restore a topic to its previous location.
     * @param int $id
     * @throws HttpMethodNotAllowedException
     * @return string
     */
    public function restore($id)
    {
        extract($this->modBase($id));

        if (!$forum->perms->topicMove) {
            throw new HttpMethodNotAllowedException;
        }

        if ($topic->oldForum) {
            $topic->move($topic->oldForum, false);
        }

        redirect(route('forums.topic', $topic->id));
    }

    /**
     * Move a topic.
     * @param int $id
     * @throws HttpMethodNotAllowedException
     * @return string
     */
    public function move($id)
    {
        extract($this->modBase($id));
        $dest_forum = new Forum($_REQUEST['forum_id'] ?? 0);

        if (!$forum->perms->topicMove
            || $dest_forum->id === 0
            || $dest_forum->perms->view) {
            throw new HttpMethodNotAllowedException;
        }

        $topic->move($dest_forum->id);

        redirect(route('forums.topic', $topic->id));
    }

    /**
     * Reply to a topic.
     * @param int $id
     * @return string
     */
    public function reply($id = 0)
    {
        $text = $_POST['text'] ?? null;

        // Attempt to get the forum
        $topic = new Topic($id);

        // And attempt to get the forum
        $forum = new Forum($topic->forum);

        // Check if the topic exists
        if ($topic->id === 0
            || $forum->type !== 0
            || !$forum->perms->view) {
            $message = "This post doesn't exist or you don't have access to it!";
            $redirect = route('forums.index');

            return view('global/information', compact('message', 'redirect'));
        }

        // Check if the topic exists
        if (!$forum->perms->reply
            || (
                $topic->status === 1
                && !$forum->perms->changeStatus
            )) {
            $message = "You are not allowed to post in this topic!";
            $redirect = route('forums.topic', $topic->id);

            return view('global/information', compact('message', 'redirect'));
        }

        // Length
        $length = strlen($text);
        $minLen = config('forum.min_post_length');
        $maxLen = config('forum.max_post_length');
        $tooShort = $length < $minLen;
        $tooLong = $length > $maxLen;

        // Check requirments
        if ($tooShort
            || $tooLong) {
            $route = route('forums.topic', $topic->id);

            $message = "Your post is " . (
                $tooShort
                ? "too short, add some more text! Make it at least {$minLen}."
                : "too long, you're gonna have to cut a little! Keep it under {$maxLen}."
            );
            $redirect = "{$route}#reply";

            if (!isset($_SESSION['replyText'])) {
                $_SESSION['replyText'] = [];
            }

            $_SESSION['replyText']["t{$topic->id}"] = $text;

            return view('global/information', compact('message', 'redirect'));
        }

        unset($_SESSION['replyText']["t{$topic->id}"]);

        // Create the post
        $post = Post::create(
            "Re: {$topic->title}",
            $text,
            CurrentSession::$user,
            $topic->id,
            $forum->id
        );

        // Go to the post
        $postLink = route('forums.post', $post->id);

        // Head to the post
        redirect($postLink);
    }

    /**
     * Create a topic.
     * @param int $id
     * @return string
     */
    public function create($id = 0)
    {
        $title = $_POST['title'] ?? null;
        $text = $_POST['text'] ?? null;

        // And attempt to get the forum
        $forum = new Forum($id);

        // Check if the forum exists
        if ($forum->id === 0
            || $forum->type !== 0
            || !$forum->perms->view
            || !$forum->perms->reply
            || !$forum->perms->topicCreate) {
            $message = "This forum doesn't exist or you don't have access to it!";
            $redirect = route('forums.index');

            return view('global/information', compact('message', 'redirect'));
        }

        if ($text && $title) {
            // Length
            $titleLength = strlen($title);
            $textLength = strlen($text);
            $titleMin = config('forum.min_title_length');
            $titleMax = config('forum.max_title_length');
            $textMin = config('forum.min_post_length');
            $textMax = config('forum.max_post_length');

            // Checks
            $titleTooShort = $titleLength < $titleMin;
            $titleTooLong = $titleLength > $titleMax;
            $textTooShort = $textLength < $textMin;
            $textTooLong = $textLength > $textMax;

            // Check requirments
            if ($titleTooShort
                || $titleTooLong
                || $textTooShort
                || $textTooLong) {
                $message = "";

                if ($titleTooShort) {
                    $message = "This title is too short, it has to be longer than {$titleMin} characters!";
                } elseif ($titleTooLong) {
                    $message = "This title is too long, keep it under {$titleMax} characters!";
                } elseif ($textTooShort) {
                    $message = "Please make your post a little bit longer, at least {$textMin} characters!";
                } elseif ($textTooLong) {
                    $message = "Your post is too long, you're gonna have to cut a little!"
                        . " Can't be more than {$textMax} characters.";
                }

                $redirect = route('forums.new', $forum->id);

                if (!isset($_SESSION['replyText'])) {
                    $_SESSION['replyText'] = [];
                }

                $_SESSION['replyText']["f{$forum->id}"]["title"] = $title;
                $_SESSION['replyText']["f{$forum->id}"]["text"] = $text;

                return view('global/information', compact('message', 'redirect'));
            }

            unset($_SESSION['replyText']["f{$forum->id}"]);

            // Create the post
            $post = Post::create(
                $title,
                $text,
                CurrentSession::$user,
                0,
                $forum->id
            );

            // Go to the post
            $postLink = route('forums.post', $post->id);

            // Head to the post
            redirect($postLink);
            return;
        }

        return view('forum/topic', compact('forum'));
    }
}
