<?php
/*
 * Sakura Forum Topic Viewer
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Attempt to get a topic
$topic = Forum::getTopic(isset($_GET['p']) ? Forum::getTopicIdFromPostId($_GET['p']) : (isset($_GET['t']) ? $_GET['t'] : 0));

// Check if the forum exists
if(!$topic) {

    // Set render data
    $renderData['page'] = [
        'title'     => 'Information',
        'message'   => 'The topic you tried to access does not exist.'
    ];

    // Print template
    print Templates::render('errors/information.tpl', $renderData);
    exit;

}

// Set additional render data
$renderData = array_merge($renderData, $topic, [
    'board' => [
        'viewforum' => false,
        'viewtopic' => true,
    ],
    'page' => [
        'title' => $topic['topic']['topic_title']
    ]
]);

// Print page contents
print Templates::render('forum/viewtopic.tpl', $renderData);
