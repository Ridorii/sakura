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
        'sakura_version'    => SAKURA_VERSION,
        'configuration'     => $fiiConf,
        'pageTitle'         => 'Flashii_Sakura'
    )
);
