<?php
/*
 * Sakura Info Page Handler
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Set default variables
$renderData['page'] = [
    'title'     => 'Info pages',
    'content'   => Main::mdParse("# Unable to load the requested info page.\r\n\r\nCheck the URL and try again.")
];

// Set page id
$pageId = isset($_GET['p']) ? strtolower($_GET['p']) : '';

// Get info page data from the database
if($ipData = Main::loadInfoPage($pageId)) {

    // Assign new proper variable
    $renderData['page'] = [

        'id'        => $pageId,
        'title'     => $ipData['pagetitle'],
        'content'   => Main::mdParse($ipData['content'])

    ];

}

// Print page contents
print Templates::render('main/infopage.tpl', $renderData);
