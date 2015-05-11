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

}

// Settings page list
$pages = [
    'home'          => ['General',          'Home'],
    'profile'       => ['General',          'Edit Profile'],
    'notifications' => ['Notifications',    'History'],
    'avatar'        => ['Aesthetics',       'Avatar'],
    'background'    => ['Aesthetics',       'Background'],
    'page'          => ['Aesthetics',       'Profile Page'],
    'email'         => ['Account',          'E-Mail Address'],
    'username'      => ['Account',          'Username'],
    'usertitle'     => ['Account',          'User Title'],
    'password'      => ['Account',          'Password'],
    'ranks'         => ['Account',          'Ranks'],
    'sessions'      => ['Danger zone',      'Sessions'],
    'regkeys'       => ['Danger zone',      'Registration Keys'],
    'deactivate'    => ['Danger zone',      'Deactivate Account']
];
$currentPage = isset($_GET['mode']) && array_key_exists($_GET['mode'], $pages) ? $_GET['mode'] : key($pages);

// Print page contents
print Templates::render('settings/'. $currentPage .'.tpl', $renderData);
