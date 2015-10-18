<?php
/*
 * Sakura Forum Posting
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Set location
$topicId = isset($_GET['t']) ?
$_GET['t'] :
(
    isset($_GET['p']) ?
    Forum::getTopicIdFromPostId($_GET['p']) :
    0
);

$forumId = isset($_GET['f']) ?
$_GET['f'] :
Forum::getForumIdFromTopicId($topicId);

$mode = isset($_GET['f']) ? 'f' : (isset($_GET['t']) || isset($_GET['p']) ? 't' : null);

// Check if a post is being made
if (isset($_POST['post'])) {
    // Set post mode
    switch($_POST['parseMode']) {
        case '1':
            $parse = '1';
            break;
        case '2':
            $parse = '2';
            break;
        default:
            $parse = '0';
    }

    // Attempt to make the post
    $makePost = Forum::createPost($currentUser->data['user_id'], $_POST['subject'], $_POST['text'], $forumId, $topicId, $parse);

    // Add page specific things
    $renderData['page'] = [

        'redirect' => $urls->format('FORUM_THREAD', [$makePost[3]]),
        'message' => 'Made the post!',
        'success' => $makePost[0],

    ];

    // Print page contents or if the AJAX request is set only display the render data
    print isset($_REQUEST['ajax']) ?
        (
            $renderData['page']['message'] . '|' .
            $renderData['page']['success'] . '|' .
            $renderData['page']['redirect']
        ) :
        Templates::render('global/information.tpl', $renderData);
    exit;
}

// Set additional render data
$renderData = array_merge($renderData, [

    'posting' => [

        'emoticons' => Main::getEmotes(),
        'bbcodes' => Main::getBBcodes(),

    ],

]);

print Templates::render('forum/posting.tpl', $renderData);
