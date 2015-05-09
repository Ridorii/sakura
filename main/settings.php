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

    // Create the notification container array
    $notifications = array();

    // Check if the user is logged in
    if(Users::checkLogin() && isset($_REQUEST['time']) && $_REQUEST['time'] > (time() - 1000) && isset($_REQUEST['session']) && $_REQUEST['session'] == session_id()) {

        // Get the user's notifications from the past forever but exclude read notifications
        $userNotifs = Users::getNotifications(null, 0, true, true);

        // Add the proper values to the array
        foreach($userNotifs as $notif) {

            $notifications[$notif['timestamp']]             = array();
            $notifications[$notif['timestamp']]['read']     = $notif['notif_read'];
            $notifications[$notif['timestamp']]['title']    = $notif['notif_title'];
            $notifications[$notif['timestamp']]['text']     = $notif['notif_text'];
            $notifications[$notif['timestamp']]['link']     = $notif['notif_link'];
            $notifications[$notif['timestamp']]['img']      = $notif['notif_img'];
            $notifications[$notif['timestamp']]['timeout']  = $notif['notif_timeout'];
            $notifications[$notif['timestamp']]['sound']    = $notif['notif_sound'];

        }

    }

    // Set header, convert the array to json, print it and exit
    print json_encode($notifications);
    exit;

}

// Print page contents
print Templates::render('ucp/index.tpl', $renderData);
