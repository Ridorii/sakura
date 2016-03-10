<?php
/**
 * Holds the forum pages controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\DB;
use Sakura\Forum\Forum;
use Sakura\Forum\Thread;
use Sakura\Perms\Forum as ForumPerms;
use Sakura\Router;
use Sakura\Template;
use Sakura\User;
use Sakura\Users;

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
        // Merge index specific stuff with the global render data
        Template::vars([
            'forum' => (new Forum()),
            'stats' => [
                'userCount' => DB::table('users')->where('password_algo', '!=', 'disabled')->whereNotIn('rank_main', [1, 10])->count(),
                'newestUser' => User::construct(Users::getNewestUserId()),
                'lastRegDate' => date_diff(
                    date_create(date('Y-m-d', User::construct(Users::getNewestUserId())->registered)),
                    date_create(date('Y-m-d'))
                )->format('%a'),
                'topicCount' => DB::table('topics')->count(),
                'postCount' => DB::table('posts')->count(),
                'onlineUsers' => Users::checkAllOnline(),
            ],
        ]);

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
            header('Location: ' . Router::route('forums.index'));
            exit;
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

        // Check if the forum exists
        if ($thread->id == 0 || !$forum->permission(ForumPerms::VIEW, $currentUser->id)) {
            // Set render data
            Template::vars([
                'page' => [
                    'message' => 'This thread doesn\'t exist or you don\'t have access to it!',
                    'redirect' => Router::route('forums.index'),
                ],
            ]);
        } else {
            // Take the action
            $action = isset($_POST['action']) ? $_POST['action'] : null;

            // Switch
            switch ($action) {
                default:
                    Template::vars([
                        'page' => [
                            'message' => 'Unknown moderation action.',
                            'redirect' => Router::route('forums.thread', $thread->id),
                        ],
                    ]);
                    break;
            }
        }

        // Print page contents
        return Template::render('global/information');
    }
}
