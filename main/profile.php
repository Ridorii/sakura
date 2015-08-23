<?php
/*
 * Sakura User Profiles
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';


// Get the user's context
$profile = new User(isset($_GET['u']) ? $_GET['u'] : 0);

// Assign the object to a renderData variable
$renderData['profile'] = $profile;

// Set proper page title
$renderData['page']['title'] = (

    $profile->checkPermission('SITE', 'DEACTIVATED') || $profile->data['password_algo'] == 'nologin' || ($profile->checkPermission('SITE', 'RESTRICTED') && ($profile->data['id'] != $currentUser->data['id'] && !$currentUser->checkPermission('MANAGE', 'USE_MANAGE')))
    ? 'User not found!'
    : 'Profile of '. $profile->data['username']

);

// Print page contents
print Templates::render('main/profile.tpl', $renderData);
