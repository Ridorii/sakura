<?php
/*
 * Sakura Memberlist
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Add page specific things
$renderData['page'] = [

    'ranks'     => ($_MEMBERLIST_RANKS  = Users::getAllRanks()),
    'active'    => ($_MEMBERLIST_ACTIVE = (isset($_GET['rank']) && $_GET['rank'] && array_key_exists($_GET['rank'], $_MEMBERLIST_RANKS) ? $_GET['rank'] : 0)),
    'notfound'  => ($_MEMBERLIST_NFOUND = (isset($_GET['rank']) && !array_key_exists($_GET['rank'], $_MEMBERLIST_RANKS) && $_GET['rank'] != 0)),
    'sorts'     => ($_MEMBERLIST_SORTS  = ['boxes', 'rectangles', 'list']),
    'sort'      => isset($_GET['sort']) && $_GET['sort'] && in_array($_GET['sort'], $_MEMBERLIST_SORTS) ? $_GET['sort'] : $_MEMBERLIST_SORTS[0],
    'title'     => isset($_GET['rank']) && $_GET['rank'] && !$_MEMBERLIST_NFOUND                        ? 'Viewing '. $_MEMBERLIST_RANKS[$_GET['rank']]['name'] . ($_MEMBERLIST_RANKS[$_GET['rank']]['multi'] ? 's' : '') : 'Member List',
    'page'      => isset($_GET['page']) && ($_GET['page'] - 1) >= 0                                     ? $_GET['page'] - 1 : 0,
    'users'     => array_chunk($_MEMBERLIST_ACTIVE && !$_MEMBERLIST_NFOUND                              ? Users::getUsersInRank($_MEMBERLIST_ACTIVE, null, true, true) : Users::getAllUsers(), 30, true)

];

// Print page contents
print Templates::render('main/memberlist.tpl', $renderData);
