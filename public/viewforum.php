<?php
/*
 * Sakura Forum List Viewer
 */

// Declare Namespace
namespace Sakura;

use Sakura\Perms\Forum as ForumPerms;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Get the forum's data
$forum = new Forum\Forum(isset($_GET['f']) ? $_GET['f'] : -1);

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Check if the forum exists
if ($forum->id < 0) {
    // Set render data
    $renderData['page'] = [
        'title' => 'Information',
        'message' => 'The forum you tried to access does not exist.',
    ];

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/information');
    exit;
}

// Check if the user has access to the forum
if (!$forum->permission(ForumPerms::VIEW, $currentUser->id())) {
    // Set render data
    $renderData['page'] = [
        'title' => 'Information',
        'message' => 'You do not have access to this forum.',
    ];

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/information');
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
    echo $template->render('global/information');
    exit;
}

// Check if we're marking as read
if (isset($_GET['read']) && $_GET['read'] && isset($_GET['session']) && $_GET['session'] == session_id()) {
    // Run the function
    $forum->trackUpdateAll($currentUser->id());

    // Set render data
    $renderData['page'] = [
        'title' => 'Information',
        'message' => 'All threads have been marked as read.',
        'redirect' => $urls->format('FORUM_SUB', [$forum->id]),
    ];

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/information');
    exit;
}

// Redirect forum id 0 to the main page
if ($forum->id === 0) {
    header('Location: ' . $urls->format('FORUM_INDEX'));
    exit;
}

$renderData['forum'] = $forum;

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('forum/viewforum');
