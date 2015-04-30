<?php
/*
 * Sakura Management
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/manage.php';

// Print page contents
print Templates::render('login.tpl', $renderData);
