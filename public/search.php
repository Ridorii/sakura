<?php
/*
 * Sakura Search Page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Add page specific things
$renderData['page'] = [

    'title' => 'Search',

];

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('main/search.tpl');
