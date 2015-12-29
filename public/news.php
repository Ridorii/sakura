<?php
/*
 * Sakura News Page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Create a new News object
$news = new News(isset($_GET['cat']) ? $_GET['cat'] : Config::get('site_news_category'));

$renderData = array_merge($renderData, [
    'news' => $news,
    'postsPerPage' => Config::get('news_posts_per_page'),
    'viewPost' => isset($_GET['id']),
    'postExists' => $news->postExists(isset($_GET['id']) ? $_GET['id'] : 0),
]);

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('main/news');
