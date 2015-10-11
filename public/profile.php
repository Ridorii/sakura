<?php
/*
 * Sakura User Profiles
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Get the user's context
$profile = new User(isset($_GET['u']) ? $_GET['u'] : 0);

// Views array
$views = [
    'index',
    'threads',
    'posts',
    'comments',
];

// Assign the object to a renderData variable
$renderData['profile'] = $profile;
$renderData['profileView'] = isset($_GET['view']) && in_array($_GET['view'], $views) ? $_GET['view'] : $views[0];

// Print page contents
print Templates::render('main/profile.tpl', $renderData);
