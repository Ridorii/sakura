<?php
/*
 * Sakura FAQ Page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Add page specific things
$renderData['page'] = [

    'title'     => 'Frequently Asked Questions',
    'questions' => Main::getFaqData()

];

// Print page contents
print Templates::render('main/faq.tpl', $renderData);
