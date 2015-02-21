<?php
/*
 * Flashii Sakura Backend
 * (c)Flashwave/Flashii Media 2013-2015 <http://flash.moe>
 */

// Start output buffering
ob_start();
 
// Define Sakura version
define('SAKURA_VERSION', '20150221');
 
// Error Reporting: 0 for production and -1 for testing 
error_reporting(-1);

// Include Configuration
require_once('config/config.php');

// Include Composer libraries
require_once('vendor/autoload.php');

// Include Flashii components
require_once('components/main.php');
require_once('components/hashing.php');
require_once('components/database.php');

// Set Error handler
set_error_handler(array('Flashii\Flashii', 'ErrorHandler'));

// Initialise Flashii Class
$flashii = new Flashii\Flashii($fiiConf);
