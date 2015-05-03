<?php
/*
 * Sakura Forum Index
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Add page specific things
$renderData['page'] = [
    'title'         => Configuration::getConfig('sitename') .' Forum'
];

// Print page contents
print Templates::render('forum/index.tpl', $renderData);
