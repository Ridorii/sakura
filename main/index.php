<?php
/*
 * Sakura Main Index
 */

// Declare Namespace
namespace Sakura;

// Define Sakura Path
define('ROOT_DIRECTORY', str_replace('main', '', dirname(__FILE__)));

// Include components
require_once ROOT_DIRECTORY .'_sakura/sakura.php';

// Add page specific things
$renderData['page'] = [
    'title' => 'Flashii Dev'
];
$renderData['newsPosts'] = Main::getNewsPosts(3);

// Print page contents
print Main::$_TPL->render('main/index.tpl', $renderData);
