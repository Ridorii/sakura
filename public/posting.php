<?php
/*
 * Sakura Forum Posting
 * Needs to be thoroughly unfucked before permissions can be properly implemented
 */

// Declare Namespace
namespace Sakura;

use Sakura\Perms\Forum as ForumPerms;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Set location
$topicId = isset($_GET['t']) ?
$_GET['t'] :
(
    isset($_GET['p']) ?
    (new Forum\Post($_GET['p']))->thread :
    0
);

// Get the topic
if ($topicId) {
    $thread = new Forum\Thread($topicId);
}

$forumId = isset($_GET['f']) ?
$_GET['f'] :
$thread->forum;

// Creare forum class
$forum = new Forum\Forum($forumId);

// Check if the user has access to the forum
if (!$forum->permission(ForumPerms::VIEW, $currentUser->id) || !$forum->permission(ForumPerms::REPLY, $currentUser->id)) {
    // Set render data
    $renderData['page'] = [
        'title' => 'Information',
        'message' => 'You do not have access to this forum.',
    ];

    // Set parse variables
    Template::vars($renderData);

    // Print page contents
    echo Template::render('global/information');
    exit;
}

// Check if the user has access to the forum
if (!isset($thread) && !$forum->permission(ForumPerms::CREATE_THREADS, $currentUser->id)) {
    // Set render data
    $renderData['page'] = [
        'title' => 'Information',
        'message' => 'You are not allowed to create threads in this forum.',
    ];

    // Set parse variables
    Template::vars($renderData);

    // Print page contents
    echo Template::render('global/information');
    exit;
}

$mode = isset($_GET['f']) ? 'f' : (isset($_GET['t']) ? 't' : (isset($_GET['p']) ? 'p' : null));

$emotes = DBv2::prepare('SELECT * FROM `{prefix}emoticons`');
$emotes->execute();

// Include emotes and bbcodes
$posting = [
    'emoticons' => $emotes->fetchAll(),
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
        Template::vars($renderData);

        // Print page contents
        echo Template::render('global/information');
        exit;
    }

    // Prompt an error if the topic doesn't exist
    if ($thread->status == 1 && !$forum->permission(ForumPerms::LOCK, $currentUser->id)) {
        // Add page specific things
        $renderData['page'] = [
            'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
            'message' => 'The thread you tried to reply to is locked.',
        ];

        // Set parse variables
        Template::vars($renderData);

        // Print page contents
        echo Template::render('global/information');
        exit;
    }

    // Check if we're in quote mode
    if ($mode == 'p' && isset($_GET['quote']) && $_GET['quote'] == $_GET['p'] && array_key_exists($_GET['p'], $thread->posts())) {
        // Reassign post for ease
        $post = $thread->posts()[$_GET['p']];

        // Add subject to render data
        $posting['text'] = '[quote=' . $post->poster->username . ']' . BBcode::toEditor($post->text) . '[/quote]';

        // Post editing
    } elseif ($mode == 'p' && isset($_GET['edit']) && $_GET['edit'] == $_GET['p'] && array_key_exists($_GET['p'], $thread->posts())) {
        // Permissions
        if (!$currentUser->permission(ForumPerms::EDIT_OWN, Perms::FORUM)) {
            // Add page specific things
            $renderData['page'] = [
                'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
                'message' => 'You are not allowed to edit posts!',
            ];

            // Set parse variables
            Template::vars($renderData);

            // Print page contents
            echo Template::render('global/information');
            exit;
        }
        // Checks
        if ($thread->posts()[$_GET['p']]->poster->id != $currentUser->id && !$forum->permission(ForumPerms::EDIT_ANY, $currentUser->id)) {
            // Add page specific things
            $renderData['page'] = [
                'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
                'message' => 'You can only edit your own posts!',
            ];

            // Set parse variables
            Template::vars($renderData);

            // Print page contents
            echo Template::render('global/information');
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
        // Permissions
        if (!$currentUser->permission(ForumPerms::DELETE_OWN, Perms::FORUM)) {
            // Add page specific things
            $renderData['page'] = [
                'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
                'message' => 'You are not allowed to delete posts!',
            ];

            // Set parse variables
            Template::vars($renderData);

            // Print page contents
            echo Template::render('global/information');
            exit;
        }

        // Checks
        if ($thread->posts()[$_GET['p']]->poster->id != $currentUser->id && !$forum->permission(ForumPerms::DELETE_ANY, $currentUser->id)) {
            // Add page specific things
            $renderData['page'] = [
                'redirect' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('FORUM_INDEX')),
                'message' => 'You can only delete your own posts!',
            ];

            // Set parse variables
            Template::vars($renderData);

            // Print page contents
            echo Template::render('global/information');
            exit;
        }

        // Submit mode
        if (isset($_POST['timestamp'], $_POST['sessionid'], $_POST['post_id'])) {
            // Post deletion code
            if (isset($_POST['yes'])) {
                // Delete the post
                DBv2::prepare('DELETE FROM `{prefix}posts` WHERE `post_id` = :post')
                    ->execute([
                    'post' => $_POST['post_id'],
                ]);

                // Reload the topic
                $thread = new Forum\Thread($topicId);

                // If there's no more posts left in the topic delete it as well
                if (!$thread->replyCount()) {
                    DBv2::prepare('DELETE FROM `{prefix}topics` WHERE `topic_id` = :thread')
                        ->execute([
                        'thread' => $thread->id,
                    ]);
                }

                // Add page specific things
                $renderData['page'] = [
                    'redirect' => ($thread->replyCount() ? $urls->format('FORUM_THREAD', [$thread->id]) : $urls->format('FORUM_INDEX')),
                    'message' => 'Your post has been deleted!',
                ];

                // Set parse variables
                Template::vars($renderData);

                // Print page contents
                echo Template::render('global/information');
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
        Template::vars($renderData);

        // Print page contents
        echo Template::render('global/confirm');
        exit;
    }

    // Add subject to render data
    if (!isset($posting['subject'])) {
        $posting['subject'] = 'Re: ' . $thread->title;
    }
}

// Check if a post is being made
if (isset($_POST['post'])) {
    // Check if an ID is set
    if (isset($_POST['id'])) {
        // Attempt to create a post object
        $post = new Forum\Post($_POST['id']);
        
        // Check if the post israel
        if ($post->id == $_POST['id']) {
            $post->subject = $_POST['subject'];
            $post->text = $_POST['text'];
            $post->editTime = time();
            $post->editReason = '';
            $post->editUser = $currentUser;
            $post = $post->update();
        } else {
            $post = null;
        }
    } else {
        // Attempt to make the post
        $post = Forum\Post::create($_POST['subject'], $_POST['text'], $currentUser, $topicId, $forumId);
    }

    // Add page specific things
    $renderData['page'] = [
        'redirect' => $post ? $urls->format('FORUM_POST', [$post->id]) . '#p' . $post->id : '',
        'message' => $post ? 'Made the post!' : 'Something is wrong with your post!',
        'success' => $post ? 1 : 0,
    ];

    // Print page contents or if the AJAX request is set only display the render data
    if (isset($_REQUEST['ajax'])) {
        echo $renderData['page']['message'] . '|' .
            $renderData['page']['success'] . '|' .
            $renderData['page']['redirect'];
    } else {
        // Set parse variables
        Template::vars($renderData);

        // Print page contents
        echo Template::render('global/information');
    }
    exit;
}

// Set additional render data
$renderData = array_merge($renderData, [
    'posting' => $posting,
]);

// Set parse variables
Template::vars($renderData);

// Print page contents
echo Template::render('forum/posting');
