<?php
/*
 * Sakura User Settings
 */

// Declare Namespace
namespace Sakura;

// If this we're requesting notifications this page won't require templating
if (isset($_REQUEST['request-notifications']) && $_REQUEST['request-notifications']) {
    define('SAKURA_NO_TPL', true);
}

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Notifications
if (isset($_REQUEST['request-notifications']) && $_REQUEST['request-notifications']) {
    // Create the notification container array
    $notifications = array();

    // Check if the user is logged in
    if (Users::checkLogin()
        && isset($_REQUEST['time'])
        && $_REQUEST['time'] > (time() - 1000)
        && isset($_REQUEST['session']) && $_REQUEST['session'] == session_id()) {
        // Get the user's notifications from the past forever but exclude read notifications
        $userNotifs = Users::getNotifications(null, 0, true, true);

        // Add the proper values to the array
        foreach ($userNotifs as $notif) {
            // Add the notification to the display array
            $notifications[$notif['alert_timestamp']] = [

                'read' => $notif['alert_read'],
                'title' => $notif['alert_title'],
                'text' => $notif['alert_text'],
                'link' => $notif['alert_link'],
                'img' => $notif['alert_img'],
                'timeout' => $notif['alert_timeout'],
                'sound' => $notif['alert_sound'],

            ];
        }
    }

    // Set header, convert the array to json, print it and exit
    print json_encode($notifications);
    exit;
} elseif (isset($_REQUEST['comment-action']) && $_REQUEST['comment-action'] && Users::checkLogin()) {
    // Referrer
    $redirect = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('SITE_INDEX'));

    // Continue
    $continue = true;

    // Match session ids for the same reason
    if (!isset($_REQUEST['session']) || $_REQUEST['session'] != session_id()) {
        $renderData['page'] = [

            'redirect' => $redirect,
            'message' => 'Invalid session, please try again.',
            'success' => 0,

        ];

        // Prevent
        $continue = false;
    }

    // Match session ids for the same reason
    if (!isset($_REQUEST['category'])) {
        $renderData['page'] = [

            'redirect' => $redirect,
            'message' => 'No category was set.',
            'success' => 0,

        ];

        // Prevent
        $continue = false;
    }

    // Select the right action
    if ($continue) {
        $comments = new Comments($_REQUEST['category']);

        switch (isset($_REQUEST['mode']) ? $_REQUEST['mode'] : false) {
            case 'vote':
                $comment = $comments->getComment(isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);

                // Check if the comment was actually made by the current user
                if (!$comment) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'The requested comment does not exist.',
                        'success' => 0,

                    ];
                    break;
                }

                // Check if the user can delete comments
                if (!$currentUser->checkPermission('SITE', 'VOTE_COMMENTS')) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'You aren\'t allowed to vote on comments.',
                        'success' => 0,

                    ];
                    break;
                }

                $comments->makeVote(
                    $currentUser->data['user_id'],
                    isset($_REQUEST['id']) ? $_REQUEST['id'] : 0,
                    isset($_REQUEST['state']) && $_REQUEST['state'] ? '1' : '0'
                );

                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => 'Your vote has been cast!',
                    'success' => 1,

                ];
                break;

            case 'delete':
                $comment = $comments->getComment(isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);

                // Check if the comment was actually made by the current user
                if (!$comment) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'The requested comment does not exist.',
                        'success' => 0,

                    ];
                    break;
                }

                // Check if the user can delete comments
                if (!$currentUser->checkPermission('SITE', 'DELETE_COMMENTS')) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'You aren\'t allowed to delete comments.',
                        'success' => 0,

                    ];
                    break;
                }

                // Check if the comment was actually made by the current user
                if ($comment['comment_poster'] !== $currentUser->data['id']) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'You can\'t delete the comments of others.',
                        'success' => 0,

                    ];
                    break;
                }

                $comments->removeComment(isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);

                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => 'The comment has been deleted!',
                    'success' => 1,

                ];
                break;

            case 'comment':
                // Check if the user can delete comments
                if (!$currentUser->checkPermission('SITE', 'CREATE_COMMENTS')) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'You aren\'t allowed to comment.',
                        'success' => 0,

                    ];
                    break;
                }

                // Attempt to make a new comment
                $comment = $comments->makeComment($currentUser->data['user_id'], $_POST['replyto'], $_POST['comment']);

                // Messages
                $messages = [
                    'TOO_SHORT' => 'The comment you\'re trying to make is too short!',
                    'TOO_LONG' => 'The comment you\'re trying to make is too long!',
                    'SUCCESS' => 'Posted!',
                ];

                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => $messages[$comment[1]],
                    'success' => $comment[0],

                ];
                break;

            default:
                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => 'Unknown action.',
                    'success' => 0,

                ];
        }
    }

    // Print page contents or if the AJAX request is set only display the render data
    print isset($_REQUEST['ajax']) ?
    (
        $renderData['page']['message'] . '|' .
        $renderData['page']['success'] . '|' .
        $renderData['page']['redirect']
    ) :
    Templates::render('global/information.tpl', $renderData);
    exit;
} elseif (isset($_REQUEST['friend-action']) && $_REQUEST['friend-action'] && Users::checkLogin()) {
    // Continue
    $continue = true;

    // Referrer
    $redirect = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('SITE_INDEX'));

    // Compare time and session so we know the link isn't forged
    if (!isset($_REQUEST['add']) && !isset($_REQUEST['remove'])) {
        if (!isset($_REQUEST['ajax'])) {
            header('Location: ' . $redirect);
            exit;
        }

        $renderData['page'] = [

            'redirect' => $redirect,
            'message' => 'One of the required operators isn\'t set.',
            'success' => 0,

        ];

        // Prevent
        $continue = false;
    }

    // Compare time and session so we know the link isn't forged
    if ($continue && $_REQUEST[(isset($_REQUEST['add']) ? 'add' : 'remove')] == $currentUser->data['user_id']) {
        $renderData['page'] = [

            'redirect' => $redirect,
            'message' => 'You can\'t be friends with yourself, stop trying to bend reality.',
            'success' => 0,

        ];

        // Prevent
        $continue = false;
    }

    // Compare time and session so we know the link isn't forged
    if (!isset($_REQUEST['time']) || $_REQUEST['time'] < time() - 1000) {
        $renderData['page'] = [

            'redirect' => $redirect,
            'message' => 'Timestamps differ too much, refresh the page and try again.',
            'success' => 0,

        ];

        // Prevent
        $continue = false;
    }

    // Match session ids for the same reason
    if (!isset($_REQUEST['session']) || $_REQUEST['session'] != session_id()) {
        $renderData['page'] = [

            'redirect' => $redirect,
            'message' => 'Invalid session, please try again.',
            'success' => 0,

        ];

        // Prevent
        $continue = false;
    }

    // Continue if nothing fucked up
    if ($continue) {
        // Execute the action
        $action = (isset($_REQUEST['add']) ?
            $currentUser->addFriend($_REQUEST['add']) :
            $currentUser->removeFriend($_REQUEST['remove'], true));

        // Set the messages
        $messages = [

            'USER_NOT_EXIST' => 'The user you tried to add doesn\'t exist.',
            'ALREADY_FRIENDS' => 'You are already friends with this person!',
            'FRIENDS' => 'You are now mutual friends!',
            'NOT_MUTUAL' => 'A friend request has been sent to this person.',
            'ALREADY_REMOVED' => 'You aren\'t friends with this person.',
            'REMOVED' => 'Removed this person from your friends list.',

        ];

        // Notification strings
        $notifStrings = [

            'FRIENDS' => ['%s accepted your friend request!', 'You can now do mutual friend things!'],
            'NOT_MUTUAL' => ['%s added you as a friend!', 'Click here to add them as well.'],
            'REMOVED' => ['%s removed you from their friends.', 'You can no longer do friend things now ;_;'],

        ];

        // Add page specific things
        $renderData['page'] = [

            'redirect' => $redirect,
            'message' => $messages[$action[1]],
            'success' => $action[0],

        ];

        // Create a notification
        if (array_key_exists($action[1], $notifStrings)) {
            // Get the current user's profile data
            $user = new User($currentUser->data['user_id']);

            Users::createNotification(
                $_REQUEST[(isset($_REQUEST['add']) ? 'add' : 'remove')],
                sprintf($notifStrings[$action[1]][0], $user->data['username']),
                $notifStrings[$action[1]][1],
                60000,
                '//' . Configuration::getConfig('url_main') . '/a/' . $user->data['user_id'],
                '//' . Configuration::getConfig('url_main') . '/u/' . $user->data['user_id'],
                '1'
            );
        }
    }

    // Print page contents or if the AJAX request is set only display the render data
    print isset($_REQUEST['ajax']) ?
    (
        $renderData['page']['message'] . '|' .
        $renderData['page']['success'] . '|' .
        $renderData['page']['redirect']
    ) :
    Templates::render('global/information.tpl', $renderData);
    exit;
} elseif (isset($_POST['submit']) && isset($_POST['submit'])) {
    $continue = true;

    // Set redirector
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('SETTINGS_INDEX');

    // Check if the user is logged in
    if (!Users::checkLogin() || !$continue) {
        $renderData['page'] = [

            'redirect' => '/authenticate',
            'message' => 'You must be logged in to edit your settings.',
            'success' => 0,

        ];

        $continue = false;
    }

    // Check session variables
    if (!isset($_REQUEST['timestamp'])
        || $_REQUEST['timestamp'] < time() - 1000
        || !isset($_REQUEST['sessid'])
        || $_REQUEST['sessid'] != session_id()
        || !$continue) {
        $renderData['page'] = [

            'redirect' => $redirect,
            'message' => 'Your session has expired, please refresh the page and try again.',
            'success' => 0,

        ];

        $continue = false;
    }

    // Change settings
    if ($continue) {
        // Switch to the correct mode
        switch ($_POST['mode']) {
            // Avatar & Background
            case 'avatar':
            case 'background':
                // Assign $_POST['mode'] to a $mode variable because I ain't typin that more than once
                $mode = $_POST['mode'];

                // Assign the correct userData key to a variable and correct title
                switch ($mode) {
                    case 'background':
                        $userDataKey = 'profileBackground';
                        $msgTitle = 'Background';
                        $permission = (
                            !empty($currentUser->data['user_data'][$userDataKey])
                            && $currentUser->checkPermission('SITE', 'CHANGE_BACKGROUND')
                        ) || $currentUser->checkPermission('SITE', 'CREATE_BACKGROUND');
                        break;

                    case 'avatar':
                    default:
                        $userDataKey = 'userAvatar';
                        $msgTitle = 'Avatar';
                        $permission = $currentUser->checkPermission('SITE', 'CHANGE_AVATAR');
                }

                // Check if the user has the permissions to go ahead
                if (!$permission) {
                    // Set render data
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'You are not allowed to alter your ' . strtolower($msgTitle) . '.',
                        'success' => 0,

                    ];

                    break;
                }

                // Set path variables
                $filepath = ROOT . Configuration::getConfig('user_uploads') . '/';
                $filename = $filepath . $mode . '_' . $currentUser->data['user_id'];
                $currfile = isset($currentUser->data['user_data'][$userDataKey])
                && !empty($_OLDFILE = $currentUser->data['user_data'][$userDataKey]) ? $_OLDFILE : null;

                // Check if $_FILES is set
                if (!isset($_FILES[$mode]) && empty($_FILES[$mode])) {
                    // Set render data
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'No file was uploaded.',
                        'success' => 0,

                    ];
                    break;
                }

                // Check if the upload went properly
                if ($_FILES[$mode]['error'] !== UPLOAD_ERR_OK && $_FILES[$mode]['error'] !== UPLOAD_ERR_NO_FILE) {
                    // Get the error in text
                    switch ($_FILES[$mode]['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $msg = 'The uploaded file exceeds the maximum filesize!';
                            break;

                        case UPLOAD_ERR_PARTIAL:
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

                        'redirect' => $redirect,
                        'message' => $msg,
                        'success' => 0,

                    ];
                    break;
                }

                // Check if we're not in removal mode
                if ($_FILES[$mode]['error'] != UPLOAD_ERR_NO_FILE) {
                    // Get the meta data
                    $metadata = getimagesize($_FILES[$mode]['tmp_name']);

                    // Check if the image is actually an image
                    if ($metadata == false) {
                        // Set render data
                        $renderData['page'] = [

                            'redirect' => $redirect,
                            'message' => 'Uploaded file is not an image.',
                            'success' => 0,

                        ];

                        break;
                    }

                    // Check if the image is an allowed filetype
                    if ((($metadata[2] !== IMAGETYPE_GIF)
                        && ($metadata[2] !== IMAGETYPE_JPEG)
                        && ($metadata[2] !== IMAGETYPE_PNG))) {
                        // Set render data
                        $renderData['page'] = [

                            'redirect' => $redirect,
                            'message' => 'This filetype is not allowed.',
                            'success' => 0,

                        ];

                        break;
                    }

                    // Check if the image is too large
                    if (($metadata[0] > Configuration::getConfig($mode . '_max_width')
                        || $metadata[1] > Configuration::getConfig($mode . '_max_height'))) {
                        // Set render data
                        $renderData['page'] = [

                            'redirect' => $redirect,
                            'message' => 'The resolution of this picture is too big.',
                            'success' => 0,

                        ];

                        break;
                    }

                    // Check if the image is too small
                    if (($metadata[0] < Configuration::getConfig($mode . '_min_width')
                        || $metadata[1] < Configuration::getConfig($mode . '_min_height'))) {
                        // Set render data
                        $renderData['page'] = [

                            'redirect' => $redirect,
                            'message' => 'The resolution of this picture is too small.',
                            'success' => 0,

                        ];

                        break;
                    }

                    // Check if the file is too large
                    if ((filesize($_FILES[$mode]['tmp_name']) > Configuration::getConfig($mode . '_max_fsize'))) {
                        // Set render data
                        $renderData['page'] = [

                            'redirect' => $redirect,
                            'message' => 'The filesize of this file is too large.',
                            'success' => 0,

                        ];

                        break;
                    }
                }

                // Delete old avatar
                if ($currfile && file_exists($currfile)) {
                    unlink($filepath . $currfile);
                }

                if ($_FILES[$mode]['error'] != UPLOAD_ERR_NO_FILE) {
                    // Append extension to filename
                    $filename .= image_type_to_extension($metadata[2]);

                    if (!move_uploaded_file($_FILES[$mode]['tmp_name'], $filename)) {
                        // Set render data
                        $renderData['page'] = [

                            'redirect' => $redirect,
                            'message' => 'Something went wrong, please try again.',
                            'success' => 0,

                        ];
                    }

                    // Create new array
                    $updated = [$userDataKey => basename($filename)];
                } else {
                    // Remove entry
                    $updated = [$userDataKey => null];
                }

                // Update database
                Users::updateUserDataField($currentUser->data['user_id'], $updated);

                // Set render data
                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => 'Updated your ' . strtolower($msgTitle) . '!',
                    'success' => 1,

                ];
                break;

            // Profile
            case 'profile':
                // Get profile fields and create storage var
                $fields = Users::getProfileFields();
                $store = [];

                // Go over each field
                foreach ($fields as $field) {
                    // Add to the store array
                    if (isset($_POST['profile_' . $field['field_identity']]) && !empty($_POST['profile_' . $field['field_identity']])) {
                        $store[$field['field_identity']] = $_POST['profile_' . $field['field_identity']];
                    }

                    // Check if there's additional values we should keep in mind
                    if (isset($field['field_additional']) && !empty($field['field_additional'])) {
                        // Go over each additional value
                        foreach ($field['field_additional'] as $addKey => $addVal) {
                            // Add to the array
                            $store[$addKey] = (isset($_POST['profile_additional_' . $addKey])
                                || !empty($_POST['profile_additional_' . $addKey])) ?
                            $_POST['profile_additional_' . $addKey] :
                            false;
                        }
                    }
                }

                // Update database
                Users::updateUserDataField($currentUser->data['user_id'], ['profileFields' => $store]);

                // Set render data
                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => 'Your profile has been updated!',
                    'success' => 1,

                ];

                // Birthdays
                if (isset($_POST['birthday_day'])
                    && isset($_POST['birthday_month'])
                    && isset($_POST['birthday_year'])) {
                    // Check if the values aren't fucked with
                    if ($_POST['birthday_day'] < 0
                        || $_POST['birthday_day'] > 31
                        || $_POST['birthday_month'] < 0
                        || $_POST['birthday_month'] > 12
                        || (
                            $_POST['birthday_year'] != 0
                            && $_POST['birthday_year'] < (date("Y") - 100)
                        )
                        || $_POST['birthday_year'] > date("Y")) {
                        $renderData['page']['message'] = 'Your birthdate is invalid.';
                        $renderData['page']['success'] = 0;
                        break;
                    }

                    // Check if the values aren't fucked with
                    if ((
                        $_POST['birthday_day'] < 1
                        && $_POST['birthday_month'] > 0
                    )
                        || (
                            $_POST['birthday_day'] > 0
                            && $_POST['birthday_month'] < 1)
                    ) {
                        $renderData['page']['message'] = 'Only setting a day or month is disallowed.';
                        $renderData['page']['success'] = 0;
                        break;
                    }

                    // Check if the values aren't fucked with
                    if ($_POST['birthday_year'] > 0
                        && (
                            $_POST['birthday_day'] < 1
                            || $_POST['birthday_month'] < 1
                        )
                    ) {
                        $renderData['page']['message'] = 'Only setting a year is disallowed.';
                        $renderData['page']['success'] = 0;
                        break;
                    }

                    $birthdate = implode(
                        '-',
                        [$_POST['birthday_year'], $_POST['birthday_month'], $_POST['birthday_day']]
                    );

                    Database::update('users', [
                        [
                            'user_birthday' => $birthdate,
                        ],
                        [
                            'user_id' => [$currentUser->data['user_id'], '='],
                        ],
                    ]);

                }
                break;

            // Site Options
            case 'options':
                // Get profile fields and create storage var
                $fields = Users::getOptionFields();
                $store = [];

                // Go over each field
                foreach ($fields as $field) {
                    // Make sure the user has sufficient permissions to complete this action
                    if (!$currentUser->checkPermission('SITE', $field['option_permission'])) {
                        $store[$field['option_id']] = false;
                        continue;
                    }

                    $store[$field['option_id']] = isset($_POST['option_' . $field['option_id']])
                    && !empty($_POST['option_' . $field['option_id']]) ?
                    $_POST['option_' . $field['option_id']] :
                    null;
                }

                // Update database
                Users::updateUserDataField($currentUser->data['user_id'], ['userOptions' => $store]);

                // Set render data
                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => 'Changed your options!',
                    'success' => 1,

                ];
                break;

            // Usertitle
            case 'usertitle':
                // Check permissions
                if (!$currentUser->checkPermission('SITE', 'CHANGE_USERTITLE')) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'You aren\'t allowed to change your usertitle.',
                        'success' => 0,

                    ];
                    break;
                }

                // Check length
                if (isset($_POST['usertitle']) ? (strlen($_POST['usertitle']) > 64) : false) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'Your usertitle is too long.',
                        'success' => 0,

                    ];
                    break;
                }

                // Update database
                Database::update(
                    'users',
                    [
                        [
                            'user_title' => (isset($_POST['usertitle']) ? $_POST['usertitle'] : null),
                        ],
                        [
                            'user_id' => [$currentUser->data['user_id'], '='],
                        ],
                    ]
                );

                // Set render data
                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => 'Updated your usertitle!',
                    'success' => 1,

                ];
                break;

            // Username changing
            case 'username':
                // Check permissions
                if (!$currentUser->checkPermission('SITE', 'CHANGE_USERNAME')) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'You aren\'t allowed to change your username.',
                        'success' => 0,

                    ];

                    break;
                }

                // Attempt username change
                $userNameChange = $currentUser->setUsername(isset($_POST['username']) ? $_POST['username'] : '');

                // Messages
                $messages = [
                    'TOO_SHORT' => 'Your new name is too short!',
                    'TOO_LONG' => 'Your new name is too long!',
                    'TOO_RECENT' => 'The username you tried to use is reserved, try again later.',
                    'IN_USE' => 'Someone already has this username!',
                    'SUCCESS' => 'Successfully changed your username!',
                ];

                // Set render data
                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => $messages[$userNameChange[1]],
                    'success' => $userNameChange[0],

                ];
                break;

            // E-mail changing
            case 'email':
                // Check permissions
                if (!$currentUser->checkPermission('SITE', 'CHANGE_EMAIL')) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'You aren\'t allowed to change your e-mail address.',
                        'success' => 0,

                    ];

                    break;
                }

                // Attempt e-mail change
                $emailChange = $currentUser->setEMailAddress(isset($_POST['email']) ? $_POST['email'] : '');

                // Messages
                $messages = [
                    'INVALID' => 'Your e-mail isn\'t considered valid!',
                    'IN_USE' => 'This e-mail address has already been used!',
                    'SUCCESS' => 'Successfully changed your e-mail address!',
                ];

                // Set render data
                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => $messages[$emailChange[1]],
                    'success' => $emailChange[0],

                ];
                break;

            // Password changing
            case 'password':
                // Check permissions
                if (!$currentUser->checkPermission('SITE', 'CHANGE_PASSWORD')) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'You aren\'t allowed to change your password.',
                        'success' => 0,

                    ];

                    break;
                }

                // Attempt password change
                $passChange = $currentUser->setPassword(isset($_POST['oldpassword']) ? $_POST['oldpassword'] : '', isset($_POST['newpassword']) ? $_POST['newpassword'] : '', isset($_POST['newpasswordconfirm']) ? $_POST['newpasswordconfirm'] : '');

                // Messages
                $messages = [
                    'NO_LOGIN' => 'How are you even logged in right now?',
                    'INCORRECT_PASSWORD' => 'The password you provided is incorrect!',
                    'PASS_TOO_SHIT' => 'Your password isn\'t strong enough!',
                    'PASS_NOT_MATCH' => 'Your new passwords don\'t match!',
                    'SUCCESS' => 'Successfully changed your password!',
                ];

                // Set render data
                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => $messages[$passChange[1]],
                    'success' => $passChange[0],

                ];
                break;

            // Deactivation
            case 'deactivate':
                // Check permissions
                if (!$currentUser->checkPermission('SITE', 'DEACTIVATE_ACCOUNT')) {
                    $renderData['page'] = [

                        'redirect' => $redirect,
                        'message' => 'You aren\'t allowed to deactivate your own account.',
                        'success' => 0,

                    ];

                    break;
                }

                // Set render data
                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => 'Nothing happened.',
                    'success' => 1,

                ];
                break;

            // Userpage
            case 'userpage':
                // Base64 encode the userpage
                $userPage = base64_encode($_POST['userpage']);

                // Update database
                Users::updateUserDataField($currentUser->data['user_id'], ['userPage' => $userPage]);

                // Set render data
                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => 'Your userpage has been updated!',
                    'success' => 1,

                ];
                break;

            // Fallback
            default:
                // Set render data
                $renderData['page'] = [

                    'redirect' => $redirect,
                    'message' => 'The requested method does not exist.',
                    'success' => 0,

                ];
                break;

        }

    }

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

