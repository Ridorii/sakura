<?php
/**
 * Holds the meta page controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\DB;
use Sakura\News\Category;
use Sakura\Template;
use Sakura\User;

/**
 * Meta page controllers (sections that aren't big enough to warrant a dedicated controller).
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class MetaController extends Controller
{
    /**
     * Serves the site index.
     *
     * @return mixed HTML for the index.
     */
    public function index()
    {
        // Get the newest user
        $newestUserId = DB::table('users')
            ->whereNotIn('rank_main', [config('rank.banned'), config('rank.inactive')])
            ->orderBy('user_id', 'desc')
            ->limit(1)
            ->get(['user_id']);
        $newestUser = User::construct($newestUserId ? $newestUserId[0]->user_id : 0);

        // Get all the currently online users
        $timeRange = time() - 120;

        // Create a storage variable
        $onlineUsers = [];

        // Get all online users
        $getOnline = DB::table('users')
            ->where('user_last_online', '>', $timeRange)
            ->get(['user_id']);
        $getOnline = array_column($getOnline, 'user_id');

        foreach ($getOnline as $user) {
            $user = User::construct($user);

            // Do a second check
            if (!$user->isOnline()) {
                continue;
            }

            $onlineUsers[$user->id] = $user;
        }

        // Get news
        $news = new Category(config('general.news'));

        // Merge index specific stuff with the global render data
        Template::vars([
            'news' => $news->posts(3),
            'stats' => [
                'userCount' => DB::table('users')
                    ->whereNotIn('rank_main', [config('rank.banned'), config('rank.inactive')])
                    ->count(),
                'newestUser' => $newestUser,
                'lastRegDate' => date_diff(
                    date_create(date('Y-m-d', $newestUser->registered)),
                    date_create(date('Y-m-d'))
                )->format('%a'),
                'topicCount' => DB::table('topics')->where('forum_id', '!=', config('forum.trash'))->count(),
                'postCount' => DB::table('posts')->where('forum_id', '!=', config('forum.trash'))->count(),
                'onlineUsers' => $onlineUsers,
            ],
        ]);

        // Return the compiled page
        return Template::render('meta/index');
    }

    /**
     * Displays the FAQ.
     *
     * @return mixed HTML for the FAQ.
     */
    public function faq()
    {
        // Get faq entries
        $faq = DB::table('faq')
            ->orderBy('faq_id')
            ->get();

        // Set parse variables
        Template::vars([
            'page' => [
                'title' => 'Frequently Asked Questions',
                'questions' => $faq,
            ],
        ]);

        // Print page contents
        return Template::render('meta/faq');
    }

    /**
     * Search page
     *
     * @return mixed HTML for the search page.
     */
    public function search()
    {
        return Template::render('meta/search');
    }
}
