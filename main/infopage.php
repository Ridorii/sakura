<?php
/*
 * Sakura Info Page Handler
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once '/var/www/flashii.net/_sakura/sakura.php';

// Set default variables
$renderData['page'] = [
    'title'     => 'Info pages',
    'content'   => Main::mdParse("# Unable to load the requested info page.\r\n\r\nCheck the URL and try again.")
];

// Get info page data from the database
if($ipData = Main::loadInfoPage(isset($_GET['r']) ? strtolower($_GET['r']) : '')) {

    // Assign new proper variable
    $renderData['page'] = [
        'title'     => $ipData['title'],
        'content'   => Main::mdParse($ipData['content'])
    ];

}

// Print page contents
print Main::$_TPL->render('main/infopage.tpl', $renderData);
