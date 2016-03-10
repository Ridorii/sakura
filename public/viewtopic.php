<?php
/*
 * Sakura Forum Topic Viewer
 */

// Declare Namespace
namespace Sakura;

use Sakura\Perms\Forum as ForumPerms;

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

// Check if the forum exists
if (!$thread) {
    // Set render data
    $renderData['page'] = [
        'message' => 'The topic you tried to access does not exist.',
        'redirect' => Router::route('forums.thread', $thread->id),
    ];

    // Set parse variables
    Template::vars($renderData);

    // Print page contents
    echo Template::render('global/information');
    exit;
}

// Check if the user has access to the forum
if (!$forum->permission(ForumPerms::VIEW, $currentUser->id)) {
    // Set render data
    $renderData['page'] = [
        'message' => 'You do not have access to this thread.',
        'redirect' => Router::route('forums.thread', $thread->id),
    ];

    // Set parse variables
    Template::vars($renderData);

    // Print page contents
    echo Template::render('global/information');
    exit;
}

// Sticky thread
if (isset($_GET['sticky']) && $_GET['sticky'] == session_id() && $forum->permission(ForumPerms::STICKY, $currentUser->id)) {
    // Check the status
    if ($thread->type == 1) {
        $thread->type = 0;
    } else {
        $thread->type = 1;
    }

    // Update the thread
    $thread->update();

    // Set render data
    $renderData['page'] = [
        'message' => 'Changed the thread type.',
        'redirect' => Router::route('forums.thread', $thread->id),
    ];

    // Set parse variables
    Template::vars($renderData);

    // Print page contents
    echo Template::render('global/information');
    exit;
}

// Announce thread
if (isset($_GET['announce']) && $_GET['announce'] == session_id() && $forum->permission(ForumPerms::ANNOUNCEMENT, $currentUser->id)) {
    // Check the status
    if ($thread->type == 2) {
        $thread->type = 0;
    } else {
        $thread->type = 2;
    }

    // Update the thread
    $thread->update();
    // Set render data
    $renderData['page'] = [
        'message' => 'Changed the thread type.',
        'redirect' => Router::route('forums.thread', $thread->id),
    ];

    // Set parse variables
    Template::vars($renderData);

    // Print page contents
    echo Template::render('global/information');
    exit;
}

// Lock thread
if (isset($_GET['lock']) && $_GET['lock'] == session_id() && $forum->permission(ForumPerms::LOCK, $currentUser->id)) {
    // Check the status
    if ($thread->status == 1) {
        $thread->status = 0;
    } else {
        $thread->status = 1;
    }

    // Update the thread
    $thread->update();
    // Set render data
    $renderData['page'] = [
        'message' => 'Changed the thread status.',
        'redirect' => Router::route('forums.thread', $thread->id),
    ];

    // Set parse variables
    Template::vars($renderData);

    // Print page contents
    echo Template::render('global/information');
    exit;
}

// Trash thread
if (isset($_GET['trash']) && $_GET['trash'] == session_id() && $forum->permission(ForumPerms::MOVE, $currentUser->id)) {
    // Check the status
    if ($thread->forum != Config::get('forum_trash_id')) {
        $thread->move(Config::get('forum_trash_id'));

        // Set render data
        $renderData['page'] = [
            'message' => 'Moved thread to the trash.',
            'redirect' => Router::route('forums.thread', $thread->id),
        ];
    } else {
        // Set render data
        $renderData['page'] = [
            'message' => 'This thread is already trashed.',
            'redirect' => Router::route('forums.thread', $thread->id),
        ];
    }

    // Set parse variables
    Template::vars($renderData);

    // Print page contents
    echo Template::render('global/information');
    exit;
}

// Restore thread
if (isset($_GET['restore']) && $_GET['restore'] == session_id() && $forum->permission(ForumPerms::MOVE, $currentUser->id)) {
    // Check the status
    if ($thread->oldForum) {
        // Move thread
        $thread->move($thread->oldForum, false);

        // Set render data
        $renderData['page'] = [
            'message' => 'Restored the thread to its previous location.',
            'redirect' => Router::route('forums.thread', $thread->id),
        ];
    } else {
        // Set render data
        $renderData['page'] = [
            'message' => 'This thread has never been moved.',
            'redirect' => Router::route('forums.thread', $thread->id),
        ];
    }

    // Set parse variables
    Template::vars($renderData);

    // Print page contents
    echo Template::render('global/information');
    exit;
}

// Prune thread
if (isset($_GET['prune']) && $_GET['prune'] == session_id() && $forum->permission(ForumPerms::DELETE_ANY, $currentUser->id)) {
    // Check the status
    if ($thread->forum == Config::get('forum_trash_id')) {
        $thread->delete();

        // Set render data
        $renderData['page'] = [
            'message' => 'The thread has been pruned.',
            'redirect' => Router::route('forums.forum', $thread->forum),
        ];
    } else {
        // Set render data
        $renderData['page'] = [
            'message' => 'You can only prune trashed threads.',
            'redirect' => Router::route('forums.thread', $thread->id),
        ];
    }

    // Set parse variables
    Template::vars($renderData);

    // Print page contents
    echo Template::render('global/information');
    exit;
}

header('Location: ' . Router::route('forums.thread', $thread->id));
