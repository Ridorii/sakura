<?php
/**
 * Holds the forum pages controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\ActiveUser;
use Sakura\Config;
use Sakura\DB;
use Sakura\Forum\Forum;
use Sakura\Forum\Post;
use Sakura\Forum\Thread;
use Sakura\Perms;
use Sakura\Perms\Forum as ForumPerms;
use Sakura\Router;
use Sakura\Template;
use Sakura\User;

/**
 * Forum page controllers.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class ForumController extends Controller
{
    /**
     * Serves the forum index.
     *
     * @return string HTML for the forum index.
     */
    public function index()
    {
        // Get the most active threads
        $activeThreadsIds = DB::table('posts')
            ->groupBy('topic_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->get(['topic_id']);
        $activeThreads = [];

        while (list($_n, $_t) = each($activeThreadsIds)) {
            // Create the thread object
            $thread = new Thread($_t->topic_id);

            // Create a forum object
            $forum = new Forum($thread->forum);

            // Check if we have permission to view it
            if (!$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
                $fetch = DB::table('posts')
                    ->groupBy('topic_id')
                    ->orderByRaw('COUNT(*) DESC')
                    ->skip(11 + $_n)
                    ->take(1)
                    ->get(['topic_id']);

                if ($fetch) {
                    $activeThreadsIds[] = $fetch[0];
                }
                continue;
            }

            $activeThreads[$thread->id] = $thread;
        }

        // Get the latest posts
        $latestPostsIds = DB::table('posts')
            ->orderBy('post_id', 'desc')
            ->limit(10)
            ->get(['post_id']);
        $latestPosts = [];

        while (list($_n, $_p) = each($latestPostsIds)) {
            // Create new post object
            $post = new Post($_p->post_id);

            // Forum id
            $forum = new Forum($post->forum);

            // Check if we have permission to view it
            if (!$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
                $fetch = DB::table('posts')
                    ->orderBy('post_id', 'desc')
                    ->skip(11 + $_n)
                    ->take(1)
                    ->get(['post_id']);

                if ($fetch) {
                    $latestPostsIds[] = $fetch[0];
                }
                continue;
            }

            $latestPosts[$post->id] = $post;
        }

        // Get the most active poster
        $activePosterId = DB::table('posts')
            ->where('post_time', '>', time() - (24 * 60 * 60))
            ->groupBy('poster_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(1)
            ->get(['poster_id']);
        $activePoster = User::construct(
            $activePosterId ? $activePosterId[0]->poster_id : 0
        );

        // Create the forum object
        $forum = new Forum;

        Template::vars(compact('forum', 'activeThreads', 'latestPosts', 'activePoster'));

        // Return the compiled page
        return Template::render('forum/index');
    }

    /**
     * Get a forum page.
     *
     * @return string
     */
    public function forum($id = 0)
    {
        // Get the forum
        $forum = new Forum($id);

        // Redirect forum id 0 to the main page
        if ($forum->id === 0) {
            return header('Location: ' . Router::route('forums.index'));
        }

        // Check if the forum exists
        if ($forum->id < 0) {
            // Set render data
            Template::vars([
                'page' => [
                    'message' => 'The forum you tried to access does not exist.',
                    'redirect' => Router::route('forums.index'),
                ],
            ]);

            // Print page contents
            return Template::render('global/information');
        }

        // Check if the user has access to the forum
        if (!$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
            // Set render data
            Template::vars([
                'page' => [
                    'message' => 'You do not have access to this forum.',
                    'redirect' => Router::route('forums.index'),
                ],
            ]);

            // Print page contents
            return Template::render('global/information');
        }

        // Check if the forum isn't a link
        if ($forum->type === 2) {
            // Set render data
            Template::vars([
                'page' => [
                    'message' => 'The forum you tried to access is a link. You\'re being redirected.',
                    'redirect' => $forum->link,
                ],
            ]);

            // Print page contents
            return Template::render('global/information');
        }

        // Set parse variables
        Template::vars([
            'forum' => $forum,
        ]);

        // Print page contents
        return Template::render('forum/forum');
    }

    /**
     * Mark a forum as read.
     *
     * @return string
     */
    public function markForumRead($id = 0)
    {
        // Check if the session id was supplied
        if (!isset($_GET['s']) || $_GET['s'] != session_id()) {
            // Set render data
            Template::vars([
                'page' => [
                    'message' => 'Your session expired! Go back and try again.',
                    'redirect' => Router::route('forums.index'),
                ],
            ]);

            // Print page contents
            return Template::render('global/information');
        }

        // Get the forum
        $forum = new Forum($id);

        // Check if the forum exists
        if ($forum->id < 1) {
            // Set render data
            Template::vars([
                'page' => [
                    'message' => 'The forum you tried to access does not exist.',
                    'redirect' => Router::route('forums.index'),
                ],
            ]);

            // Print page contents
            return Template::render('global/information');
        }

        // Check if the user has access to the forum
        if (!$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
            // Set render data
            Template::vars([
                'page' => [
                    'message' => 'You do not have access to this forum.',
                    'redirect' => Router::route('forums.index'),
                ],
            ]);

            // Print page contents
            return Template::render('global/information');
        }

        // Run the function
        $forum->trackUpdateAll(ActiveUser::$user->id);

        // Set render data
        Template::vars([
            'page' => [
                'message' => 'All threads have been marked as read.',
                'redirect' => Router::route('forums.forum', $forum->id),
            ],
        ]);

        // Print page contents
        return Template::render('global/information');
    }

    /**
     * View a thread.
     *
     * @return string
     */
    public function thread($id = 0)
    {
        // Attempt to get the thread
        $thread = new Thread($id);

        // And attempt to get the forum
        $forum = new Forum($thread->forum);

        // Check if the forum exists
        if ($thread->id == 0 || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
            // Set render data
            Template::vars([
                'page' => [
                    'message' => 'This thread doesn\'t exist or you don\'t have access to it!',
                    'redirect' => Router::route('forums.index'),
                ],
            ]);

            // Print page contents
            return Template::render('global/information');
        }

        // Update the tracking status
        $thread->trackUpdate(ActiveUser::$user->id);

        // Update views
        $thread->viewsUpdate();

        // Set parse variables
        Template::vars(compact('forum', 'thread'));

        // Print page contents
        return Template::render('forum/thread');
    }

    /**
     * Moderate a thread.
     *
     * @return string
     */
    public function threadModerate($id = 0)
    {
        // Attempt to get the thread
        $thread = new Thread($id);

        // And attempt to get the forum
        $forum = new Forum($thread->forum);

        // Default stuff
        $message = 'Unknown moderation action.';
        $redirect = Router::route('forums.thread', $thread->id);

        // Check if the forum exists
        if ($thread->id == 0
            || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)
            || !isset($_POST['session'])
            || $_POST['session'] != session_id()) {
            $message = 'This thread doesn\'t exist or you don\'t have access to it!';
            $redirect = Router::route('forums.index');
        } else {
            // Take the action
            $action = isset($_POST['action']) ? $_POST['action'] : null;

            // Switch
            switch ($action) {
                case 'sticky':
                    // Check permission
                    if (!$forum->permission(ForumPerms::STICKY, ActiveUser::$user->id)) {
                        $message = "You're not allowed to do this!";
                        break;
                    }

                    // Update the type
                    $thread->type = $thread->type !== 1 ? 1 : 0;

                    $thread->update();

                    // Add page variable stuff
                    $message = $thread->type
                    ? 'Changed the thread to sticky!'
                    : 'Reverted the thread back to normal!';
                    break;

                case 'announce':
                    // Check permission
                    if (!$forum->permission(ForumPerms::ANNOUNCEMENT, ActiveUser::$user->id)) {
                        $message = "You're not allowed to do this!";
                        break;
                    }

                    // Update the type
                    $thread->type = $thread->type !== 2 ? 2 : 0;

                    $thread->update();

                    // Add page variable stuff
                    $message = $thread->type
                    ? 'Changed the thread to into an announcement!'
                    : 'Reverted the thread back to normal!';
                    break;

                case 'lock':
                    // Check permission
                    if (!$forum->permission(ForumPerms::LOCK, ActiveUser::$user->id)) {
                        $message = "You're not allowed to do this!";
                        break;
                    }

                    // Update the status
                    $thread->status = $thread->status !== 1 ? 1 : 0;

                    $thread->update();

                    // Add page variable stuff
                    $message = ($thread->status ? 'Locked' : 'Unlocked') . ' the thread!';
                    break;

                case 'delete':
                    // Get the id of the trash forum
                    $trash = Config::get('forum_trash_id');

                    // Check if we're operating from the trash
                    if ($thread->forum == $trash) {
                        // Check permission
                        if (!$forum->permission(ForumPerms::DELETE_ANY, ActiveUser::$user->id)) {
                            $message = "You're not allowed to do this!";
                            break;
                        }

                        // Delete the thread
                        $thread->delete();

                        // Set message
                        $message = "Deleted the thread!";
                        $redirect = Router::route('forums.forum', $trash);
                    } else {
                        // Check permission
                        if (!$forum->permission(ForumPerms::MOVE, ActiveUser::$user->id)) {
                            $message = "You're not allowed to do this!";
                            break;
                        }

                        // Move the thread
                        $thread->move($trash);

                        // Trashed!
                        $message = "Moved the thread to the trash!";
                    }
                    break;

                case 'restore':
                    // Check if this thread has record of being in a previous forum
                    if ($thread->oldForum) {
                        // Move the thread back
                        $thread->move($thread->oldForum, false);

                        $message = "Moved the thread back to it's old location!";
                    } else {
                        $message = "This thread has never been moved!";
                    }
                    break;
            }
        }

        // Set the variables
        Template::vars(compact('message', 'redirect'));

        // Print page contents
        return Template::render('global/information');
    }

    /**
     * Redirect to the position of a post in a thread.
     *
     * @return mixed
     */
    public function post($id = 0)
    {
        // Attempt to get the post
        $post = new Post($id);

        // And attempt to get the forum
        $thread = new Thread($post->thread);

        // And attempt to get the forum
        $forum = new Forum($thread->forum);

        // Check if the forum exists
        if ($post->id == 0
            || $thread->id == 0
            || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
            $message = "This post doesn't exist or you don't have access to it!";
            $redirect = Router::route('forums.index');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Generate link
        $threadLink = Router::route('forums.thread', $thread->id);

        // Get all post ids from the database
        $postIds = DB::table('posts')
            ->where('topic_id', $thread->id)
            ->get(['post_id']);
        $postIds = array_column($postIds, 'post_id');

        // Find in array
        $postAt = ceil(array_search($post->id, $postIds) / 10);

        // Only append the page variable if it's more than 1
        if ($postAt > 1) {
            $threadLink .= "?page={$postAt}";
        }

        return header("Location: {$threadLink}#p{$post->id}");
    }

    /**
     * Get the raw text of a post.
     *
     * @return string
     */
    public function postRaw($id = 0)
    {
        // Attempt to get the post
        $post = new Post($id);

        // And attempt to get the forum
        $thread = new Thread($post->thread);

        // And attempt to get the forum
        $forum = new Forum($thread->forum);

        // Check if the forum exists
        if ($post->id == 0
            || $thread->id == 0
            || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
            return "";
        }

        return $post->text;
    }

    /**
     * Reply to a thread.
     *
     * @return string
     */
    public function threadReply($id = 0)
    {
        $text = isset($_POST['text']) ? $_POST['text'] : null;

        // Attempt to get the forum
        $thread = new Thread($id);

        // And attempt to get the forum
        $forum = new Forum($thread->forum);

        // Check if the thread exists
        if ($thread->id == 0
            || $forum->type !== 0
            || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
            $message = "This post doesn't exist or you don't have access to it!";
            $redirect = Router::route('forums.index');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Check if the thread exists
        if (!$forum->permission(ForumPerms::REPLY, ActiveUser::$user->id)
            || (
                $thread->status === 1
                && !$forum->permission(ForumPerms::LOCK, ActiveUser::$user->id)
            )) {
            $message = "You are not allowed to post in this thread!";
            $redirect = Router::route('forums.thread', $thread->id);

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Length
        $length = strlen($text);
        $minLen = Config::get('forum_text_min');
        $maxLen = Config::get('forum_text_max');
        $tooShort = $length < $minLen;
        $tooLong = $length > $maxLen;

        // Check requirments
        if ($tooShort
            || $tooLong) {
            $route = Router::route('forums.thread', $thread->id);

            $message = "Your post is " . (
                $tooShort
                ? "too short, add some more text!"
                : "too long, you're gonna have to cut a little!"
            );
            $redirect = "{$route}#reply";

            Template::vars(compact('message', 'redirect'));

            if (!isset($_SESSION['replyText'])) {
                $_SESSION['replyText'] = [];
            }

            $_SESSION['replyText']["t{$thread->id}"] = $text;

            return Template::render('global/information');
        }

        unset($_SESSION['replyText']["t{$thread->id}"]);

        // Create the post
        $post = Post::create(
            "Re: {$thread->title}",
            $text,
            ActiveUser::$user,
            $thread->id,
            $forum->id
        );

        // Go to the post
        $postLink = Router::route('forums.post', $post->id);

        // Head to the post
        return header("Location: {$postLink}");
    }

    /**
     * Create a thread.
     *
     * @return string
     */
    public function createThread($id = 0)
    {
        $title = isset($_POST['title']) ? $_POST['title'] : null;
        $text = isset($_POST['text']) ? $_POST['text'] : null;

        // And attempt to get the forum
        $forum = new Forum($id);

        // Check if the forum exists
        if ($forum->id === 0
            || $forum->type !== 0
            || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)
            || !$forum->permission(ForumPerms::REPLY, ActiveUser::$user->id)
            || !$forum->permission(ForumPerms::CREATE_THREADS, ActiveUser::$user->id)) {
            $message = "This forum doesn't exist or you don't have access to it!";
            $redirect = Router::route('forums.index');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        if ($text && $title) {
            // Length
            $titleLength = strlen($title);
            $textLength = strlen($text);
            $titleMin = Config::get('forum_title_min');
            $titleMax = Config::get('forum_title_max');
            $textMin = Config::get('forum_text_min');
            $textMax = Config::get('forum_text_max');

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
                    $message = "This title is too short!";
                } elseif ($titleTooLong) {
                    $message = "This title is too long!";
                } elseif ($textTooShort) {
                    $message = "Please make your post a little bit longer!";
                } elseif ($textTooLong) {
                    $message = "Your post is too long, you're gonna have to cut a little!";
                }

                $redirect = Router::route('forums.new', $forum->id);

                Template::vars(compact('message', 'redirect'));

                if (!isset($_SESSION['replyText'])) {
                    $_SESSION['replyText'] = [];
                }

                $_SESSION['replyText']["f{$forum->id}"]["title"] = $title;
                $_SESSION['replyText']["f{$forum->id}"]["text"] = $text;

                return Template::render('global/information');
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
            $postLink = Router::route('forums.post', $post->id);

            // Head to the post
            return header("Location: {$postLink}");
        }

        Template::vars(compact('forum'));

        return Template::render('forum/thread');
    }

    /**
     * Edit a post.
     *
     * @return string
     */
    public function editPost($id = 0)
    {
        $title = isset($_POST['title']) ? $_POST['title'] : null;
        $text = isset($_POST['text']) ? $_POST['text'] : null;

        // Attempt to get the post
        $post = new Post($id);

        // Attempt to get the thread
        $thread = new Thread($post->thread);

        // And attempt to get the forum
        $forum = new Forum($thread->forum);

        // Check permissions
        $noAccess = $post->id == 0
        || $thread->id == 0
        || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id);

        $noEdit = (
            $post->poster->id === ActiveUser::$user->id
            ? !ActiveUser::$user->permission(ForumPerms::EDIT_OWN, Perms::FORUM)
            : !$forum->permission(ForumPerms::EDIT_ANY, ActiveUser::$user->id)
        ) || (
            $thread->status === 1
            && !$forum->permission(ForumPerms::LOCK, ActiveUser::$user->id)
        );

        // Check if the forum exists
        if ($noAccess || $noEdit) {
            if ($noDelete) {
                $message = "You aren't allowed to edit posts in this thread!";
                $redirect = Router::route('forums.post', $post->id);
            } else {
                $message = "This post doesn't exist or you don't have access to it!";
                $redirect = Router::route('forums.index');
            }

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Length
        $titleLength = strlen($title);
        $textLength = strlen($text);
        $titleMin = Config::get('forum_title_min');
        $titleMax = Config::get('forum_title_max');
        $textMin = Config::get('forum_text_min');
        $textMax = Config::get('forum_text_max');

        // Checks
        $titleTooShort = $title !== null
        && $post->id === $thread->firstPost()->id
        && $titleLength < $titleMin;
        $titleTooLong = $title !== null
        && $post->id === $thread->firstPost()->id
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

            $redirect = Router::route('forums.post', $post->id);

            Template::vars(compact('message', 'redirect'));

            if (!isset($_SESSION['replyText'])) {
                $_SESSION['replyText'] = [];
            }

            $_SESSION['replyText']["t{$forum->id}"] = $text;

            return Template::render('global/information');
        }

        unset($_SESSION['replyText']["t{$forum->id}"]);

        if ($post->id !== $thread->firstPost()->id || $title === null) {
            $title = "Re: {$thread->title}";
        } else {
            $thread->title = $title;
            $thread->update();
        }

        // Create the post
        $post->subject = $title;
        $post->text = $text;
        $post->editTime = time();
        $post->editReason = '';
        $post->editUser = ActiveUser::$user;
        $post = $post->update();

        // Go to the post
        $postLink = Router::route('forums.post', $post->id);

        // Head to the post
        return header("Location: {$postLink}");
    }

    /**
     * Delete a post.
     *
     * @return string
     */
    public function deletePost($id = 0)
    {
        $action = isset($_POST['yes']) && isset($_POST['sessionid'])
        ? $_POST['sessionid'] === session_id()
        : null;

        // Attempt to get the post
        $post = new Post($id);

        // And attempt to get the forum
        $thread = new Thread($post->thread);

        // And attempt to get the forum
        $forum = new Forum($thread->forum);

        // Check permissions
        $noAccess = $post->id == 0
        || $thread->id == 0
        || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id);

        $noDelete = (
            $post->poster->id === ActiveUser::$user->id
            ? !ActiveUser::$user->permission(ForumPerms::DELETE_OWN, Perms::FORUM)
            : !$forum->permission(ForumPerms::DELETE_ANY, ActiveUser::$user->id)
        ) || (
            $thread->status === 1
            && !$forum->permission(ForumPerms::LOCK, ActiveUser::$user->id)
        );

        // Check if the forum exists
        if ($noAccess || $noDelete) {
            if ($noDelete) {
                $message = "You aren't allowed to delete posts in this thread!";
                $redirect = Router::route('forums.post', $post->id);
            } else {
                $message = "This post doesn't exist or you don't have access to it!";
                $redirect = Router::route('forums.index');
            }

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        if ($action !== null) {
            if ($action) {
                // Set message
                $message = "Deleted the post!";

                // Check if the thread only has 1 post
                if ($thread->replyCount() === 1) {
                    // Delete the entire thread
                    $thread->delete();

                    $redirect = Router::route('forums.forum', $forum->id);
                } else {
                    // Just delete the post
                    $post->delete();

                    $redirect = Router::route('forums.thread', $thread->id);
                }

                Template::vars(compact('message', 'redirect'));

                return Template::render('global/information');
            }

            $postLink = Router::route('forums.post', $post->id);
            return header("Location: {$postLink}");
        }

        $message = "Are you sure?";

        Template::vars(compact('message'));

        return Template::render('global/confirm');
    }
}
