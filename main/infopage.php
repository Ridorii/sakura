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
    'title'     => 'Info pages',
    'content'   => 'Unable to load the requested info page.'
];

// Print page contents
print Main::$_TPL->render('main/infopage.tpl', $renderData);
