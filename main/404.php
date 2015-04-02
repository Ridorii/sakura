<?php
/*
 * Flashii.net Main Index
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once '/var/www/flashii.net/_sakura/sakura.php';

// Print page contents
print Main::tplRender('errors/http404.tpl', $renderData);
