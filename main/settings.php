<?php
/*
 * Sakura User Settings
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Notifications
if(isset($_REQUEST['request-notifications']) && $_REQUEST['request-notifications']) {

    // Set CORS header
    header('Access-Control-Allow-Origin: *');

    // Create the notification container array
    $notifications = array();

    // Check if the user is logged in
    if(Users::checkLogin() && isset($_REQUEST['time']) && $_REQUEST['time'] > (time() - 1000) && isset($_REQUEST['session']) && $_REQUEST['session'] == session_id()) {

        // Get the user's notifications from the past forever but exclude read notifications
        $userNotifs = Users::getNotifications(null, 0, true, true);

        // Add the proper values to the array
        foreach($userNotifs as $notif) {

            // Add the notification to the display array
            $notifications[$notif['timestamp']] = [
                'read'      => $notif['notif_read'],
                'title'     => $notif['notif_title'],
                'text'      => $notif['notif_text'],
                'link'      => $notif['notif_link'],
                'img'       => $notif['notif_img'],
                'timeout'   => $notif['notif_timeout'],
                'sound'     => $notif['notif_sound']
            ];

        }

    }

    // Set header, convert the array to json, print it and exit
    print json_encode($notifications);
    exit;

// Friends
} elseif(isset($_REQUEST['friend-action']) && $_REQUEST['friend-action'] && Users::checkLogin()) {

    // Continue
    $continue = true;

    // Referrer
    $redirect = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/');

    // Compare time and session so we know the link isn't forged
    if(!isset($_REQUEST['add']) && !isset($_REQUEST['remove'])) {

        if(!isset($_REQUEST['ajax'])) {

            header('Location: /settings/friends');
            exit;

        }

        $renderData['page'] = [
            'title'     => 'Action failed',
            'redirect'  => $redirect,
            'message'   => 'One of the required operators isn\'t set.',
            'success'   => 0
        ];

        // Prevent
        $continue = false;

    }

    // Compare time and session so we know the link isn't forged
    if($continue && $_REQUEST[(isset($_REQUEST['add']) ? 'add' : 'remove')] == Session::$userId) {

        $renderData['page'] = [
            'title'     => 'Action failed',
            'redirect'  => $redirect,
            'message'   => 'You can\'t be friends with yourself, stop trying to bend reality.',
            'success'   => 0
        ];

        // Prevent
        $continue = false;

    }

    // Compare time and session so we know the link isn't forged
    if(!isset($_REQUEST['time']) || $_REQUEST['time'] < time() - 1000) {

        $renderData['page'] = [
            'title'     => 'Action failed',
            'redirect'  => $redirect,
            'message'   => 'Timestamps differ too much, refresh the page and try again.',
            'success'   => 0
        ];

        // Prevent
        $continue = false;

    }

    // Match session ids for the same reason
    if(!isset($_REQUEST['session']) || $_REQUEST['session'] != session_id()) {

        $renderData['page'] = [
            'title'     => 'Action failed',
            'redirect'  => $redirect,
            'message'   => 'Invalid session, please try again.',
            'success'   => 0
        ];

        // Prevent
        $continue = false;

    }

    // Continue if nothing fucked up
    if($continue) {

        // Execute the action
        $action = (isset($_REQUEST['add']) ?  Users::addFriend($_REQUEST['add']) : Users::removeFriend($_REQUEST['remove']));

        // Set the messages
        $messages = [
            'USER_NOT_EXIST'    => 'The user you tried to add doesn\'t exist.',
            'ALREADY_FRIENDS'   => 'You are already friends with this person!',
            'FRIENDS'           => 'You are now mutual friends!',
            'NOT_MUTUAL'        => 'A friend request has been sent to this person.',
            'ALREADY_REMOVED'   => 'You aren\'t friends with this person.',
            'REMOVED'           => 'Removed this person from your friends list.'
        ];

        // Notification strings
        $notifStrings = [
            'FRIENDS'       => ['%s accepted your friend request!',     'You can now do mutual friend things!'],
            'NOT_MUTUAL'    => ['%s added you as a friend!',            'Click here to add them as well.'],
            'REMOVED'       => ['%s removed you from their friends.',   'You can no longer do friend things now ;_;']
        ];

        // Add page specific things
        $renderData['page'] = [
            'title'     => 'Managing Friends',
            'redirect'  => $redirect,
            'message'   => $messages[$action[1]],
            'success'   => $action[0]
        ];

        // Create a notification
        if(array_key_exists($action[1], $notifStrings)) {

            // Get the current user's profile data
            $user = Users::getUser(Session::$userId);

            Users::createNotification(
                $_REQUEST[(isset($_REQUEST['add']) ? 'add' : 'remove')],
                sprintf($notifStrings[$action[1]][0], $user['username']),
                $notifStrings[$action[1]][1],
                60000,
                '//'. Configuration::getLocalConfig('urls', 'main') .'/a/'. $user['id'],
                '//'. Configuration::getLocalConfig('urls', 'main') .'/u/'. $user['id'],
                '1'
            );

        }

    }

    // Print page contents or if the AJAX request is set only display the render data
    print   isset($_REQUEST['ajax']) ?
            (
                $renderData['page']['title']
                . '|'
                . $renderData['page']['message']
                . '|'
                . $renderData['page']['success']
                . '|'
                . $renderData['page']['redirect']
            ) :
            Templates::render('errors/information.tpl', $renderData);
    exit;

}

if(Users::checkLogin()) {

    // Settings page list
    $pages = [
        'home'          => ['General',          'Home'],
        'profile'       => ['General',          'Edit Profile'],
        'friends'       => ['General',          'Friends'],
        'groups'        => ['General',          'Groups'],
        'notifications' => ['Notifications',    'History'],
        'avatar'        => ['Aesthetics',       'Avatar'],
        'background'    => ['Aesthetics',       'Background'],
        'page'          => ['Aesthetics',       'Profile Page'],
        'email'         => ['Account',          'E-mail Address'],
        'username'      => ['Account',          'Username'],
        'usertitle'     => ['Account',          'User Title'],
        'password'      => ['Account',          'Password'],
        'ranks'         => ['Account',          'Ranks'],
        'sessions'      => ['Danger zone',      'Sessions'],
        'regkeys'       => ['Danger zone',      'Registration Keys'],
        'deactivate'    => ['Danger zone',      'Deactivate Account'],
        'notfound'      => ['Settings',         '404']
    ];

    // Current settings page
    $currentPage = isset($_GET['mode']) ? (array_key_exists($_GET['mode'], $pages) ? $_GET['mode'] : 'notfound') : 'home';

    // Render data
    $renderData['page'] = [
        'title' => $pages[$currentPage][0] .' / '. $pages[$currentPage][1]
    ];

    // Section specific
    switch($currentPage) {

        // Profile
        case 'profile':
            $renderData['profile'] = [
                'user' => Users::getUser(Session::$userId),
                'fields' => Database::fetch('profilefields')
            ];
            break;

        // Friends
        case 'friends':
            $renderData['friends'] = Users::getFriends();
            break;

        // Notification history
        case 'notifications':
            $renderData['notifs'] = array_reverse(Users::getNotifications(null, 0, false, true));
            break;

    }

    // Print page contents
    print Templates::render('settings/'. $currentPage .'.tpl', $renderData);

} else {

    $renderData['page']['title'] = 'Restricted!';

    print Templates::render('global/header.tpl', $renderData);
    print Templates::render('elements/restricted.tpl', $renderData);
    print Templates::render('global/footer.tpl', $renderData);

}
