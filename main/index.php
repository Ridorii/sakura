<?php
/*
 * Sakura Main Index
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Are we in forum mode?
$forumMode = isset($_GET['forums']) ? ($_GET['forums'] == true) : false;

// Add page specific things
$renderData['newsPosts'] = ($forumMode ? null : Main::getNewsPosts(Configuration::getConfig('front_page_news_posts')));

$renderData['page'] = [
    'title'         => ($forumMode ? 'Forum Listing' : Configuration::getConfig('sitename')),
    'friend_req'    => Users::getPendingFriends()
];

$renderData['board'] = [
    'forums'    => ($forumMode ? Forum::getForumList() : null),
    'viewforum' => false,
    'viewtopic' => false
];

$renderData['stats'] = [
    'userCount'     => ($_INDEX_USER_COUNT      = count($_INDEX_USERS = Users::getAllUsers(false))) .' user'. ($_INDEX_USER_COUNT == 1 ? '' : 's'),
    'newestUser'    => ($_INDEX_NEWEST_USER     = empty($_INDEX_USERS) ? (new User(0))->data : max($_INDEX_USERS)),
    'lastRegDate'   => ($_INDEX_LAST_REGDATE    = date_diff(date_create(date('Y-m-d', $_INDEX_NEWEST_USER['regdate'])), date_create(date('Y-m-d')))->format('%a')) .' day'. ($_INDEX_LAST_REGDATE == 1 ? '' : 's'),
    'onlineUsers'   => Users::checkAllOnline(),
    'topicCount'    => ($_TOPICS    = count(Database::fetch('topics'))) .' topic'.  ($_TOPICS   != 1 ? 's' : ''),
    'postCount'     => ($_POSTS     = count(Database::fetch('posts')))  .' post'.   ($_POSTS    != 1 ? 's' : '')
];

// Print page contents
print Templates::render(($forumMode ? 'forum' : 'main') .'/index.tpl', $renderData);
