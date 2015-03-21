<?php
/*
 * Flashii.net Main Index
 */

// Declare Namespace
namespace Flashii;

// Include components
require_once('/var/www/flashii.net/_sakura/sakura.php');

// Print page contents
print Main::$_TPL->render('errors/http404.tpl', $renderData);
