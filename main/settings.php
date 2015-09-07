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
    $redirect = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('SITE_INDEX'));

    // Compare time and session so we know the link isn't forged
    if(!isset($_REQUEST['add']) && !isset($_REQUEST['remove'])) {

        if(!isset($_REQUEST['ajax'])) {

            header('Location: '. $redirect);
            exit;

        }

        $renderData['page'] = [

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

            'redirect'  => $redirect,
            'message'   => $messages[$action[1]],
            'success'   => $action[0]

        ];

        // Create a notification
        if(array_key_exists($action[1], $notifStrings)) {

            // Get the current user's profile data
            $user = new User(Session::$userId);

            Users::createNotification(
                $_REQUEST[(isset($_REQUEST['add']) ? 'add' : 'remove')],
                sprintf($notifStrings[$action[1]][0], $user->data['username']),
                $notifStrings[$action[1]][1],
                60000,
                '//'. Configuration::getConfig('url_main') .'/a/'. $user->data['id'],
                '//'. Configuration::getConfig('url_main') .'/u/'. $user->data['id'],
                '1'
            );

        }

    }

    // Print page contents or if the AJAX request is set only display the render data
    print   isset($_REQUEST['ajax']) ?
            (
                $renderData['page']['message'] .'|'.
                $renderData['page']['success'] .'|'.
                $renderData['page']['redirect']
            ) :
            Templates::render('global/information.tpl', $renderData);
    exit;

} elseif(isset($_POST['submit']) && isset($_POST['submit'])) {

    $continue = true;

    // Set redirector
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('SETTINGS_INDEX');

    // Check if the user is logged in
    if(!Users::checkLogin() || !$continue) {

        $renderData['page'] = [

            'redirect'  => '/authenticate',
            'message'   => 'You must be logged in to edit your settings.',
            'success'   => 0

        ];

        break;

    }

    // Check session variables
   if(!isset($_REQUEST['timestamp']) || $_REQUEST['timestamp'] < time() - 1000 || !isset($_REQUEST['sessid']) || $_REQUEST['sessid'] != session_id() || !$continue) {

        $renderData['page'] = [

            'redirect'  => $redirect,
            'message'   => 'Your session has expired, please refresh the page and try again.',
            'success'   => 0

        ];

        break;

    }

    // Change settings
    if($continue) {

        // Switch to the correct mode
        switch($_POST['mode']) {

            // Avatar & Background
            case 'avatar':
            case 'background':

                // Assign $_POST['mode'] to a $mode variable because I ain't typin that more than once
                $mode = $_POST['mode'];

                // Assign the correct userData key to a variable and correct title
                switch($mode) {

                    case 'background':
                        $userDataKey    = 'profileBackground';
                        $msgTitle       = 'Background';
                        $permission     = (!empty($currentUser->data['userData'][$userDataKey]) && $currentUser->checkPermission('SITE', 'CHANGE_BACKGROUND')) || $currentUser->checkPermission('SITE', 'CREATE_BACKGROUND');
                        break;

                    case 'avatar':
                    default:
                        $userDataKey    = 'userAvatar';
                        $msgTitle       = 'Avatar';
                        $permission     = $currentUser->checkPermission('SITE', 'CHANGE_AVATAR');

                }

                // Check if the user has the permissions to go ahead
                if(!$permission) {

                    // Set render data
                    $renderData['page'] = [

                        'redirect'  => $redirect,
                        'message'   => 'You are not allowed to alter your '. strtolower($msgTitle) .'.',
                        'success'   => 0

                    ];

                    break;

                }

                // Set path variables
                $filepath = ROOT . Configuration::getConfig('user_uploads') .'/';
                $filename = $filepath . $mode .'_'. Session::$userId;
                $currfile = isset($currentUser->data['userData'][$userDataKey]) && !empty($_OLDFILE = $currentUser->data['userData'][$userDataKey]) ? $_OLDFILE : null;

                // Check if $_FILES is set
                if(!isset($_FILES[$mode]) && empty($_FILES[$mode])) {

                    // Set render data
                    $renderData['page'] = [

                        'redirect'  => $redirect,
                        'message'   => 'No file was uploaded.',
                        'success'   => 0

                    ];

                    break;

                }

                // Check if the upload went properly
                if($_FILES[$mode]['error'] !== UPLOAD_ERR_OK && $_FILES[$mode]['error'] !== UPLOAD_ERR_NO_FILE) {

                    // Get the error in text
                    switch($_FILES[$mode]['error']) {

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

                        'redirect'  => $redirect,
                        'message'   => $msg,
                        'success'   => 0

                    ];

                    break;

                }

                // Check if we're not in removal mode
                if($_FILES[$mode]['error'] != UPLOAD_ERR_NO_FILE) {

                    // Get the meta data
                    $metadata = getimagesize($_FILES[$mode]['tmp_name']);

                    // Check if the image is actually an image
                    if($metadata == false) {

                        // Set render data
                        $renderData['page'] = [

                            'redirect'  => $redirect,
                            'message'   => 'Uploaded file is not an image.',
                            'success'   => 0

                        ];

                        break;

                    }

                    // Check if the image is an allowed filetype
                    if((($metadata[2] !== IMAGETYPE_GIF) && ($metadata[2] !== IMAGETYPE_JPEG) && ($metadata[2] !== IMAGETYPE_PNG))) {

                        // Set render data
                        $renderData['page'] = [

                            'redirect'  => $redirect,
                            'message'   => 'This filetype is not allowed.',
                            'success'   => 0

                        ];

                        break;

                    }

                    // Check if the image is too large
                    if(($metadata[0] > Configuration::getConfig($mode .'_max_width') || $metadata[1] > Configuration::getConfig($mode .'_max_height'))) {

                        // Set render data
                        $renderData['page'] = [

                            'redirect'  => $redirect,
                            'message'   => 'The resolution of this picture is too big.',
                            'success'   => 0

                        ];

                        break;

                    }

                    // Check if the image is too small
                    if(($metadata[0] < Configuration::getConfig($mode .'_min_width') || $metadata[1] < Configuration::getConfig($mode .'_min_height'))) {

                        // Set render data
                        $renderData['page'] = [

                            'redirect'  => $redirect,
                            'message'   => 'The resolution of this picture is too small.',
                            'success'   => 0

                        ];

                        break;

                    }

                    // Check if the file is too large
                    if((filesize($_FILES[$mode]['tmp_name']) > Configuration::getConfig($mode .'_max_fsize'))) {

                        // Set render data
                        $renderData['page'] = [

                            'redirect'  => $redirect,
                            'message'   => 'The filesize of this file is too large.',
                            'success'   => 0

                        ];

                        break;

                    }

                }

                // Delete old avatar
                if($currfile && file_exists($currfile)) {

                    unlink($filepath . $currfile);

                }

                if($_FILES[$mode]['error'] != UPLOAD_ERR_NO_FILE) {

                    // Append extension to filename
                    $filename .= image_type_to_extension($metadata[2]);

                    if(!move_uploaded_file($_FILES[$mode]['tmp_name'], $filename)) {

                        // Set render data
                        $renderData['page'] = [

                            'redirect'  => $redirect,
                            'message'   => 'Something went wrong, please try again.',
                            'success'   => 0

                        ];

                    }

                    // Create new array
                    $updated = [$userDataKey => basename($filename)];

                } else {

                    // Remove entry
                    $updated = [$userDataKey => null];

                }

                // Update database
                Users::updateUserDataField(Session::$userId, $updated);

                // Set render data
                $renderData['page'] = [

                    'redirect'  => $redirect,
                    'message'   => 'Updated your '. strtolower($msgTitle) .'!',
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
                            if(!isset($_POST['profile_additional_'. $addKey]) || empty($_POST['profile_additional_'. $addKey])) {

                                continue;

                            }

                            // Add to the array
                            $store[$addKey] = $_POST['profile_additional_'. $addKey];

                        }

                    }

                }

                // Update database
                Users::updateUserDataField(Session::$userId, ['profileFields' => $store]);

                // Set render data
                $renderData['page'] = [

                    'redirect'  => $redirect,
                    'message'   => 'Your profile has been updated!',
                    'success'   => 1

                ];

                break;

            // Profile
            case 'options':

                // Get profile fields and create storage var
                $fields = Users::getOptionFields();
                $store  = [];

                // Go over each field
                foreach($fields as $field) {

                    // Make sure the user has sufficient permissions to complete this action
                    if(!$currentUser->checkPermission('SITE', $field['require_perm'])) {

                        $store[$field['id']] = false;
                        continue;

                    }

                    $store[$field['id']] = isset($_POST['option_'. $field['id']]) && !empty($_POST['option_'. $field['id']]) ? $_POST['option_'. $field['id']] : null;

                }

                // Update database
                Users::updateUserDataField(Session::$userId, ['userOptions' => $store]);

                // Set render data
                $renderData['page'] = [

                    'redirect'  => $redirect,
                    'message'   => 'Changed your options!',
                    'success'   => 1

                ];

                break;

            // Userpage
            /*case 'userpage':

                // Base64 encode the userpage
                $userPage = base64_encode($_POST['userpage']);

                // Update database
                Users::updateUserDataField(Session::$userId, ['userPage' => [$userPage, 0]]);

                // Set render data
                $renderData['page'] = [

                    'redirect'  => $redirect,
                    'message'   => 'Your userpage has been updated!',
                    'success'   => 1

                ];

                break;*/

            // Fallback
            default:

                // Set render data
                $renderData['page'] = [

                    'redirect'  => $redirect,
                    'message'   => 'The requested method does not exist.',
                    'success'   => 0

                ];

                break;

        }

    }

    // Print page contents or if the AJAX request is set only display the render data
    print   isset($_REQUEST['ajax']) ?
            (
                $renderData['page']['message'] .'|'.
                $renderData['page']['success'] .'|'.
                $renderData['page']['redirect']
            ) :
            Templates::render('global/information.tpl', $renderData);
    exit;

}

