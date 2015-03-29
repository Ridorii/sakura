<?php
/*
 * Sakura Main Index
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once '/var/www/flashii.net/_sakura/sakura.php';

// Add page specific things
$renderData['page'] = [
    'title' => 'Flashii Dev'
];

// Print page contents
print Main::$_TPL->render('main/index.tpl', $renderData);
