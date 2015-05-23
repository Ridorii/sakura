<?php
/*
 * Sakura Management
 */

// Declare Namespace
namespace Sakura;

// Define that we are in Management mode
define('SAKURA_MANAGE', true);

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Management pages
$managePages = [

    'index' => [
        'desc' => 'Index',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'reports' => [
        'desc' => 'Reports',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'banning' => [
        'desc' => 'Banning',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'warnings' => [
        'desc' => 'Warnings',
        'subs'  => [
            'front-thing' => [
                'desc' => 'thing'
            ]
        ]
    ],

    'user-notes' => [
        'desc' => 'User notes',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'action-logs' => [
        'desc' => 'Action logs',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'action-logs' => [
        'desc' => 'Action logs',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'statistics' => [
        'desc' => 'Statistics',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'general-settings' => [
        'desc' => 'General Settings',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'users' => [
        'desc' => 'Users',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'ranks' => [
        'desc' => 'Ranks',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'permissions' => [
        'desc' => 'Permissions',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'info-pages' => [
        'desc' => 'Info pages',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ],

    'system' => [
        'desc' => 'System',
        'subs'  => [
            'front-page' => [
                'desc' => 'Front Page'
            ]
        ]
    ]

];

// Add page specific things
$renderData['page'] = [

    'title' => 'Manage Index',
    'pages' => $managePages,
    'activepage' => $_MANAGE_ACTIVE_PAGE = (
        isset($_GET['page']) ?
        (
            array_key_exists($_GET['page'], $managePages) ?
            $_GET['page'] :
            key($managePages)
        ) :
        key($managePages)
    ),
    'subs' => $_MANAGE_SUBS = $managePages[$_MANAGE_ACTIVE_PAGE]['subs'],
    'activesub' => $_MANAGE_ACTIVE_SUB = (
        isset($_GET['sub']) ?
        (
            array_key_exists($_GET['sub'], $_MANAGE_SUBS) ?
            $_GET['sub'] :
            key($_MANAGE_SUBS)
        ) :
        key($_MANAGE_SUBS)
    )

];

// Print page contents
print Templates::render('pages/'. $_MANAGE_ACTIVE_PAGE .'/'. $_MANAGE_ACTIVE_SUB .'.tpl', $renderData);
