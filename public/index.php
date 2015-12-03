<?php
/*
 * Sakura Main Index
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Info pages
if (isset($_GET['p'])) {
    // Set default variables
    $renderData['page'] = [
        'content' => Main::mdParse("# Unable to load the requested info page.\r\n\r\nCheck the URL and try again."),
    ];

    // Set page id
    $pageId = isset($_GET['p']) ? strtolower($_GET['p']) : '';

    // Get info page data from the database
    if ($ipData = Main::loadInfoPage($pageId)) {
        // Assign new proper variable
        $renderData['page'] = [
            'id' => $pageId,
            'title' => $ipData['page_title'],
            'content' => Main::mdParse($ipData['page_content']),
        ];
    }

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('main/infopage.tpl');
    exit;
}

// Are we in forum mode?
$forumMode = isset($_GET['forum']) ? ($_GET['forum'] == true) : false;

$renderData['news'] = ($forumMode ? null : (new News(Config::getConfig('site_news_category'))));

$renderData['newsCount'] = Config::getConfig('front_page_news_posts');

$renderData['forum'] = ($forumMode ? (new Forum\Forum()) : null);

$renderData['stats'] = [
    'userCount' => Database::count('users', ['password_algo' => ['nologin', '!='], 'rank_main' => ['1', '!=']])[0],
    'newestUser' => ($_INDEX_NEWEST_USER = new User(Users::getNewestUserId())),
    'lastRegDate' => ($_INDEX_LAST_REGDATE = date_diff(
        date_create(
            date(
                'Y-m-d',
                $_INDEX_NEWEST_USER->dates()['joined']
            )
        ),
        date_create(
            date('Y-m-d')
        )
    )->format('%a')) . ' day' . ($_INDEX_LAST_REGDATE == 1 ? '' : 's'),
    'topicCount' => Database::count('topics')[0],
    'postCount' => Database::count('posts')[0],
    'onlineUsers' => Users::checkAllOnline(),
];

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render(($forumMode ? 'forum' : 'main') . '/index.tpl');
