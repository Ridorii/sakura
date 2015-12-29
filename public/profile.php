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
    'friends',
    'threads',
    'posts',
    'comments',
];

// Assign the object to a renderData variable
$renderData['profile'] = $profile;
$renderData['profileView'] = isset($_GET['view']) && in_array($_GET['view'], $views) ? $_GET['view'] : $views[0];

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('main/profile');
