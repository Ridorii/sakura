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
} elseif(isset($_REQUEST['friend-action']) && $_REQUEST['friend-action']) {

    if(!isset($_REQUEST['session']) || $_REQUEST['session'] !== session_id()) {
        print Templates::render('errors/information.tpl', array_merge($renderData, ['page' => ['redirect' => $_SERVER['PHP_SELF'], 'message' => 'Invalid session ID, please try again.', 'title' => 'Information']]));
        exit;
    }

    if((!isset($_REQUEST['add']) && !isset($_REQUEST['remove'])) || !isset($_REQUEST['time'])) {
        print Templates::render('errors/information.tpl', array_merge($renderData, ['page' => ['redirect' => $_SERVER['PHP_SELF'], 'message' => 'One or more required parameter is not set.', 'title' => 'Information']]));
        exit;
    }

    if((isset($_REQUEST['add']) && $_REQUEST['add'] == Session::$userId) || (isset($_REQUEST['remove']) && $_REQUEST['remove'] == Session::$userId)) {
        print Templates::render('errors/information.tpl', array_merge($renderData, ['page' => ['redirect' => $_SERVER['PHP_SELF'], 'message' => 'Can\'t add yourself as a friend.', 'title' => 'Information']]));
        exit;
    }
    
    $add = Users::addFriend($_REQUEST['add']);
print $add[1];
    if($add[0]) {
        $user = Users::getUser(Session::$userId);
        Users::createNotification($_REQUEST['add'], $user['username'] .' added you as a friend!', 'If you aren\'t mutual friends yet click here to add them as well.', 60000, '//'. Configuration::getLocalConfig('urls', 'main') .'/a/'. $user['id'], '//'. Configuration::getLocalConfig('urls', 'main') .'/u/'. $user['id'], '1');
        print Templates::render('errors/information.tpl', array_merge($renderData, ['page' => ['redirect' => $_SERVER['PHP_SELF'], 'message' => 'You are now friends!', 'title' => 'Information']]));
        exit;
    } else {
        print Templates::render('errors/information.tpl', array_merge($renderData, ['page' => ['redirect' => $_SERVER['PHP_SELF'], 'message' => 'Something went wrong.', 'title' => 'Information']]));
        exit;
    }
    exit;

}

// Settings page list
$pages = [
    'home'          => ['General',          'Home'],
    'profile'       => ['General',          'Edit Profile'],
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

    // Notification history
    case 'notifications':
        $renderData['notifs'] = array_reverse(Users::getNotifications(null, 0, false, true));
        break;

}

// Print page contents
print Templates::render('settings/'. $currentPage .'.tpl', $renderData);
