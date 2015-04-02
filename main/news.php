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
$renderData['newsPosts'] = Main::getNewsPosts();

// Print page contents
print Main::tplRender('main/news.tpl', $renderData);
