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
    Forums::getTopicIdFromPostId($_GET['p']) :
    0
);

$forumId = isset($_GET['f']) ?
$_GET['f'] :
Forums::getForumIdFromTopicId($topicId);

$mode = isset($_GET['f']) ? 'f' : (isset($_GET['t']) ? 't' : (isset($_GET['p']) ? 'p' : null));

// Include emotes and bbcodes
$posting = [
    'emoticons' => Main::getEmotes(),
    'bbcodes' => Main::getBBcodes(),
];

// Check if we're in reply mode
if ($mode != 'f') {
    // Attempt to get the topic
    $topic = Forum::getTopic($topicId, true);

    // Prompt an error if the topic doesn't exist
    if (!$topic) {
        // Add page specific things
        $renderData['page'] = [
            'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
            'message' => 'The requested post does not exist.',
        ];

        // Render information page
        print Templates::render('global/information.tpl', $renderData);
        exit;
    }

    // Check if we're in quote mode
    if ($mode == 'p' && isset($_GET['quote']) && $_GET['quote'] == $_GET['p'] && array_key_exists($_GET['p'], $topic['posts'])) {
        // Reassign post for ease
        $post = $topic['posts'][$_GET['p']];

        // Add subject to render data
        $posting['text'] = '[quote]' . $post['post_text'] . '[/quote]';

        // Post editing
    } elseif ($mode == 'p' && isset($_GET['edit']) && $_GET['edit'] == $_GET['p'] && array_key_exists($_GET['p'], $topic['posts'])) {
        // Checks
        if ($topic['posts'][$_GET['p']]['poster_id'] != $currentUser->data['user_id']) {
            // Add page specific things
            $renderData['page'] = [
                'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
                'message' => 'You can only edit your own posts!',
            ];

            // Render information page
            print Templates::render('global/information.tpl', $renderData);
            exit;
        }

        // Reassign post for ease
        $post = $topic['posts'][$_GET['p']];

        // Set variables
        $posting = array_merge($posting, [
            'subject' => $post['post_subject'],
            'text' => $post['post_text'],
            'id' => $post['post_id'],
        ]);
        // Post deletion
    } elseif ($mode == 'p' && isset($_GET['delete']) && $_GET['delete'] == $_GET['p'] && array_key_exists($_GET['p'], $topic['posts'])) {
        // Checks
        if ($topic['posts'][$_GET['p']]['poster_id'] != $currentUser->data['user_id']) {
            // Add page specific things
            $renderData['page'] = [
                'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
                'message' => 'You can only delete your own posts!',
            ];

            // Render information page
            print Templates::render('global/information.tpl', $renderData);
            exit;
        }

        // Submit mode
        if (isset($_POST['timestamp'], $_POST['sessionid'], $_POST['post_id'])) {
            // Post deletion code
            if (isset($_POST['yes'])) {
                // Delete the post
                Database::delete('posts', [
                    'post_id' => [$_POST['post_id'], '='],
                ]);

                // Reload the topic
                $topic = Forums::getTopic($topicId, true);

                // If there's no more posts left in the topic delete it as well
                if (!count($topic['posts'])) {
                    Database::delete('topics', [
                        'topic_id' => [$topic['topic']['topic_id'], '='],
                    ]);
                }

                // Add page specific things
                $renderData['page'] = [
                    'redirect' => (count($topic['posts']) ? $urls->format('FORUM_THREAD', [$topic['topic']['topic_id']]) : $urls->format('FORUM_INDEX')),
                    'message' => 'Your post has been deleted!',
                ];

                // Render information page
                print Templates::render('global/information.tpl', $renderData);
                exit;
                // Return to previous page
            } else {
                header('Location: ' . $urls->format('FORUM_POST', [$_POST['post_id']]));
                exit;
            }
        }

        // Form mode
        $renderData = array_merge($renderData, [
            'message' => 'Are you sure you want to delete your reply to ' . $topic['topic']['topic_title'] . '?',
            'conditions' => [
                'post_id' => $topic['posts'][$_GET['p']]['post_id'],
            ],
        ]);

        // Render confirmation form
        print Templates::render('global/confirm.tpl', $renderData);
        exit;
    }

    // Add subject to render data
    if (!isset($posting['subject'])) {
        $posting['subject'] = 'Re: ' . $topic['topic']['topic_title'];
    }
}

// Check if a post is being made
if (isset($_POST['post'])) {
    // Set post mode
    switch ($_POST['parseMode']) {
        // BBcode
        case '1':
            $parse = '1';
            break;
        // Markdown
        case '2':
            $parse = '2';
            break;
        // Raw
        default:
            $parse = '0';
    }

    // Attempt to make the post
    $makePost = Forums::createPost($currentUser->data['user_id'], $_POST['subject'], $_POST['text'], $forumId, $topicId, $parse, 1, 1);

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
    'posting' => $posting,
]);

print Templates::render('forum/posting.tpl', $renderData);
