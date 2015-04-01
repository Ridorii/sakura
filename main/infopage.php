<?php
/*
 * Sakura Info Page Handler
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once '/var/www/flashii.net/_sakura/sakura.php';

// Do parsing etc.
$renderData['page'] = [
    'title' => 'Infopage'
];

// Print page contents
print Main::$_TPL->render('main/infopage.tpl', $renderData);
