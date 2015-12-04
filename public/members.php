<?php
/*
 * Sakura Memberlist
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// CHeck if the user is logged in
if (Users::checkLogin()) {
    // Add page specific things
    $renderData['page'] = [

        'ranks' => ($_MEMBERLIST_RANKS = Users::getAllRanks()),
        'active' => ($_MEMBERLIST_ACTIVE = (
            isset($_GET['rank'])
            && $_GET['rank']
            && array_key_exists($_GET['rank'], $_MEMBERLIST_RANKS) ? $_GET['rank'] : 0
        )),
        'notfound' => ($_MEMBERLIST_NFOUND = (
            isset($_GET['rank'])
            && !array_key_exists($_GET['rank'], $_MEMBERLIST_RANKS) && $_GET['rank'] != 0
        )),
        'sorts' => ($_MEMBERLIST_SORTS = ['boxes', 'rectangles', 'list']),
        'sort' => isset($_GET['sort']) && $_GET['sort'] && in_array($_GET['sort'], $_MEMBERLIST_SORTS) ?
        $_GET['sort'] :
        $_MEMBERLIST_SORTS[0],

    ];

    $renderData['users'] = ($_MEMBERLIST_ACTIVE && !$_MEMBERLIST_NFOUND ? Users::getUsersInRank($_MEMBERLIST_ACTIVE) : Users::getAllUsers());

    $renderData['membersPerPage'] = Config::get('members_per_page');

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('main/memberlist.tpl');
} else {
    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/restricted.tpl');
}
