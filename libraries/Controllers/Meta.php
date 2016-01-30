<?php
/*
 * Meta controllers
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\Database;
use Sakura\News;
use Sakura\Template;
use Sakura\User;
use Sakura\Users;
use Sakura\Utils;

/**
 * Class Meta
 * @package Sakura
 */
class Meta
{
    // Site index
    public static function index()
    {
        // Get the global renderData
        global $renderData;

        // Initialise templating engine
        $template = new Template();

        // Merge index specific stuff with the global render data
        $renderData = array_merge(
            $renderData,
            [
                'news' => new News(Config::get('site_news_category')),
                'newsCount' => Config::get('front_page_news_posts'),
                'stats' => [
                    'userCount' => Database::count('users', ['password_algo' => ['nologin', '!='], 'rank_main' => ['1', '!=']])[0],
                    'newestUser' => User::construct(Users::getNewestUserId()),
                    'lastRegDate' => date_diff(
                        date_create(date('Y-m-d', User::construct(Users::getNewestUserId())->registered)),
                        date_create(date('Y-m-d'))
                    )->format('%a'),
                    'topicCount' => Database::count('topics')[0],
                    'postCount' => Database::count('posts')[0],
                    'onlineUsers' => Users::checkAllOnline(),
                ],
            ]
        );

        // Set parse variables
        $template->setVariables($renderData);

        // Return the compiled page
        return $template->render('main/index');
    }

    // News
    public static function news()
    {
        // Get the global renderData
        global $renderData;

        // Get arguments
        $args = func_get_args();
        $category = isset($args[0]) && !is_numeric($args[0]) ? $args[0] : Config::get('site_news_category');
        $post = isset($args[1]) && is_numeric($args[1]) ? $args[1] : (
            isset($args[0]) && is_numeric($args[0]) ? $args[0] : 0
        );

        // Create news object
        $news = new News($category);

        // Merge the data for this page with the global
        $renderData = array_merge($renderData, [
            'news' => $news,
            'postsPerPage' => Config::get('news_posts_per_page'),
            'viewPost' => $post != 0,
            'postExists' => $news->postExists($post),
        ]);

        // Initialise templating engine
        $template = new Template();

        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        return $template->render('main/news');
    }

    // FAQ
    public static function faq()
    {
        // Get the global renderData
        global $renderData;

        // Add page specific things
        $renderData['page'] = [
            'title' => 'Frequently Asked Questions',
            'questions' => Utils::getFaqData(),
        ];

        // Initialise templating engine
        $template = new Template();

        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        echo $template->render('main/faq');

    }

    // Info pages
    public static function infoPage($id = null)
    {
        // Get the global renderData
        global $renderData;

        // Initialise templating engine
        $template = new Template();

        // Set default variables
        $renderData['page'] = [
            'content' => '<h1>Unable to load the requested info page.</h1><p>Check the URL and try again.</p>',
        ];

        // Set page id
        $id = strtolower($id);

        // Get info page data from the database
        if ($ipData = Utils::loadInfoPage($id)) {
            // Assign new proper variable
            $renderData['page'] = [
                'id' => $id,
                'title' => $ipData['page_title'],
                'content' => $ipData['page_content'],
            ];
        }

        // Set parse variables
        $template->setVariables($renderData);

        // Return the compiled page
        return $template->render('main/infopage');
    }

    // Search
    public static function search()
    {
        // Get the global renderData
        global $renderData;

        // Add page specific things
        $renderData['page'] = [
            'title' => 'Search',
        ];

        // Initialise templating engine
        $template = new Template();

        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        echo $template->render('main/search');
    }
}
