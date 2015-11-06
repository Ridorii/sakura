<?php
/*
 * Sakura Management
 */

// Declare Namespace
namespace Sakura;

// Define that we are in Management mode
define('SAKURA_MANAGE', true);

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Make sure user has the permissions to view this
if (!Permissions::check('MANAGE', 'USE_MANAGE', $currentUser->data['user_id'], 1)) {
    header('Location: /');
    exit;
}

// Modes
$modes = [
    'dashboard' => [
        'index',
    ],
    'configuration' => [
        'general',
        'security',
        'authentication',
        'appearance',
        'performance',
    ],
    'error' => [
        'index',
    ],
];

// Select mode
$category = isset($_GET['cat'])
? (
    array_key_exists($_GET['cat'], $modes) ?
    $_GET['cat'] :
    'error'
)
: key($modes);
$mode = isset($_GET['mode'])
? (
    in_array($_GET['mode'], $modes[$category]) ?
    $_GET['mode'] :
    'error'
)
: $modes[$category][0];

// Override category if mode is error
if ($mode == 'error') {
    $category = 'error';
    $mode = $modes[$category][0];
}

// Set page data
$renderData = array_merge($renderData, [
    'manage' => [
        'category' => $category,
        'mode' => $mode,
    ],
]);

// Add special variables
switch ($category . '.' . $mode) {
    case 'system.index':
        $renderData = array_merge($renderData, [
            'uname' => [
                'osn' => php_uname('s'),
                'osv' => php_uname('v'),
                'host' => php_uname('n'),
                'arch' => php_uname('m'),
            ],
        ]);
        break;
    case 'config.index':
        break;
}

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('pages/' . $mode . '.' . $category . '.tpl');
