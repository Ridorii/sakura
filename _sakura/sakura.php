<?php
/*
 * Sakura C/PMS
 * (c)Flashwave/Flashii Media 2013-2015 <http://flash.moe>
 */

// Declare namespace
namespace Sakura;

// Start output buffering
ob_start();

// Define Sakura version
define('SAKURA_VERSION', '20150329');

// Define Sakura Path
define('ROOT_DIRECTORY', str_replace('_sakura', '', dirname(__FILE__)));
 
// Error Reporting: 0 for production and -1 for testing 
error_reporting(-1);

// Include Configuration
require_once ROOT_DIRECTORY .'_sakura/config/config.php';

// Include libraries
require_once ROOT_DIRECTORY .'_sakura/vendor/autoload.php';
require_once ROOT_DIRECTORY .'_sakura/components/Main.php';
require_once ROOT_DIRECTORY .'_sakura/components/Hashing.php';
require_once ROOT_DIRECTORY .'_sakura/components/Configuration.php';
require_once ROOT_DIRECTORY .'_sakura/components/Sessions.php';
require_once ROOT_DIRECTORY .'_sakura/components/Users.php';

// Generate path to database driver
$_DBNGNPATH = ROOT_DIRECTORY .'_sakura/components/database/'. Configuration::getLocalConfig('db', 'driver') .'.php';

// Include database driver
if(file_exists($_DBNGNPATH))
    require_once $_DBNGNPATH;
else
    die('<h1>Failed to load database driver.</h1>');

// Set Error handler
set_error_handler(array('Sakura\Main', 'ErrorHandler'));

// Initialise Flashii Class
Main::init($fiiConf);

// Set base page rendering data
$renderData = array(
    'sakura' => [
        'version'   => SAKURA_VERSION,
        'urls'      => Configuration::getLocalConfig('urls'),
        'charset'   => Configuration::getConfig('charset')
    ]
);
