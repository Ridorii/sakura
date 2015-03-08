<?php
/*
 * Flashii.net Main Index
 */

// Include components
require_once('/var/www/flashii.net/_sakura/sakura.php');

// Add page specific things
$renderData['page'] = [
    'title' => 'Flashii Dev'
];

// Print page contents
print $flashii->_TPL->render('main/index.tpl', $renderData);
