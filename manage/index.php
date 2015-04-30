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

    // Moderation
    'mod' => [

        'desc' => '<span style="color: #0C0;">Moderator</span> actions',

        'pages' => [

            'index' => [

                'title' => 'Index',
                'sub'   => [
                    'front-page' => [

                        'desc' => 'Front Page'

                    ]
                ]

            ],

            'message-board' => [

                'title' => 'Message Board',
                'sub'   => []

            ],

            'reports' => [

                'title' => 'Reports',
                'sub'   => []

            ],

            'banning' => [

                'title' => 'Banning',
                'sub'   => []

            ],

            'warnings' => [

                'title' => 'Warnings',
                'sub'   => []

            ],

            'user-notes' => [

                'title' => 'User notes',
                'sub'   => []

            ],

            'action-logs' => [

                'title' => 'Action Logs',
                'sub'   => []

            ]

        ]

    ],

    // Administrative
    'adm' => [

        'desc' => '<span style="color: #C00;">Administrator</span> actions',

        'pages' => [

            'statistics' => [

                'title' => 'Statistics',
                'sub'   => []

            ],

            'general-settings' => [

                'title' => 'General Settings',
                'sub'   => []

            ],

            'users' => [

                'title' => 'Users',
                'sub'   => []

            ],

            'ranks' => [

                'title' => 'Ranks',
                'sub'   => []

            ],

            'permissions' => [

                'title' => 'Permissions',
                'sub'   => []

            ],

            'customise' => [

                'title' => 'Customise',
                'sub'   => []

            ],

            'system' => [

                'title' => 'System',
                'sub'   => []

            ]

        ]

    ]

];

// Add page specific things
$renderData['page'] = [

    'title' => 'Manage Index',
    'pages' => $managePages,
    'activepage' => ,
    'subs' => ,
    'activesub' => 

];

// Print page contents
print Templates::render('main/index.tpl', $renderData);
