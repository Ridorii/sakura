<?php
/*
 * Sakura User Profiles
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Get the user's context
$profile = User::construct(isset($_GET['u']) ? $_GET['u'] : 0);

// Views array
$views = [
    'index',
    /*'friends',
    'threads',
    'posts',*/
    'comments',
];

// Assign the object to a renderData variable
$renderData['profile'] = $profile;
$renderData['profileView'] = isset($_GET['view']) && in_array($_GET['view'], $views) ? $_GET['view'] : $views[0];

// If the user id is zero check if there was a namechange
if ($profile->id == 0) {
    // Fetch from username_history
    $check = Database::fetch('username_history', false, ['username_old_clean' => [Utils::cleanString(isset($_GET['u']) ? $_GET['u'] : 0, true, true), '=']]);
    
    // Redirect if so
    if ($check) {
        $renderData['page'] = [
            'message' => 'The user this profile belongs to changed their username, you are being redirected.',
            'redirect' => $urls->format('USER_PROFILE', [$check['user_id']]),
        ];

        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        echo $template->render('global/information');
        exit;
    }
}

// If the user id is zero check if there was a namechange
if (isset($_GET['restrict']) && $_GET['restrict'] == session_id() && $currentUser->permission(Perms\Manage::CAN_RESTRICT_USERS, Perms::MANAGE)) {
    // Check restricted status
    $restricted = $profile->permission(Perms\Site::RESTRICTED);

    if ($restricted) {
        $profile->removeRanks([Config::get('restricted_rank_id')]);
    } else {
        $profile->addRanks([Config::get('restricted_rank_id')]);
        $profile->removeRanks($profile->ranks());
    }

    $renderData['page'] = [
        'message' => 'Toggled the restricted status of the user.',
        'redirect' => $urls->format('USER_PROFILE', [$profile->id]),
    ];

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('global/information');
    exit;
}

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('main/profile');
