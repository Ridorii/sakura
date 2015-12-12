<?php
/*
 * Sakura Management
 */

// Declare Namespace
namespace Sakura;

// Define that we are in Management mode
define('SAKURA_MANAGE', true);

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Make sure user has the permissions to view this
if (!$currentUser->checkPermission('MANAGE', 'USE_MANAGE')) {
    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/restricted');
    exit;
}

// Modes
$pages = [
    'general' => [
        'title' => 'General',
        'modes' => [
            'dashboard' => [
                'title' => 'Dashboard',
                'description' => [
                    'Welcome to the Broomcloset! Here\'s a quick overview of the site.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'info' => [
                'title' => 'Info pages',
                'description' => [
                    'Manage and edit the info pages.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
        ],
    ],
    'config' => [
        'title' => 'Configuration',
        'modes' => [
            'general' => [
                'title' => 'General',
                'description' => [
                    'Manages the appearance of the site and most other options that don\'t need their own category.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'uploads' => [
                'title' => 'Uploads',
                'description' => [
                    'Settings regarding uploads like avatars and backgrounds.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'premium' => [
                'title' => 'Premium',
                'description' => [
                    'Alters the way the premium system works.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'user' => [
                'title' => 'User',
                'description' => [
                    'Settings regarding users such as registration.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'mail' => [
                'title' => 'Mail',
                'description' => [
                    'How will Sakura send e-mails.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
        ],
    ],
    'forums' => [
        'title' => 'Forums',
        'modes' => [
            'manage' => [
                'title' => 'Manage',
                'description' => [
                    'Change the forums.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'settings' => [
                'title' => 'Settings',
                'description' => [
                    'Alter settings specific to the forum.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
        ],
    ],
    'comments' => [
        'title' => 'Comments',
        'modes' => [
            'manage' => [
                'title' => 'Manage',
                'description' => [
                    'View all the comment categories.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
        ],
    ],
    'users' => [
        'title' => 'Users',
        'modes' => [
            'manage-users' => [
                'title' => 'Manage users',
                'description' => [
                    'View and change users.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'manage-ranks' => [
                'title' => 'Manage ranks',
                'description' => [
                    'View and change ranks.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'profile-fields' => [
                'title' => 'Profile fields',
                'description' => [
                    'Manage the custom profile fields.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'option-fields' => [
                'title' => 'Option fields',
                'description' => [
                    'Manage the custom option fields.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'bans' => [
                'title' => 'Bans',
                'description' => [
                    'Banning users.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'warnings' => [
                'title' => 'Warnings',
                'description' => [
                    'Warn users.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
        ],
    ],
    'permissions' => [
        'title' => 'Permissions',
        'modes' => [
            'site' => [
                'title' => 'Manage site',
                'description' => [
                    'Alter the global site perms.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'management' => [
                'title' => 'Manage management',
                'description' => [
                    'Alter the management/moderation perms.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'forum' => [
                'title' => 'Manage forums',
                'description' => [
                    'Alter the perms of the forums.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
        ],
    ],
    'logs' => [
        'title' => 'Logs',
        'modes' => [
            'actions' => [
                'title' => 'Actions',
                'description' => [
                    'Viewing the global action logs.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'management' => [
                'title' => 'Management',
                'description' => [
                    'Viewing the management actions taken by staff.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
                'menu' => true,
            ],
            'errors' => [
                'title' => 'Errors',
                'description' => [
                    'Viewing the PHP error logs Sakura was able to log.',
                ],
                'access' => !$currentUser->checkPermission('SITE', 'DEACTIVATED'),
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
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/notfound');
    exit;
}

// Set templates directory
$renderData['templates'] = 'manage';

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

// Add special variables
switch ($category . '.' . $mode) {
    case 'general.dashboard':
        $renderData = array_merge($renderData, [
            'stats' => [
                'postcount' => Database::count('posts')[0],
                'threadcount' => Database::count('topics')[0],
                'commentcount' => Database::count('comments')[0],
                'userscount' => Database::count('users')[0],
                'bancount' => Database::count('bans')[0],
                'uploadcount' => count(glob(ROOT . Config::get('user_uploads') . '/*')) - 1,
            ], 
        ]);
        break;

    case 'logs.errors':
        $errorLog = Database::fetch('error_log', true, null, ['error_id', true]);
        $renderData = array_merge($renderData, ['errors' => $errorLog]);
        break;
}

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('main/settings');
