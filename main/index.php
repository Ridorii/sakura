<?php
/*
 * Flashii.net Main Index
 */

// Include components 
require_once('/var/www/flashii.net/_sakura/sakura.php');

// Initialise templating engine
$flashii->initTwig();

// Add page specific things
$renderData['page'] = [
    'title' => 'Flashii Dev'
];

// Print page contents
print $flashii->twig->render('main/index.tpl', $renderData);
