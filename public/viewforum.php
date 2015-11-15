<?php
/*
 * Sakura Forum List Viewer
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Get the forum's data
$forum = new Forum(isset($_GET['f']) ? $_GET['f'] : -1);

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Check if the forum exists
if (!$forum) {
    // Set render data
    $renderData['page'] = [
        'title' => 'Information',
        'message' => 'The subforum you tried to access does not exist.',
    ];

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/information.tpl');
    exit;
}

// Check if the forum isn't a link
if ($forum->type === 2) {
    // Set render data
    $renderData['page'] = [
        'title' => 'Information',
        'message' => 'The forum you tried to access is a link. You\'re being redirected.',
        'redirect' => $forum->link,
    ];

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/information.tpl');
    exit;
}

$renderData['forum'] = $forum;

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('forum/viewforum.tpl');
