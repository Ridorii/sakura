<?php
/*
 * Flashii.net Main Index
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Set 404 header
header('HTTP/1.0 404 Not Found');

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('global/notfound');
