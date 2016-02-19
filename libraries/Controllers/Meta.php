<?php
/**
 * Holds the meta page controllers.
 * 
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\DB;
use Sakura\News;
use Sakura\Template;
use Sakura\User;
use Sakura\Users;
use Sakura\Utils;

/**
 * Meta page controllers (sections that aren't big enough to warrant a dedicated controller).
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Meta extends Controller
{
    /**
     * Serves the site index.
     * 
     * @return mixed HTML for the index.
     */
    public function index()
    {
        $userCount = DB::prepare("SELECT * FROM `{prefix}users` WHERE `password_algo` != 'disabled' AND `rank_main` != 1");
        $userCount->execute();
        $threadCount = DB::prepare('SELECT * FROM `{prefix}topics`');
        $threadCount->execute();
        $postCount = DB::prepare('SELECT * FROM `{prefix}posts`');
        $postCount->execute();

        // Merge index specific stuff with the global render data
        Template::vars([
            'news' => new News(Config::get('site_news_category')),
            'newsCount' => Config::get('front_page_news_posts'),
            'stats' => [
                'userCount' => $userCount->rowCount(),
                'newestUser' => User::construct(Users::getNewestUserId()),
                'lastRegDate' => date_diff(
                    date_create(date('Y-m-d', User::construct(Users::getNewestUserId())->registered)),
                    date_create(date('Y-m-d'))
                )->format('%a'),
                'topicCount' => $threadCount->rowCount(),
                'postCount' => $postCount->rowCount(),
                'onlineUsers' => Users::checkAllOnline(),
            ],
        ]);

        // Return the compiled page
        return Template::render('main/index');
    }

    /**
     * Handles the news pages.
     * 
     * @return mixed HTML for the correct news section.
     */
    public function news()
    {
        // Get arguments
        $args = func_get_args();
        $category = isset($args[0]) && !is_numeric($args[0]) ? $args[0] : Config::get('site_news_category');
        $post = isset($args[1]) && is_numeric($args[1]) ? $args[1] : (
            isset($args[0]) && is_numeric($args[0]) ? $args[0] : 0
        );

        // Create news object
        $news = new News($category);

        // Set parse variables
        Template::vars([
            'news' => $news,
            'postsPerPage' => Config::get('news_posts_per_page'),
            'viewPost' => $post != 0,
            'postExists' => $news->postExists($post),
        ]);

        // Print page contents
        return Template::render('main/news');
    }

    /**
     * Displays the FAQ.
     * 
     * @return mixed HTML for the FAQ.
     */
    public function faq()
    {
        // Get faq entries
        $faq = DB::prepare('SELECT * FROM `{prefix}faq` ORDER BY `faq_id`');
        $faq->execute();
        $faq = $faq->fetchAll();

        // Set parse variables
        Template::vars([
            'page' => [
                'title' => 'Frequently Asked Questions',
                'questions' => $faq,
            ],
        ]);

        // Print page contents
        return Template::render('main/faq');
    }

    /**
     * Handles the info pages.
     * 
     * @param string $id The page ID from the database.
     * 
     * @return mixed HTML for the info page.
     */
    public function infoPage($id = null)
    {
        // Set default variables
        Template::vars([
            'page' => [
                'content' => '<h1>Unable to load the requested info page.</h1><p>Check the URL and try again.</p>',
            ],
        ]);

        // Set page id
        $id = strtolower($id);

        // Get the page from the database
        $ipData = DB::prepare('SELECT * FROM `{prefix}infopages` WHERE `page_shorthand` = :id');
        $ipData->execute([
            'id' => $id,
        ]);
        $ipData = $ipData->fetch();

        // Get info page data from the database
        if ($ipData) {
            // Assign new proper variable
            Template::vars([
                'page' => [
                    'id' => $id,
                    'title' => $ipData->page_title,
                    'content' => $ipData->page_content,
                ],
            ]);
        }

        // Return the compiled page
        return Template::render('main/infopage');
    }

    /**
     * Search page
     * 
     * @return mixed HTML for the search page.
     */
    public function search()
    {
        // Set parse variables
        Template::vars([
            'page' => [
                'title' => 'Search',
            ],
        ]);

        // Print page contents
        return Template::render('main/search');
    }
}