if (Users::checkLogin()) {
    // Settings page list
    $pages = [

        'general' => [

            'title' => 'General',

            'modes' => [

                'home' => [

                    'title' => 'Home',
                    'description' => [

                        'Welcome to the Settings Panel.
                        From here you can monitor, view and update your profile and preferences.',

                    ],
                    'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                    'menu' => true,

                ],
                'profile' => [

                    'title' => 'Edit Profile',
                    'description' => [

                        'These are the external account links etc.
                        on your profile, shouldn\'t need any additional explanation for this one.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'ALTER_PROFILE'),
                    'menu' => true,

                ],
                'options' => [

                    'title' => 'Site Options',
                    'description' => [

                        'These are a few personalisation options for the site while you\'re logged in.',

                    ],
                    'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                    'menu' => true,

                ], /*,
            'groups' => [

            'title' => 'Groups',
            'description' => [

            '{{ user.colour }}'

            ],
            'access' => $currentUser->checkPermission('SITE', 'JOIN_GROUPS'),
            'menu' => true

            ]*/

            ],

        ],
        'friends' => [

            'title' => 'Friends',

            'modes' => [

                'listing' => [

                    'title' => 'Listing',
                    'description' => [

                        'Manage your friends.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'MANAGE_FRIENDS'),
                    'menu' => true,

                ],
                'requests' => [

                    'title' => 'Requests',
                    'description' => [

                        'Handle friend requests.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'MANAGE_FRIENDS'),
                    'menu' => true,

                ],

            ],

        ],
        'messages' => [

            'title' => 'Messages',

            'modes' => [

                'inbox' => [

                    'title' => 'Inbox',
                    'description' => [

                        'The list of messages you\'ve received.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'USE_MESSAGES'),
                    'menu' => true,

                ],
                'sent' => [

                    'title' => 'Sent',
                    'description' => [

                        'The list of messages you\'ve sent to other users.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'USE_MESSAGES'),
                    'menu' => true,

                ],
                'compose' => [

                    'title' => 'Compose',
                    'description' => [

                        'Write a new message.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'SEND_MESSAGES'),
                    'menu' => true,

                ],
                'read' => [

                    'title' => 'Read',
                    'description' => [

                        'Read a message.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'USE_MESSAGES'),
                    'menu' => false,

                ],

            ],

        ],
        'notifications' => [

            'title' => 'Notifications',

            'modes' => [

                'history' => [

                    'title' => 'History',
                    'description' => [

                        'The history of notifications that have been sent to you in the last month.',

                    ],
                    'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                    'menu' => true,

                ],

            ],

        ],
        'appearance' => [

            'title' => 'Appearance',

            'modes' => [

                'avatar' => [

                    'title' => 'Avatar',
                    'description' => [

                        'Your avatar which is displayed all over the site and on your profile.',
                        'Maximum image size is {{ avatar.max_width }}x{{ avatar.max_height }},
                        minimum image size is {{ avatar.min_width }}x{{ avatar.min_height }},
                        maximum file size is {{ avatar.max_size_view }}.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CHANGE_AVATAR'),
                    'menu' => true,

                ],
                'background' => [

                    'title' => 'Background',
                    'description' => [

                        'The background that is displayed on your profile.',
                        'Maximum image size is {{ background.max_width }}x{{ background.max_height }},
                        minimum image size is {{ background.min_width }}x{{ background.min_height }},
                        maximum file size is {{ background.max_size_view }}.',

                    ],
                    'access' => (
                        isset($currentUser->data['user_data']['profileBackground'])
                        && $currentUser->checkPermission('SITE', 'CHANGE_BACKGROUND')
                    ) || $currentUser->checkPermission('SITE', 'CREATE_BACKGROUND'),
                    'menu' => true,

                ],
                'userpage' => [

                    'title' => 'Userpage',
                    'description' => [

                        'The custom text that is displayed on your profile.',

                    ],
                    'access' => (
                        isset($currentUser->data['user_data']['userPage'])
                        && $currentUser->checkPermission('SITE', 'CHANGE_USERPAGE')
                    ) || $currentUser->checkPermission('SITE', 'CREATE_USERPAGE'),
                    'menu' => true,

                ],
                'signature' => [

                    'title' => 'Signature',
                    'description' => [

                        'This signature is displayed at the end of all your posts (unless you choose not to show it).',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CHANGE_SIGNATURE'),
                    'menu' => true,

                ],

            ],

        ],
        'account' => [

            'title' => 'Account',

            'modes' => [

                'email' => [

                    'title' => 'E-mail Address',
                    'description' => [

                        'You e-mail address is used for password recovery and stuff like that, we won\'t spam you ;).',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CHANGE_EMAIL'),
                    'menu' => true,

                ],
                'username' => [

                    'title' => 'Username',
                    'description' => [

                        'Probably the biggest part of your identity on a site.',
                        '<b>You can only change this once every 30 days so choose wisely.</b>',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CHANGE_USERNAME'),
                    'menu' => true,

                ],
                'usertitle' => [

                    'title' => 'Usertitle',
                    'description' => [

                        'That little piece of text displayed under your username on your profile.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CHANGE_USERTITLE'),
                    'menu' => true,

                ],
                'password' => [

                    'title' => 'Password',
                    'description' => [

                        'Used to authenticate with the site and certain related services.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CHANGE_PASSWORD'),
                    'menu' => true,

                ],
                'ranks' => [

                    'title' => 'Ranks',
                    'description' => [

                        'Manage what ranks you\'re in and what is set as your main rank.
                        Your main rank is highlighted.
                        You get the permissions of all of the ranks you\'re in combined.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'ALTER_RANKS'),
                    'menu' => true,

                ],

            ],

        ],
        'advanced' => [

            'title' => 'Advanced',

            'modes' => [

                'sessions' => [

                    'title' => 'Sessions',
                    'description' => [

                        'Session keys are a way of identifying yourself with the system without keeping
                        your password in memory.',
                        'If someone finds one of your session keys they could possibly compromise your account,
                        if you see any sessions here that shouldn\'t be here hit the Kill button to kill the
                            selected session.',
                        'If you get logged out after clicking one you\'ve most likely killed your current session,
                        to make it easier to avoid this from happening your current session is highlighted.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'MANAGE_SESSIONS'),
                    'menu' => true,

                ],
                'registrationkeys' => [

                    'title' => 'Registration Keys',
                    'description' => [

                        'Sometimes we activate the registration key system which means that users can only
                        register using your "referer" keys,this means we can keep unwanted people from registering.',
                        'Each user can generate 5 of these keys, bans and deactivates render these keys useless.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CREATE_REGKEYS'),
                    'menu' => true,

                ],
                'deactivate' => [

                    'title' => 'Deactivate Account',
                    'description' => [

                        'You can deactivate your account here if you want to leave :(.',

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'DEACTIVATE_ACCOUNT'),
                    'menu' => true,

                ],

            ],

        ],

    ];

    // Current settings page
    $category = isset($_GET['cat']) ? (
        array_key_exists($_GET['cat'], $pages) ? $_GET['cat'] : false
    ) : array_keys($pages)[0];
    $mode = false;

    // Only continue setting mode if $category is true
    if ($category) {
        $mode = isset($_GET['mode']) && $category ? (
            array_key_exists($_GET['mode'], $pages[$category]['modes']) ? $_GET['mode'] : false
        ) : array_keys($pages[$category]['modes'])[0];
    }

    // Not found
    if (!$category
        || empty($category)
        || !$mode
        || empty($mode)
        || !$pages[$category]['modes'][$mode]['access']) {
        header('HTTP/1.0 404 Not Found');
        print Templates::render('global/notfound.tpl', $renderData);
        exit;
    }

    // Render data
    $renderData['current'] = $category . '.' . $mode;

    // Settings pages
    $renderData['pages'] = $pages;

    // Page data
    $renderData['page'] = [

        'category' => $pages[$category]['title'],
        'mode' => $pages[$category]['modes'][$mode]['title'],
        'currentPage' => isset($_GET['page']) && ($_GET['page'] - 1) >= 0 ? $_GET['page'] - 1 : 0,
        'description' => $pages[$category]['modes'][$mode]['description'],

    ];

    // Section specific
    switch ($category . '.' . $mode) {
        // Profile
        case 'general.profile':
            $renderData['profile'] = [

                'fields' => Users::getProfileFields(),
                'months' => [

                    1 => 'January',
                    2 => 'February',
                    3 => 'March',
                    4 => 'April',
                    5 => 'May',
                    6 => 'June',
                    7 => 'July',
                    8 => 'August',
                    9 => 'September',
                    10 => 'October',
                    11 => 'November',
                    12 => 'December',

                ],

            ];
            break;

        // Options
        case 'general.options':
            $renderData['options'] = [

                'fields' => Users::getOptionFields(),

            ];
            break;

        // Friends
        case 'friends.listing':
            $renderData['friends'] = array_chunk(array_reverse(Users::getFriends(null, true, true)), 12, true);
            break;

        // Pending Friend Requests
        case 'friends.requests':
            $renderData['friends'] = array_chunk(array_reverse(Users::getPendingFriends(null, true)), 12, true);
            break;

        // PM inbox
        case 'messages.inbox':
            $renderData['messages'] = Users::getPrivateMessages();
            break;

        // Notification history
        case 'notifications.history':
            $renderData['alerts'] = array_chunk(array_reverse(Users::getNotifications(null, 0, false, true)), 10, true);
            break;

        // Avatar and background sizes
        case 'appearance.avatar':
        case 'appearance.background':
            $renderData[$mode] = [

                'max_width' => Configuration::getConfig($mode . '_max_width'),
                'max_height' => Configuration::getConfig($mode . '_max_height'),
                'min_width' => Configuration::getConfig($mode . '_min_width'),
                'min_height' => Configuration::getConfig($mode . '_min_height'),
                'max_size' => Configuration::getConfig($mode . '_max_fsize'),
                'max_size_view' => Main::getByteSymbol(Configuration::getConfig($mode . '_max_fsize')),

            ];
            break;

        // Profile
        case 'appearance.userpage':
            $renderData['userPage'] = isset($currentUser->data['user_data']['userPage']) ? base64_decode($currentUser->data['user_data']['userPage']) : '';
            break;

        // Username changing
        case 'account.username':
            $renderData['difference'] = $currentUser->getUsernameHistory() ? Main::timeElapsed($currentUser->getUsernameHistory()[0]['change_time']) : 0;
            break;
    }

    // Print page contents
    print Templates::render('main/settings.tpl', $renderData);
} else {
    // If not allowed print the restricted page
    print Templates::render('global/restricted.tpl', $renderData);
}
