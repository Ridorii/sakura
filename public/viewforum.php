<?php
/*
 * Sakura Forum List Viewer
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Get the forum's data
$forum = Forum::getForum(isset($_GET['f']) ? $_GET['f'] : 0);

// Check if the forum exists
if (!$forum) {
    // Set render data
    $renderData['page'] = [
        'title' => 'Information',
        'message' => 'The subforum you tried to access does not exist.',
    ];

    // Print template
    print Templates::render('global/information.tpl', $renderData);
    exit;
}

// Check if the forum isn't a link
if ($forum['forum']['forum_type'] === 2) {
    // Set render data
    $renderData['page'] = [
        'title' => 'Information',
        'message' => 'The forum you tried to access is a link. You\'re being redirected.',
        'redirect' => $forum['forum']['forum_link'],
    ];

    // Print template
    print Templates::render('global/information.tpl', $renderData);
    exit;
}

$renderData['board'] = [
    'forums' => [
        $forum,
    ],
    'topics' => array_chunk($forum['topics'], 25, true),
    'viewforum' => true,
    'viewtopic' => false,
];
$renderData['currentPage'] = isset($_GET['page']) && ($_GET['page'] - 1) >= 0 ? $_GET['page'] - 1 : 0;

// Print page contents
print Templates::render('forum/viewforum.tpl', $renderData);
