<?php
/*
 * Flashii.net Main Index
 */

// Include components 
require_once('/var/www/flashii.net/_sakura/sakura.php');

// Initialise templating engine
$flashii->initTwig();

// Print page contents
print $flashii->twig->render('main/index.tpl',
    array(
        'configuration' => $fiiConf,
        'pageTitle'     => 'Flashii_Sakura'
    )
);
