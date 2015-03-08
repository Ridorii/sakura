<?php
/*
 * Flashii.net Main Index
 */

// Include components
require_once('/var/www/flashii.net/_sakura/sakura.php');

// Print page contents
print $flashii->_TPL->render('errors/http404.tpl', $renderData);
