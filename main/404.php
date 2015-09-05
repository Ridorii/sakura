<?php
/*
 * Flashii.net Main Index
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Set 404 header
header('HTTP/1.0 404 Not Found');

// Print page contents
print Templates::render('global/notfound.tpl', $renderData);
