<?php
/**
 * Holds the forum pages controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\DB;
use Sakura\Forum\Forum;
use Sakura\Forum\Post;
use Sakura\Forum\Thread;
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
     * @return mixed HTML for the forum index.
     */
    public function index()
    {
        global $currentUser;

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
            if (!$forum->permission(ForumPerms::VIEW, $currentUser->id)) {
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
            if (!$forum->permission(ForumPerms::VIEW, $currentUser->id)) {
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
        $activePoster = User::construct($activePosterId[0]->poster_id);

        // Create the forum object
        $forum = new Forum;

        Template::vars(compact('forum', 'activeThreads', 'latestPosts', 'activePoster'));

        // Return the compiled page
        return Template::render('forum/index');
    }

    public function forum($id = 0)
    {
        global $currentUser;

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
        if (!$forum->permission(ForumPerms::VIEW, $currentUser->id)) {
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
        return Template::render('forum/viewforum');
    }

    public function markForumRead($id = 0)
    {
        global $currentUser;

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
        if (!$forum->permission(ForumPerms::VIEW, $currentUser->id)) {
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
        $forum->trackUpdateAll($currentUser->id);

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

    public function thread($id = 0)
    {
        global $currentUser;

        // Attempt to get the thread
        $thread = new Thread($id);

        // And attempt to get the forum
        $forum = new Forum($thread->forum);

        // Check if the forum exists
        if ($thread->id == 0 || !$forum->permission(ForumPerms::VIEW, $currentUser->id)) {
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
        $thread->trackUpdate($currentUser->id);

        // Update views
        $thread->viewsUpdate();

        // Set parse variables
        Template::vars([
            'thread' => $thread,
            'forum' => $forum,
        ]);

        // Print page contents
        return Template::render('forum/viewtopic');
    }

    public function threadModerate($id = 0)
    {
        global $currentUser;

        // Attempt to get the thread
        $thread = new Thread($id);

        // And attempt to get the forum
        $forum = new Forum($thread->forum);

        // Default stuff
        $message = 'Unknown moderation action.';
        $redirect = Router::route('forums.thread', $thread->id);

        // Check if the forum exists
        if ($thread->id == 0
            || !$forum->permission(ForumPerms::VIEW, $currentUser->id)
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
                    if (!$forum->permission(ForumPerms::STICKY, $currentUser->id)) {
                        $message = "You're not allowed to do this!";
                        break;
                    }

                    // Update the type
                    $thread->type = $thread->type !== 1 ? 1 : 0;

                    $thread->update();

                    // Add page variable stuff
                    $message = $thread->type ? 'Changed the thread to sticky!' : 'Reverted the thread back to normal!';
                    break;

                case 'announce':
                    // Check permission
                    if (!$forum->permission(ForumPerms::ANNOUNCEMENT, $currentUser->id)) {
                        $message = "You're not allowed to do this!";
                        break;
                    }

                    // Update the type
                    $thread->type = $thread->type !== 2 ? 2 : 0;

                    $thread->update();

                    // Add page variable stuff
                    $message = $thread->type ? 'Changed the thread to into an announcement!' : 'Reverted the thread back to normal!';
                    break;

                case 'lock':
                    // Check permission
                    if (!$forum->permission(ForumPerms::LOCK, $currentUser->id)) {
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
                        if (!$forum->permission(ForumPerms::DELETE_ANY, $currentUser->id)) {
                            $message = "You're not allowed to do this!";
                            break;
                        }

                        // Set pruned to true
                        $pruned = true;

                        // Delete the thread
                        $thread->delete();

                        // Set message
                        $message = "Deleted the thread!";
                        $redirect = Router::route('forums.forum', $trash);
                    } else {
                        // Check permission
                        if (!$forum->permission(ForumPerms::MOVE, $currentUser->id)) {
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
        Template::vars([
            'page' => compact('message', 'redirect'),
        ]);

        // Print page contents
        return Template::render('global/information');
    }

    public function post($id = 0)
    {
        global $currentUser;

        // Attempt to get the post
        $post = new Post($id);

        // And attempt to get the forum
        $thread = new Thread($post->thread);

        // And attempt to get the forum
        $forum = new Forum($thread->forum);

        // Check if the forum exists
        if ($post->id == 0 || $thread->id == 0 || !$forum->permission(ForumPerms::VIEW, $currentUser->id)) {
            // Set render data
            Template::vars([
                'page' => [
                    'message' => 'This post doesn\'t exist or you don\'t have access to it!',
                    'redirect' => Router::route('forums.index'),
                ],
            ]);

            // Print page contents
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
}
