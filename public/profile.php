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

// Print page contents
print Templates::render('profile/index.tpl', $renderData);
