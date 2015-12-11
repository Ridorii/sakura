<?php
/*
 * Sakura Forum Topic Viewer
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Attempt to get the thread
$thread = new Forum\Thread(
    isset($_GET['p'])
    ? (new Forum\Post($_GET['p']))->thread
    : (isset($_GET['t']) ? $_GET['t'] : 0)
);

// And attempt to get the forum
$forum = new Forum\Forum($thread->forum);

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Check if the forum exists
if (!$thread) {
    // Set render data
    $renderData['page'] = [
        'message' => 'The topic you tried to access does not exist.',
    ];

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/information');
    exit;
}

// Update the tracking status
$thread->trackUpdate($currentUser->id());

// Update views
$thread->viewsUpdate();

// Set additional render data
$renderData = array_merge($renderData, [
    'thread' => $thread,
    'forum' => $forum,
]);

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('forum/viewtopic');
