<?php
/**
 * Holds the controller for topics.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Forum;

use Sakura\ActiveUser;
use Sakura\Forum\Forum;
use Sakura\Forum\Post;
use Sakura\Forum\Topic;
use Sakura\Perms\Forum as ForumPerms;

/**
 * Topic controller.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class TopicController extends Controller
{
    public function view($id = 0)
    {
        $topic = new Topic($id);
        $forum = new Forum($topic->forum);

        // Check if the forum exists
        if ($topic->id === 0
            || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
            $message = "This topic doesn't exist or you don't have access to it!";
            $redirect = route('forums.index');

            return view('global/information', compact('message', 'redirect'));
        }

        $topic->trackUpdate(ActiveUser::$user->id);
        $topic->viewsUpdate();

        return view('forum/topic', compact('forum', 'topic'));
    }

    private function modBase($id)
    {
        $topic = new Topic($id);
        $forum = new Forum($topic->forum);

        if ($topic->id !== 0
            || $forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)
            || session_check()) {
            return compact('topic', 'forum');
        }

        return false;
    }

    public function sticky($id)
    {
        $modBase = $this->modBase($id);
        $redirect = route('forums.index');
        $message = "This forum doesn't exist or you don't have access to it.";

        if ($modBase !== false) {
            extract($modBase);
            $redirect = route('forums.topic', $topic->id);

            if ($forum->permission(ForumPerms::STICKY, ActiveUser::$user->id)) {
                $topic->type = $topic->type !== 1 ? 1 : 0;
                $topic->update();
                $message = $topic->type
                ? 'Changed the topic to sticky!' : 'Reverted the topic back to normal!';
            } else {
                $message = "You aren't allowed to sticky topics!";
            }
        }

        return view('global/information', compact('message', 'redirect'));
    }

    public function announce($id)
    {
        $modBase = $this->modBase($id);
        $redirect = route('forums.index');
        $message = "This forum doesn't exist or you don't have access to it.";

        if ($modBase !== false) {
            extract($modBase);
            $redirect = route('forums.topic', $topic->id);

            if ($forum->permission(ForumPerms::ANNOUNCEMENT, ActiveUser::$user->id)) {
                $topic->type = $topic->type !== 2 ? 2 : 0;
                $topic->update();
                $message = $topic->type
                ? 'Changed the topic to an announcement!' : 'Reverted the topic back to normal!';
            } else {
                $message = "You aren't allowed to announce topics!";
            }
        }

        return view('global/information', compact('message', 'redirect'));
    }

    public function lock($id)
    {
        $modBase = $this->modBase($id);
        $redirect = route('forums.index');
        $message = "This forum doesn't exist or you don't have access to it.";

        if ($modBase !== false) {
            extract($modBase);
            $redirect = route('forums.topic', $topic->id);

            if ($forum->permission(ForumPerms::LOCK, ActiveUser::$user->id)) {
                $topic->status = $topic->status !== 1 ? 1 : 0;
                $topic->update();
                $message = ($topic->status ? 'Locked' : 'Unlocked') . ' the topic!';
            } else {
                $message = "You aren't allowed to lock topics!";
            }
        }

        return view('global/information', compact('message', 'redirect'));
    }

    public function delete($id)
    {
        $modBase = $this->modBase($id);
        $redirect = route('forums.index');
        $message = "This forum doesn't exist or you don't have access to it.";

        if ($modBase !== false) {
            extract($modBase);
            $trash = config('forum.trash');

            // Check if we're operating from the trash
            if ($topic->forum === $trash) {
                if ($forum->permission(ForumPerms::DELETE_ANY, ActiveUser::$user->id)) {
                    $topic->delete();
                    $message = "Deleted the topic!";
                    $redirect = route('forums.forum', $trash);
                } else {
                    $message = "You aren't allowed to delete topics!";
                }
            } else {
                $redirect = route('forums.topic', $topic->id);

                if ($forum->permission(ForumPerms::MOVE, ActiveUser::$user->id)) {
                    $topic->move($trash);
                    $message = "Moved the topic to the trash!";
                } else {
                    $message = "You're not allowed to do this!";
                }
            }
        }

        return view('global/information', compact('message', 'redirect'));
    }

    public function restore($id)
    {
        $modBase = $this->modBase($id);
        $redirect = route('forums.index');
        $message = "This forum doesn't exist or you don't have access to it.";

        if ($modBase !== false) {
            extract($modBase);
            $redirect = route('forums.topic', $topic->id);

            if ($forum->permission(ForumPerms::MOVE, ActiveUser::$user->id)) {
                if ($topic->oldForum) {
                    $topic->move($topic->oldForum, false);

                    $message = "Moved the topic back to it's old location!";
                } else {
                    $message = "This topic has never been moved!";
                }
            } else {
                $message = "You aren't allowed to move threads!";
            }
        }

        return view('global/information', compact('message', 'redirect'));
    }

    public function move($id)
    {
        $modBase = $this->modBase($id);
        $redirect = route('forums.index');
        $message = "This forum doesn't exist or you don't have access to it.";

        if ($modBase !== false) {
            extract($modBase);
            $redirect = route('forums.topic', $topic->id);

            if ($forum->permission(ForumPerms::MOVE, ActiveUser::$user->id)) {
                $dest_forum = new Forum($_REQUEST['forum_id'] ?? 0);

                if ($dest_forum->id === 0
                    || $dest_forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
                    $topic->move($dest_forum->id);

                    $message = "Moved to the topic to {$dest_forum->name}!";
                } else {
                    $message = "The destination forum doesn't exist or you don't have access to it.";
                }
            } else {
                $message = "You aren't allowed to move threads!";
            }
        }

        return view('global/information', compact('message', 'redirect'));
    }

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
            || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
            $message = "This post doesn't exist or you don't have access to it!";
            $redirect = route('forums.index');

            return view('global/information', compact('message', 'redirect'));
        }

        // Check if the topic exists
        if (!$forum->permission(ForumPerms::REPLY, ActiveUser::$user->id)
            || (
                $topic->status === 1
                && !$forum->permission(ForumPerms::LOCK, ActiveUser::$user->id)
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
            ActiveUser::$user,
            $topic->id,
            $forum->id
        );

        // Go to the post
        $postLink = route('forums.post', $post->id);

        // Head to the post
        return header("Location: {$postLink}");
    }

    public function create($id = 0)
    {
        $title = $_POST['title'] ?? null;
        $text = $_POST['text'] ?? null;

        // And attempt to get the forum
        $forum = new Forum($id);

        // Check if the forum exists
        if ($forum->id === 0
            || $forum->type !== 0
            || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)
            || !$forum->permission(ForumPerms::REPLY, ActiveUser::$user->id)
            || !$forum->permission(ForumPerms::CREATE_THREADS, ActiveUser::$user->id)) {
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
                ActiveUser::$user,
                0,
                $forum->id
            );

            // Go to the post
            $postLink = route('forums.post', $post->id);

            // Head to the post
            return header("Location: {$postLink}");
        }

        return view('forum/topic', compact('forum'));
    }
}
