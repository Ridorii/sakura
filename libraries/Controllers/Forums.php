<?php
/**
 * Holds the forum pages controllers.
 * 
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\Database;
use Sakura\Forum;
use Sakura\Perms\Forum as ForumPerms;
use Sakura\Template;
use Sakura\User;
use Sakura\Users;
use Sakura\Utils;

/**
 * Forum page controllers.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Forums
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
            'forum' => (new Forum\Forum()),
            'stats' => [
                'userCount' => Database::count('users', ['password_algo' => ['nologin', '!='], 'rank_main' => ['1', '!=']])[0],
                'newestUser' => User::construct(Users::getNewestUserId()),
                'lastRegData' => date_diff(
                    date_create(date('Y-m-d', User::construct(Users::getNewestUserId())->registered)),
                    date_create(date('Y-m-d'))
                )->format('%a'),
                'topicCount' => Database::count('topics')[0],
                'postCount' => Database::count('posts')[0],
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
        $forum = new Forum\Forum($id);

        // Redirect forum id 0 to the main page
        if ($forum->id === 0) {
            header('Location: ' . (new \Sakura\Urls)->format('FORUM_INDEX'));
            exit;
        }

        // Check if the forum exists
        if ($forum->id < 0) {
            // Set render data
            Template::vars([
                'page' => [
                    'message' => 'The forum you tried to access does not exist.',
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
                ]
            ]);
            
            // Print page contents
            return Template::render('global/information');
        }

        // Check if we're marking as read
        if (isset($_GET['read']) && $_GET['read'] && isset($_GET['session']) && $_GET['session'] == session_id()) {
            // Run the function
            $forum->trackUpdateAll($currentUser->id);

            // Set render data
            Template::vars([
                'page' => [
                    'message' => 'All threads have been marked as read.',
                    'redirect' => (new \Sakura\Urls)->format('FORUM_SUB', [$forum->id]),
                ]
            ]);

            // Print page contents
            return Template::render('global/information');
        }

        $renderData['forum'] = $forum;

        // Set parse variables
        Template::vars([
            'forum' => $forum,
        ]);

        // Print page contents
        return Template::render('forum/viewforum');
    }
}
