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
    $renderData['memberlist'] = [
        'ranks' => ($_MEMBERLIST_RANKS = Users::getAllRanks()),
        'active' => ($_MEMBERLIST_ACTIVE = (
            isset($_GET['rank'])
            && $_GET['rank']
            && array_key_exists($_GET['rank'], $_MEMBERLIST_RANKS) ? $_GET['rank'] : 0
        )),
        'users' => ($_MEMBERLIST_ACTIVE ? Users::getUsersInRank($_MEMBERLIST_ACTIVE) : Users::getAllUsers(false)),
        'membersPerPage' => Config::get('members_per_page'),
    ];

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('main/memberlist');
} else {
    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/restricted');
}
