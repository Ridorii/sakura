<?php
/*
 * Sakura Forum Posting
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Set location
$topicId = isset($_GET['t']) ?
$_GET['t'] :
(
    isset($_GET['p']) ?
    Forum\Forums::getTopicIdFromPostId($_GET['p']) :
    0
);

$forumId = isset($_GET['f']) ?
$_GET['f'] :
Forum\Forums::getForumIdFromTopicId($topicId);

$mode = isset($_GET['f']) ? 'f' : (isset($_GET['t']) ? 't' : (isset($_GET['p']) ? 'p' : null));

// Include emotes and bbcodes
$posting = [
    'emoticons' => Main::getEmotes(),
    'bbcodes' => Main::getBBcodes(),
];

// Check if we're in reply mode
if ($mode != 'f') {
    // Attempt to get the topic
    $thread = Forum\Forums::getTopic($topicId, true);

    // Prompt an error if the topic doesn't exist
    if (!$thread) {
        // Add page specific things
        $renderData['page'] = [
            'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
            'message' => 'The requested post does not exist.',
        ];

        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        echo $template->render('global/information.tpl');
        exit;
    }

    // Check if we're in quote mode
    if ($mode == 'p' && isset($_GET['quote']) && $_GET['quote'] == $_GET['p'] && array_key_exists($_GET['p'], $thread['posts'])) {
        // Reassign post for ease
        $post = $thread['posts'][$_GET['p']];

        // Add subject to render data
        $posting['text'] = '[quote]' . $post['post_text'] . '[/quote]';

        // Post editing
    } elseif ($mode == 'p' && isset($_GET['edit']) && $_GET['edit'] == $_GET['p'] && array_key_exists($_GET['p'], $thread['posts'])) {
        // Checks
        if ($thread['posts'][$_GET['p']]['poster_id'] != $currentUser->id()) {
            // Add page specific things
            $renderData['page'] = [
                'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
                'message' => 'You can only edit your own posts!',
            ];

            // Set parse variables
            $template->setVariables($renderData);

            // Print page contents
            echo $template->render('global/information.tpl');
            exit;
        }

        // Reassign post for ease
        $post = $thread['posts'][$_GET['p']];

        // Set variables
        $posting = array_merge($posting, [
            'subject' => $post['post_subject'],
            'text' => $post['post_text'],
            'id' => $post['post_id'],
        ]);
        // Post deletion
    } elseif ($mode == 'p' && isset($_GET['delete']) && $_GET['delete'] == $_GET['p'] && array_key_exists($_GET['p'], $thread['posts'])) {
        // Checks
        if ($thread['posts'][$_GET['p']]['poster_id'] != $currentUser->id()) {
            // Add page specific things
            $renderData['page'] = [
                'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
                'message' => 'You can only delete your own posts!',
            ];

            // Set parse variables
            $template->setVariables($renderData);

            // Print page contents
            echo $template->render('global/information.tpl');
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
                $thread = Forum\Forums::getTopic($topicId, true);

                // If there's no more posts left in the topic delete it as well
                if (!count($thread['posts'])) {
                    Database::delete('topics', [
                        'topic_id' => [$thread['topic']['topic_id'], '='],
                    ]);
                }

                // Add page specific things
                $renderData['page'] = [
                    'redirect' => (count($thread['posts']) ? $urls->format('FORUM_THREAD', [$thread['topic']['topic_id']]) : $urls->format('FORUM_INDEX')),
                    'message' => 'Your post has been deleted!',
                ];

                // Set parse variables
                $template->setVariables($renderData);

                // Print page contents
                echo $template->render('global/information.tpl');
                exit;
                // Return to previous page
            } else {
                header('Location: ' . $urls->format('FORUM_POST', [$_POST['post_id']]));
                exit;
            }
        }

        // Form mode
        $renderData = array_merge($renderData, [
            'message' => 'Are you sure you want to delete your reply to ' . $thread['topic']['topic_title'] . '?',
            'conditions' => [
                'post_id' => $thread['posts'][$_GET['p']]['post_id'],
            ],
        ]);

        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        echo $template->render('global/confirm.tpl');
        exit;
    }

    // Add subject to render data
    if (!isset($posting['subject'])) {
        $posting['subject'] = 'Re: ' . $thread['topic']['topic_title'];
    }
}

// Check if a post is being made
if (isset($_POST['post'])) {
    // Attempt to make the post
    $makePost = Forum\Forums::createPost($currentUser->id(), $_POST['subject'], $_POST['text'], $forumId, $topicId, 1, 1, 1);

    // Add page specific things
    $renderData['page'] = [
        'redirect' => $urls->format('FORUM_THREAD', [$makePost[3]]),
        'message' => 'Made the post!',
        'success' => $makePost[0],
    ];

    // Print page contents or if the AJAX request is set only display the render data
    if (isset($_REQUEST['ajax'])) {
        echo $renderData['page']['message'] . '|' .
            $renderData['page']['success'] . '|' .
            $renderData['page']['redirect'];
    } else {
        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        echo $template->render('global/information.tpl');
    }
    exit;
}

// Set additional render data
$renderData = array_merge($renderData, [
    'posting' => $posting,
]);

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('forum/posting.tpl');