if(Users::checkLogin()) {

    // Settings page list
    $pages = [

        'general' => [

            'title' => 'General',

            'modes' => [

                'home' => [

                    'title' => 'Home',
                    'description' => [

                        'Welcome to the Settings Panel. From here you can monitor, view and update your profile and preferences.'

                    ],
                    'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                    'menu' => true

                ],
                'profile' => [

                    'title' => 'Edit Profile',
                    'description' => [

                        'These are the external account links etc. on your profile, shouldn\'t need any additional explanation for this one.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'ALTER_PROFILE'),
                    'menu' => true

                ],
                'options' => [

                    'title' => 'Site Options',
                    'description' => [

                        'These are a few personalisation options for the site while you\'re logged in.'

                    ],
                    'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                    'menu' => true

                ],
                'groups' => [

                    'title' => 'Groups',
                    'description' => [

                        '{{ user.colour }}'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'JOIN_GROUPS'),
                    'menu' => true

                ]

            ]

        ],
        'friends' => [

            'title' => 'Friends',

            'modes' => [

                'listing' => [

                    'title' => 'Listing',
                    'description' => [

                        'Manage your friends.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'MANAGE_FRIENDS'),
                    'menu' => true

                ],
                'requests' => [

                    'title' => 'Requests',
                    'description' => [

                        'Handle friend requests.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'MANAGE_FRIENDS'),
                    'menu' => true

                ]

            ]

        ],
        'messages' => [

            'title' => 'Messages',

            'modes' => [

                'inbox' => [

                    'title' => 'Inbox',
                    'description' => [

                        'The list of messages you\'ve received.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'USE_MESSAGES'),
                    'menu' => true

                ],
                'sent' => [

                    'title' => 'Sent',
                    'description' => [

                        'The list of messages you\'ve sent to other users.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'USE_MESSAGES'),
                    'menu' => true

                ],
                'compose' => [

                    'title' => 'Compose',
                    'description' => [

                        'Write a new message.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'SEND_MESSAGES'),
                    'menu' => true

                ],
                'read' => [

                    'title' => 'Read',
                    'description' => [

                        'Read a message.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'USE_MESSAGES'),
                    'menu' => false

                ]

            ]

        ],
        'notifications' => [

            'title' => 'Notifications',

            'modes' => [

                'history' => [

                    'title' => 'History',
                    'description' => [

                        'The history of notifications that have been sent to you.'

                    ],
                    'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                    'menu' => true

                ]

            ]

        ],
        'appearance' => [

            'title' => 'Appearance',

            'modes' => [

                'avatar' => [

                    'title' => 'Avatar',
                    'description' => [

                        'Your avatar which is displayed all over the site and on your profile.',
                        'Maximum image size is {{ avatar.max_width }}x{{ avatar.max_height }}, minimum image size is {{ avatar.min_width }}x{{ avatar.min_height }}, maximum file size is {{ avatar.max_size_view }}.'


                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CHANGE_AVATAR'),
                    'menu' => true

                ],
                'background' => [

                    'title' => 'Background',
                    'description' => [

                        'The background that is displayed on your profile.',
                        'Maximum image size is {{ background.max_width }}x{{ background.max_height }}, minimum image size is {{ background.min_width }}x{{ background.min_height }}, maximum file size is {{ background.max_size_view }}.'

                    ],
                    'access' => (isset($currentUser->data['userData']['profileBackground']) && $currentUser->checkPermission('SITE', 'CHANGE_BACKGROUND')) || $currentUser->checkPermission('SITE', 'CREATE_BACKGROUND'),
                    'menu' => true

                ],
                'userpage' => [

                    'title' => 'Userpage',
                    'description' => [

                        'The custom text that is displayed on your profile.'

                    ],
                    'access' => (isset($currentUser->data['userData']['userPage']) && $currentUser->checkPermission('SITE', 'CHANGE_USERPAGE')) || $currentUser->checkPermission('SITE', 'CREATE_USERPAGE'),
                    'menu' => true

                ]

            ]

        ],
        'account' => [

            'title' => 'Account',

            'modes' => [

                'email' => [

                    'title' => 'E-mail Address',
                    'description' => [

                        'You e-mail address is used for password recovery and stuff like that, we won\'t spam you ;).'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CHANGE_EMAIL'),
                    'menu' => true

                ],
                'username' => [

                    'title' => 'Username',
                    'description' => [

                        'Probably the biggest part of your identity on a site.',
                        '<b>You can only change this once every 30 days so choose wisely.</b>'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CHANGE_USERNAME'),
                    'menu' => true

                ],
                'usertitle' => [

                    'title' => 'Username',
                    'description' => [

                        'That little piece of text displayed under your username on your profile.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CHANGE_USERTITLE'),
                    'menu' => true

                ],
                'password' => [

                    'title' => 'Password',
                    'description' => [

                        'Used to authenticate with the site and certain related services.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CHANGE_PASSWORD'),
                    'menu' => true

                ],
                'ranks' => [

                    'title' => 'Ranks',
                    'description' => [

                        'Manage what ranks you\'re in and what is set as your main rank. Your main rank is highlighted. You get the permissions of all of the ranks you\'re in combined.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'ALTER_RANKS'),
                    'menu' => true

                ]

            ]

        ],
        'advanced' => [

            'title' => 'Advanced',

            'modes' => [

                'sessions' => [

                    'title' => 'Sessions',
                    'description' => [

                        'Session keys are a way of identifying yourself with the system without keeping your password in memory.',
                        'If someone finds one of your session keys they could possibly compromise your account, if you see any sessions here that shouldn\'t be here hit the Kill button to kill the selected session.',
                        'If you get logged out after clicking one you\'ve most likely killed your current session, to make it easier to avoid this from happening your current session is highlighted.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'MANAGE_SESSIONS'),
                    'menu' => true

                ],
                'registrationkeys' => [

                    'title' => 'Registration Keys',
                    'description' => [

                        'Sometimes we activate the registration key system which means that users can only register using your "referer" keys, this means we can keep unwanted people from registering.',
                        'Each user can generate 5 of these keys, bans and deactivates render these keys useless.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'CREATE_REGKEYS'),
                    'menu' => true

                ],
                'deactivate' => [

                    'title' => 'Deactivate Account',
                    'description' => [

                        'You can deactivate your account here if you want to leave :(.'

                    ],
                    'access' => $currentUser->checkPermission('SITE', 'DEACTIVATE_ACCOUNT'),
                    'menu' => true

                ]

            ]

        ]

    ];

    // Current settings page
    $category   = isset($_GET['cat']) ? (array_key_exists($_GET['cat'], $pages) ? $_GET['cat'] : false) : array_keys($pages)[0];
    $mode       = false;

    // Only continue setting mode if $category is true
    if($category) {

        $mode = isset($_GET['mode']) && $category ? (array_key_exists($_GET['mode'], $pages[$category]['modes']) ? $_GET['mode'] : false) : array_keys($pages[$category]['modes'])[0];

    }

    // Not found
    if(!$category || empty($category) || !$mode || empty($mode) || !$pages[$category]['modes'][$mode]['access']) {

        header('HTTP/1.0 404 Not Found');
        print Templates::render('global/notfound.tpl', $renderData);
        exit;

    }

    // Render data
    $renderData['current'] = $category .'.'. $mode;

    // Settings pages
    $renderData['pages'] = $pages;

    // Page data
    $renderData['page'] = [

        'category'      => $pages[$category]['title'],
        'mode'          => $pages[$category]['modes'][$mode]['title'],
        'currentPage'   => isset($_GET['page']) && ($_GET['page'] - 1) >= 0 ? $_GET['page'] - 1 : 0,
        'description'   => $pages[$category]['modes'][$mode]['description']

    ];


    // Section specific
    switch($category .'.'. $mode) {

        // Homepage
        case 'general.home':
            $renderData['settings'] = [
                'friends'       => Users::getFriends(null, true, true, true)
            ];
            break;

        // Profile
        case 'general.profile':
            $renderData['profile'] = [

                'user'      => $currentUser->profileFields(),
                'fields'    => Users::getProfileFields()

            ];
            break;

        // Options
        case 'general.options':
            $renderData['options'] = [

                'user'      => $currentUser->optionFields(),
                'fields'    => Users::getOptionFields()

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
            $renderData['notifs'] = array_chunk(array_reverse(Users::getNotifications(null, 0, false, true)), 10, true);
            break;

        // Avatar and background sizes
        case 'appearance.avatar':
        case 'appearance.background':
            $renderData[$mode] = [

                'max_width'     => Configuration::getConfig($mode .'_max_width'),
                'max_height'    => Configuration::getConfig($mode .'_max_height'),
                'min_width'     => Configuration::getConfig($mode .'_min_width'),
                'min_height'    => Configuration::getConfig($mode .'_min_height'),
                'max_size'      => Configuration::getConfig($mode .'_max_fsize'),
                'max_size_view' => Main::getByteSymbol(Configuration::getConfig($mode .'_max_fsize'))

            ];
            break;

        // Profile
        case 'appearance.userpage':

            break;

    }

    // Print page contents
    print Templates::render('main/settings.tpl', $renderData);

} else {

    // If not allowed print the restricted page
    print Templates::render('global/restricted.tpl', $renderData);

}
