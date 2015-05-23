<?php
/*
 * Sakura Forum Index
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '../', dirname(__FILE__)) .'_sakura/sakura.php';

// Add page specific things
$renderData['page'] = [
    'title'     => 'Forum Listing',
    'boards'    => Forum::getBoardList()
];
$renderData['stats'] = [
    'userCount'     => ($_INDEX_USER_COUNT = count($_INDEX_USERS = Users::getAllUsers(false))) .' user'. ($_INDEX_USER_COUNT == 1 ? '' : 's'),
    'newestUser'    => ($_INDEX_NEWEST_USER = max($_INDEX_USERS)),
    'lastRegDate'   => ($_INDEX_LAST_REGDATE = date_diff(date_create(date('Y-m-d', $_INDEX_NEWEST_USER['regdate'])), date_create(date('Y-m-d')))->format('%a')) .' day'. ($_INDEX_LAST_REGDATE == 1 ? '' : 's'),
    'chatOnline'    => ($_INDEX_CHAT_ONLINE = count(SockChat::getOnlineUsers())) .' user'. ($_INDEX_CHAT_ONLINE == 1 ? '' : 's'),
    'onlineUsers'   => Users::checkAllOnline(),
    'topicCount'    => '0 topics',
    'postCount'     => '0 posts'
];

// Print page contents
print Templates::render('forum/index.tpl', $renderData);
