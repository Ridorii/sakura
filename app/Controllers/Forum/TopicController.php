<?php
/**
 * Holds the controller for topic.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Forum;

use Sakura\ActiveUser;
use Sakura\Forum\Forum;
use Sakura\Forum\Topic;
use Sakura\Perms\Forum as ForumPerms;
use Sakura\Template;

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
        // Attempt to get the topic
        $topic = new Topic($id);

        // And attempt to get the forum
        $forum = new Forum($topic->forum);

        // Check if the forum exists
        if ($topic->id === 0 || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
            // Set render data
            Template::vars([
                'message' => "This topic doesn't exist or you don't have access to it!",
                'redirect' => route('forums.index'),
            ]);

            // Print page contents
            return Template::render('global/information');
        }

        // Update the tracking status
        $topic->trackUpdate(ActiveUser::$user->id);

        // Update views
        $topic->viewsUpdate();

        // Set parse variables
        Template::vars(compact('forum', 'topic'));

        // Print page contents
        return Template::render('forum/topic');
    }

    private function modBase($id)
    {
        $topic = new Topic($id);
        $forum = new Forum($topic->forum);

        if ($topic->id !== 0 || $forum->permission(ForumPerms::VIEW, ActiveUser::$user->id) || session_check()) {
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

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Check if the topic exists
        if (!$forum->permission(ForumPerms::REPLY, ActiveUser::$user->id)
            || (
                $topic->status === 1
                && !$forum->permission(ForumPerms::LOCK, ActiveUser::$user->id)
            )) {
            $message = "You are not allowed to post in this topic!";
            $redirect = route('forums.topic', $topic->id);

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
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
            $route = Router::route('forums.topic', $topic->id);

            $message = "Your post is " . (
                $tooShort
                ? "too short, add some more text! Make it at least {$minLen}."
                : "too long, you're gonna have to cut a little! Keep it under {$maxLen}."
            );
            $redirect = "{$route}#reply";

            Template::vars(compact('message', 'redirect'));

            if (!isset($_SESSION['replyText'])) {
                $_SESSION['replyText'] = [];
            }

            $_SESSION['replyText']["t{$topic->id}"] = $text;

            return Template::render('global/information');
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
}
