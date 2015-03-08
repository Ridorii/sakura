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
require_once 'config/config.php';

// Include libraries
require_once 'vendor/autoload.php';
require_once 'components/SockBase.php';
require_once 'components/SockHashing.php';
require_once 'components/SockConfiguration.php';

// Generate path to database driver
$_DBNGNPATH = 'components/database/' . $fiiConf['db']['driver'] . '.php';

// Include database driver
if(file_exists($_DBNGNPATH))
    require_once $_DBNGNPATH;
else
    die('<h1>Failed to load database driver.</h1>');


// Set Error handler
set_error_handler(array('Flashii\Flashii', 'ErrorHandler'));

// Initialise Flashii Class
$flashii = new Flashii\Flashii($fiiConf);
