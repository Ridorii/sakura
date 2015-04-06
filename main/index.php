<?php
/*
 * Sakura Main Index
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Add page specific things
$renderData['newsPosts'] = Main::getNewsPosts(3);
$renderData['page'] = [
    'title'         => 'Flashii Dev',
    'articleCount'  => count($renderData['newsPosts'])
];
$renderData['stats'] = [
    'userCount' => ($userCount = count($users = Users::getAllUsers())) .' user'. ($userCount == 1 ? '' : 's'),
    'newestUser' => max($users),
    'lastRegDate' => ($lastRegDate = date_diff(date_create(date('Y-m-d', max($users)['regdate'])), date_create(date('Y-m-d')))->format('%a')) .' day'. ($lastRegDate == 1 ? '' : 's'),
    'chatOnline' => ($chatOnline = 0) .' user'. ($chatOnline == 1 ? '' : 's')
];

// Print page contents
print Main::tplRender('main/index.tpl', $renderData);
