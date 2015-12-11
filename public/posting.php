<?php
/*
 * Sakura Forum Posting
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Set location
$topicId = isset($_GET['t']) ?
$_GET['t'] :
(
    isset($_GET['p']) ?
    (new Forum\Post($_GET['p']))->thread :
    0
);

$forumId = isset($_GET['f']) ?
$_GET['f'] :
($thread = new Forum\Thread($topicId))->forum;

$mode = isset($_GET['f']) ? 'f' : (isset($_GET['t']) ? 't' : (isset($_GET['p']) ? 'p' : null));

// Include emotes and bbcodes
$posting = [
    'emoticons' => Main::getEmotes(),
];

// Check if we're in reply mode
if ($mode != 'f') {
    // Attempt to get the topic
    $thread = $thread ? $thread : new Forum\Thread($topicId);

    // Prompt an error if the topic doesn't exist
    if (!$thread->id) {
        // Add page specific things
        $renderData['page'] = [
            'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
            'message' => 'The requested post does not exist.',
        ];

        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        echo $template->render('global/information');
        exit;
    }

    // Check if we're in quote mode
    if ($mode == 'p' && isset($_GET['quote']) && $_GET['quote'] == $_GET['p'] && array_key_exists($_GET['p'], $thread->posts())) {
        // Reassign post for ease
        $post = $thread->posts()[$_GET['p']];

        // Add subject to render data
        $posting['text'] = '[quote=' . $post->poster->username() . ']' . BBcode::toEditor($post->text) . '[/quote]';

        // Post editing
    } elseif ($mode == 'p' && isset($_GET['edit']) && $_GET['edit'] == $_GET['p'] && array_key_exists($_GET['p'], $thread->posts())) {
        // Checks
        if ($thread->posts()[$_GET['p']]->poster->id() != $currentUser->id()) {
            // Add page specific things
            $renderData['page'] = [
                'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
                'message' => 'You can only edit your own posts!',
            ];

            // Set parse variables
            $template->setVariables($renderData);

            // Print page contents
            echo $template->render('global/information');
            exit;
        }

        // Reassign post for ease
        $post = $thread->posts()[$_GET['p']];

        // Set variables
        $posting = array_merge($posting, [
            'subject' => $post->subject,
            'text' => BBcode::toEditor($post->text),
            'id' => $post->id,
        ]);
        // Post deletion
    } elseif ($mode == 'p' && isset($_GET['delete']) && $_GET['delete'] == $_GET['p'] && array_key_exists($_GET['p'], $thread->posts())) {
        // Checks
        if ($thread->posts()[$_GET['p']]->poster->id() != $currentUser->id()) {
            // Add page specific things
            $renderData['page'] = [
                'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
                'message' => 'You can only delete your own posts!',
            ];

            // Set parse variables
            $template->setVariables($renderData);

            // Print page contents
            echo $template->render('global/information');
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
                $thread = new Forum\Thread($topicId);

                // If there's no more posts left in the topic delete it as well
                if (!$thread->replyCount()) {
                    Database::delete('topics', [
                        'topic_id' => [$thread->id, '='],
                    ]);
                }

                // Add page specific things
                $renderData['page'] = [
                    'redirect' => ($thread->replyCount() ? $urls->format('FORUM_THREAD', [$thread->id]) : $urls->format('FORUM_INDEX')),
                    'message' => 'Your post has been deleted!',
                ];

                // Set parse variables
                $template->setVariables($renderData);

                // Print page contents
                echo $template->render('global/information');
                exit;
                // Return to previous page
            } else {
                header('Location: ' . $urls->format('FORUM_POST', [$_POST['post_id']]));
                exit;
            }
        }

        // Form mode
        $renderData = array_merge($renderData, [
            'message' => 'Are you sure you want to delete your reply to ' . $thread->title . '?',
            'conditions' => [
                'post_id' => $thread->posts()[$_GET['p']]->id,
            ],
        ]);

        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        echo $template->render('global/confirm');
        exit;
    }

    // Add subject to render data
    if (!isset($posting['subject'])) {
        $posting['subject'] = 'Re: ' . $thread->title;
    }
}

// Check if a post is being made
if (isset($_POST['post'])) {
    // Attempt to make the post
    $post = Forum\Post::create($_POST['subject'], $_POST['text'], $currentUser, $topicId,  $forumId);

    // Add page specific things
    $renderData['page'] = [
        'redirect' => $urls->format('FORUM_POST', [$post->id]) . '#p' . $post->id,
        'message' => 'Made the post!',
        'success' => 1,
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
        echo $template->render('global/information');
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
echo $template->render('forum/posting');
