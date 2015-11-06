<?php
/*
 * Sakura Forum Topic Viewer
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Attempt to get a topic
$topic = Forums::getTopic(
    isset($_GET['p'])
    ? Forums::getTopicIdFromPostId($_GET['p'])
    : (isset($_GET['t']) ? $_GET['t'] : 0)
);

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Check if the forum exists
if (!$topic) {
    // Set render data
    $renderData['page'] = [
        'message' => 'The topic you tried to access does not exist.',
    ];

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/information.tpl');
    exit;
}

// Set additional render data
$renderData = array_merge($renderData, $topic, [
    'board' => [
        'viewforum' => false,
        'viewtopic' => true,
    ],
    'posts' => array_chunk($topic['posts'], 10, true),
    'currentPage' => isset($_GET['page']) && ($_GET['page'] - 1) >= 0 ? $_GET['page'] - 1 : 0,
]);

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('forum/viewtopic.tpl');
