<?php
/*
 * Sakura User Settings
 */

// Declare Namespace
namespace Sakura;

use Sakura\Perms\Site;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Legacy support!!!!!!!!!
$renderData = [];
$currentUser = ActiveUser::$user;

if (isset($_POST['submit']) && isset($_POST['submit'])) {
    $continue = true;

    // Set redirector
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('SETTINGS_INDEX');

    // Check if the user is logged in
    if (!ActiveUser::$user->id || !$continue) {
        $renderData['page'] = [

            'redirect' => '/login',
            'message' => 'You must be logged in to edit your settings.',
            'success' => 0,

        ];

        $continue = false;
    }

    // Check session variables
    if (!isset($_POST['timestamp'])
        || !isset($_POST['mode'])
        || $_POST['timestamp'] < time() - 1000
        || !isset($_POST['sessid'])
        || $_POST['sessid'] != session_id()
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
            case 'header':
                // Assign $_POST['mode'] to a $mode variable because I ain't typin that more than once
                $mode = $_POST['mode'];

                // Assign the correct column and title to a variable
                switch ($mode) {
                    case 'background':
                        $column = 'user_background';
                        $msgTitle = 'Background';
                        $current = $currentUser->background;
                        $permission = $currentUser->permission(Site::CHANGE_BACKGROUND);
                        break;

                    case 'header':
                        $column = 'user_header';
                        $msgTitle = 'Header';
                        $current = $currentUser->header;
                        $permission = $currentUser->permission(Site::CHANGE_HEADER);
                        break;

                    case 'avatar':
                    default:
                        $column = 'user_avatar';
                        $msgTitle = 'Avatar';
                        $current = $currentUser->avatar;
                        $permission = $currentUser->permission(Site::CHANGE_AVATAR);
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
                $filename = strtolower($msgTitle) . '_' . $currentUser->id;

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
                    if (!$metadata) {
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
                    if (($metadata[0] > Config::get($mode . '_max_width')
                        || $metadata[1] > Config::get($mode . '_max_height'))) {
                        // Set render data
                        $renderData['page'] = [
                            'redirect' => $redirect,
                            'message' => 'The resolution of this picture is too big.',
                            'success' => 0,
                        ];
                        break;
                    }

                    // Check if the image is too small
                    if (($metadata[0] < Config::get($mode . '_min_width')
                        || $metadata[1] < Config::get($mode . '_min_height'))) {
                        // Set render data
                        $renderData['page'] = [
                            'redirect' => $redirect,
                            'message' => 'The resolution of this picture is too small.',
                            'success' => 0,
                        ];
                        break;
                    }

                    // Check if the file is too large
                    if ((filesize($_FILES[$mode]['tmp_name']) > Config::get($mode . '_max_fsize'))) {
                        // Set render data
                        $renderData['page'] = [
                            'redirect' => $redirect,
                            'message' => 'The filesize of this file is too large.',
                            'success' => 0,
                        ];
                        break;
                    }
                }

                // Open the old file and remove it
                $oldFile = new File($current);
                $oldFile->delete();
                unset($oldFile);
                $fileId = 0;

                if ($_FILES[$mode]['error'] != UPLOAD_ERR_NO_FILE) {
                    // Append extension to filename
                    $filename .= image_type_to_extension($metadata[2]);

                    // Store the file
                    $file = File::create(file_get_contents($_FILES[$mode]['tmp_name']), $filename, $currentUser);

                    // Assign the file id to a variable
                    $fileId = $file->id;
                }

                // Update table
                DB::table('users')
                    ->where('user_id', $currentUser->id)
                    ->update([
                        $column => $fileId,
                    ]);

                // Set render data
                $renderData['page'] = [
                    'redirect' => $redirect,
                    'message' => 'Updated your ' . strtolower($msgTitle) . '!',
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
    if (isset($_REQUEST['ajax'])) {
        echo $renderData['page']['message'] . '|' .
            $renderData['page']['success'] . '|' .
            $renderData['page']['redirect'];
    } else {
        // If not allowed print the restricted page
        Template::vars($renderData);

        // Print page contents
        echo Template::render('global/information');
    }
    exit;
}

if (ActiveUser::$user->id) {
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
                    'access' => !$currentUser->permission(Site::DEACTIVATED),
                    'menu' => true,
                ],
                'profile' => [
                    'title' => 'Profile',
                    'description' => [
                        'These are the external account links etc.
                        on your profile, shouldn\'t need any additional explanation for this one.',
                    ],
                    'access' => $currentUser->permission(Site::ALTER_PROFILE),
                    'menu' => true,
                ],
                'options' => [
                    'title' => 'Options',
                    'description' => [
                        'These are a few personalisation options for the site while you\'re logged in.',
                    ],
                    'access' => !$currentUser->permission(Site::DEACTIVATED),
                    'menu' => true,
                ],
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
                    'access' => $currentUser->permission(Site::MANAGE_FRIENDS),
                    'menu' => true,
                ],
                'requests' => [
                    'title' => 'Requests',
                    'description' => [
                        'Handle friend requests.',
                    ],
                    'access' => $currentUser->permission(Site::MANAGE_FRIENDS),
                    'menu' => true,
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
                    'access' => !$currentUser->permission(Site::DEACTIVATED),
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
                    'access' => $currentUser->permission(Site::CHANGE_AVATAR),
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
                    'access' => $currentUser->permission(Site::CHANGE_BACKGROUND),
                    'menu' => true,
                ],
                'header' => [
                    'title' => 'Header',
                    'description' => [
                        'The header that is displayed on your profile.',
                        'Maximum image size is {{ header.max_width }}x{{ header.max_height }},
                        minimum image size is {{ header.min_width }}x{{ header.min_height }},
                        maximum file size is {{ header.max_size_view }}.',
                    ],
                    'access' => $currentUser->permission(Site::CHANGE_HEADER),
                    'menu' => true,
                ],
                'userpage' => [
                    'title' => 'Userpage',
                    'description' => [
                        'The custom text that is displayed on your profile.',
                    ],
                    'access' => (
                        $currentUser->page
                        && $currentUser->permission(Site::CHANGE_USERPAGE)
                    ) || $currentUser->permission(Site::CREATE_USERPAGE),
                    'menu' => true,
                ],
                'signature' => [
                    'title' => 'Signature',
                    'description' => [
                        'This signature is displayed at the end of all your posts (unless you choose not to show it).',
                    ],
                    'access' => $currentUser->permission(Site::CHANGE_SIGNATURE),
                    'menu' => true,
                ],
            ],
        ],
        'account' => [
            'title' => 'Account',
            'modes' => [
                'email' => [
                    'title' => 'E-mail address',
                    'description' => [
                        'You e-mail address is used for password recovery and stuff like that, we won\'t spam you ;).',
                    ],
                    'access' => $currentUser->permission(Site::CHANGE_EMAIL),
                    'menu' => true,
                ],
                'username' => [
                    'title' => 'Username',
                    'description' => [
                        'Probably the biggest part of your identity on a site.',
                        '<b>You can only change this once every 30 days so choose wisely.</b>',
                    ],
                    'access' => $currentUser->permission(Site::CHANGE_USERNAME),
                    'menu' => true,
                ],
                'usertitle' => [
                    'title' => 'Title',
                    'description' => [
                        'That little piece of text displayed under your username on your profile.',
                    ],
                    'access' => $currentUser->permission(Site::CHANGE_USERTITLE),
                    'menu' => true,
                ],
                'password' => [
                    'title' => 'Password',
                    'description' => [
                        'Used to authenticate with the site and certain related services.',
                    ],
                    'access' => $currentUser->permission(Site::CHANGE_PASSWORD),
                    'menu' => true,
                ],
                'ranks' => [
                    'title' => 'Ranks',
                    'description' => [
                        'Manage what ranks you\'re in and what is set as your main rank.
                        Your main rank is highlighted.
                        You get the permissions of all of the ranks you\'re in combined.',
                    ],
                    'access' => $currentUser->permission(Site::ALTER_RANKS),
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
                    'access' => $currentUser->permission(Site::MANAGE_SESSIONS),
                    'menu' => true,
                ],
                'deactivate' => [
                    'title' => 'Deactivate',
                    'description' => [
                        'You can deactivate your account here if you want to leave :(.',
                    ],
                    'access' => $currentUser->permission(Site::DEACTIVATE_ACCOUNT),
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

        // Set parse variables
        Template::vars($renderData);

        // Print page contents
        echo Template::render('global/notfound');
        exit;
    }

    // Set templates directory
    $renderData['templates'] = 'old-settings';

    // Render data
    $renderData['current'] = $category . '.' . $mode;

    // Settings pages
    $renderData['pages'] = $pages;

    // Page data
    $renderData['page'] = [
        'category' => $pages[$category]['title'],
        'mode' => $pages[$category]['modes'][$mode]['title'],
        'description' => $pages[$category]['modes'][$mode]['description'],
    ];

    // Section specific
    switch ($category . '.' . $mode) {
        // Avatar and background sizes
        case 'appearance.avatar':
        case 'appearance.background':
        case 'appearance.header':
            $renderData[$mode] = [
                'max_width' => Config::get($mode . '_max_width'),
                'max_height' => Config::get($mode . '_max_height'),
                'min_width' => Config::get($mode . '_min_width'),
                'min_height' => Config::get($mode . '_min_height'),
                'max_size' => Config::get($mode . '_max_fsize'),
                'max_size_view' => byte_symbol(Config::get($mode . '_max_fsize')),
            ];
            break;
    }

    // Set parse variables
    Template::vars($renderData);

    // Print page contents
    echo Template::render('meta/settings');
} else {
    // If not allowed print the restricted page
    Template::vars($renderData);

    // Print page contents
    echo Template::render('global/restricted');
}
