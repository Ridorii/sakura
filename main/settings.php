<?php
/*
 * Sakura User Settings
 */

// Declare Namespace
namespace Sakura;

// If this we're requesting notifications this page won't require templating
if(isset($_REQUEST['request-notifications']) && $_REQUEST['request-notifications']) {

    define('SAKURA_NO_TPL', true);

}

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
        $action = (isset($_REQUEST['add']) ?  Users::addFriend($_REQUEST['add']) : Users::removeFriend($_REQUEST['remove'], true));

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
                '//'. Configuration::getConfig('url_main') .'/a/'. $user['id'],
                '//'. Configuration::getConfig('url_main') .'/u/'. $user['id'],
                '1'
            );

        }

    }

    if(isset($_REQUEST['direct']) && $_REQUEST['direct'] && !isset($_REQUEST['ajax'])) {

        header('Location: '. $renderData['page']['redirect']);
        exit;

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

} elseif(isset($_POST['submit']) && isset($_POST['submit'])) {

    $continue = true;

    // Check if the user is logged in
    if(!Users::checkLogin() || !$continue) {

        $renderData['page'] = [
            'title'     => 'Settings',
            'redirect'  => '/authenticate',
            'message'   => 'You must be logged in to edit your settings.',
            'success'   => 0
        ];

        break;

    }

    // Check session variables
   if(!isset($_REQUEST['timestamp']) || $_REQUEST['timestamp'] < time() - 1000 || !isset($_REQUEST['sessid']) || $_REQUEST['sessid'] != session_id() || !$continue) {

        $renderData['page'] = [
            'title'     => 'Session expired',
            'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
            'message'   => 'Your session has expired, please refresh the page and try again.',
            'success'   => 0
        ];

        break;

    }

    // Change settings
    if($continue) {

        // Switch to the correct mode
        switch($_POST['mode']) {

            // Avatar
            case 'avatar':

                // Set path variables
                $filepath = ROOT . Configuration::getConfig('user_uploads') .'/';
                $filename = $filepath .'avatar_'. Session::$userId;
                $currfile = isset(Users::getUser(Session::$userId)['userData']['userAvatar']) && !empty($_AVA = Users::getUser(Session::$userId)['userData']['userAvatar']) ? $_AVA : null;

                // Check if $_FILES is set
                if(!isset($_FILES['avatar']) && empty($_FILES['avatar'])) {

                    // Set render data
                    $renderData['page'] = [

                        'title'     => 'Avatar',
                        'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
                        'message'   => 'No file was uploaded.',
                        'success'   => 0

                    ];

                    break;
                    
                }

                // Check if the upload went properly
                if($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {

                    // Get the error in text
                    switch($_FILES['avatar']['error']) {

                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $msg = 'The uploaded file exceeds the maximum filesize!';
                            break;

                        case UPLOAD_ERR_PARTIAL:
                        case UPLOAD_ERR_NO_FILE:
                            $msg = 'The upload was interrupted!';
                            break;

                        case UPLOAD_ERR_NO_TMP_DIR:
                        case UPLOAD_ERR_CANT_WRITE:
                            $msg = 'Unable to save file to temporary location, contact the administrator!';
                            break;

                        case UPLOAD_ERR_EXTENSION:
                        default:
                            $msg = 'An unknown exception occurred!';
                            break;

                    }

                    // Set render data
                    $renderData['page'] = [

                        'title'     => 'Avatar',
                        'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
                        'message'   => $msg,
                        'success'   => 0

                    ];

                    break;

                }

                // Get the meta data
                $metadata = getimagesize($_FILES['avatar']['tmp_name']);

                // Check if the image is actually an image
                if($metadata == false) {

                    // Set render data
                    $renderData['page'] = [

                        'title'     => 'Avatar',
                        'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
                        'message'   => 'Uploaded file is not an image.',
                        'success'   => 0

                    ];

                    break;

                }

                // Check if the image is an allowed filetype
                if((($metadata[2] !== IMAGETYPE_GIF) && ($metadata[2] !== IMAGETYPE_JPEG) && ($metadata[2] !== IMAGETYPE_PNG))) {

                    // Set render data
                    $renderData['page'] = [

                        'title'     => 'Avatar',
                        'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
                        'message'   => 'This filetype is not allowed.',
                        'success'   => 0

                    ];

                    break;

                }

                // Check if the image is too large
                if(($metadata[0] > Configuration::getConfig('avatar_max_width') || $metadata[1] > Configuration::getConfig('avatar_max_height'))) {

                    // Set render data
                    $renderData['page'] = [

                        'title'     => 'Avatar',
                        'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
                        'message'   => 'The resolution of this picture is too big.',
                        'success'   => 0

                    ];

                    break;

                }

                // Check if the image is too small
                if(($metadata[0] < Configuration::getConfig('avatar_min_width') || $metadata[1] < Configuration::getConfig('avatar_min_height'))) {

                    // Set render data
                    $renderData['page'] = [

                        'title'     => 'Avatar',
                        'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
                        'message'   => 'The resolution of this picture is too small.',
                        'success'   => 0

                    ];

                    break;

                }

                // Check if the file is too large
                if((filesize($_FILES['avatar']['tmp_name']) > Configuration::getConfig('avatar_max_fsize'))) {

                    // Set render data
                    $renderData['page'] = [

                        'title'     => 'Avatar',
                        'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
                        'message'   => 'The filesize of this picture is too large.',
                        'success'   => 0

                    ];

                    break;

                }

                // Delete old avatar
                if($currfile && file_exists($currfile)) {

                    unlink($filepath . $currfile);

                }

                // Append extension to filename
                $filename .= image_type_to_extension($metadata[2]);

                if(!move_uploaded_file($_FILES['avatar']['tmp_name'], $filename)) {


                    // Set render data
                    $renderData['page'] = [

                        'title'     => 'Avatar',
                        'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
                        'message'   => 'Something went wrong, please try again.',
                        'success'   => 0

                    ];

                }

                // Update database
                Users::updateUserDataField(Session::$userId, ['userAvatar' => basename($filename)]);

                // Set render data
                $renderData['page'] = [

                    'title'     => 'Avatar',
                    'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
                    'message'   => 'Updated your avatar!',
                    'success'   => 1

                ];

                break;

            // Profile
            case 'profile':

                // Get profile fields and create storage var
                $fields = Users::getProfileFields();
                $store  = [];

                // Go over each field
                foreach($fields as $field) {

                    // Add to the store array
                    if(isset($_POST['profile_'. $field['ident']]) && !empty($_POST['profile_'. $field['ident']])) {

                        $store[$field['ident']] = $_POST['profile_'. $field['ident']];

                    }

                    // Check if there's additional values we should keep in mind
                    if(isset($field['additional']) && !empty($field['additional'])) {

                        // Decode the json
                        $field['additional'] = json_decode($field['additional'], true);

                        // Go over each additional value
                        foreach($field['additional'] as $addKey => $addVal) {

                            // Skip if the value is empty
                            if(!isset($_POST['profile_additional_'. $addKey]) || empty($_POST['profile_additional_'. $addKey]))
                                continue;

                            // Add to the array
                            $store[$addKey] = $_POST['profile_additional_'. $addKey];

                        }

                    }

                }

                // Update database
                Users::updateUserDataField(Session::$userId, ['profileFields' => $store]);

                // Set render data
                $renderData['page'] = [

                    'title'     => 'Profile update',
                    'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
                    'message'   => 'Your profile has been updated!',
                    'success'   => 1

                ];

                break;

            // Fallback
            default:

                // Set render data
                $renderData['page'] = [

                    'title'     => 'Unknown action',
                    'redirect'  => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/settings',
                    'message'   => 'The requested method does not exist.',
                    'success'   => 0

                ];

                break;

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
        'home'              => ['General',          'Home'],
        'profile'           => ['General',          'Edit Profile'],
        'groups'            => ['General',          'Groups'],
        'friendlisting'     => ['Friends',          'List'],
        'friendrequests'    => ['Friends',          'Requests'],
        'notifications'     => ['Notifications',    'History'],
        'avatar'            => ['Aesthetics',       'Avatar'],
        'background'        => ['Aesthetics',       'Background'],
        'userpage'          => ['Aesthetics',       'Userpage'],
        'email'             => ['Account',          'E-mail Address'],
        'username'          => ['Account',          'Username'],
        'usertitle'         => ['Account',          'User Title'],
        'password'          => ['Account',          'Password'],
        'ranks'             => ['Account',          'Ranks'],
        'sessions'          => ['Danger zone',      'Sessions'],
        'regkeys'           => ['Danger zone',      'Registration Keys'],
        'deactivate'        => ['Danger zone',      'Deactivate Account'],
        'notfound'          => ['Settings',         '404']
    ];

    // Current settings page
    $currentPage = isset($_GET['mode']) ? (array_key_exists($_GET['mode'], $pages) ? $_GET['mode'] : 'notfound') : 'home';

    // Render data
    $renderData['page'] = [
        'title'         => $pages[$currentPage][0] .' / '. $pages[$currentPage][1],
        'currentPage'   => isset($_GET['page']) && ($_GET['page'] - 1) >= 0 ? $_GET['page'] - 1 : 0
    ];

    // Section specific
    switch($currentPage) {

        // Homepage
        case 'home':
            $renderData['settings'] = [
                'friends'       => Users::getFriends(null, true, true, true),
                'forum_stats'   => Forum::getUserStats(Session::$userId)
            ];
            break;

        // Avatar sizes
        case 'avatar':
            $renderData['avatar'] = [
                'max_width'     => Configuration::getConfig('avatar_max_width'),
                'max_height'    => Configuration::getConfig('avatar_max_height'),
                'min_width'     => Configuration::getConfig('avatar_min_width'),
                'min_height'    => Configuration::getConfig('avatar_min_height'),
                'max_size'      => Configuration::getConfig('avatar_max_fsize'),
                'max_size_view' => Main::getByteSymbol(Configuration::getConfig('avatar_max_fsize'))
            ];
            break;

        // Profile
        case 'profile':
            $renderData['profile'] = [
                'user'      => Users::getUserProfileFields(Session::$userId),
                'fields'    => Users::getProfileFields()
            ];
            break;

        // Friends
        case 'friendlisting':
            $renderData['friends'] = array_chunk(array_reverse(Users::getFriends(null, true, true)), 12, true);
            break;

        // Pending Friend Requests
        case 'friendrequests':
            $renderData['friends'] = array_chunk(array_reverse(Users::getPendingFriends(null, true)), 12, true);
            break;

        // Notification history
        case 'notifications':
            $renderData['notifs'] = array_chunk(array_reverse(Users::getNotifications(null, 0, false, true)), 10, true);
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
