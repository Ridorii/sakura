<?php
/*
 * Sakura News Page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once '/var/www/flashii.net/_sakura/sakura.php';

// Add page specific things
$renderData['page'] = [
    'title' => 'Flashii News'
];
$renderData['newsPosts'] = Main::getNewsPosts((isset($_GET['id']) && is_numeric($_GET['id'])) ? $_GET['id'] : null);

// Print page contents
print Main::tplRender('main/news.tpl', $renderData);
