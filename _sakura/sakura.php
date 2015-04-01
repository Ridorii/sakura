<?php
/*
 * Sakura C/PMS
 * (c)Flashwave/Flashii.net 2013-2015 <http://flash.moe>
 */

// Declare namespace
namespace Sakura;

// Define Sakura version
define('SAKURA_VERSION', '20150330');

// Define Sakura Path
define('ROOT', str_replace('_sakura', '', dirname(__FILE__)));

// Error Reporting: 0 for production and -1 for testing
error_reporting(-1);
ini_set('log_errors', 1);
ini_set('error_log', ROOT .'errors.log');

// Start output buffering with gzip and no gzip fallback
if(!ob_start('ob_gzhandler'))
    ob_start();

// Include Configuration
require_once ROOT .'_sakura/config/config.php';

// Include libraries
require_once ROOT .'_sakura/vendor/autoload.php';
require_once ROOT .'_sakura/components/Main.php';
require_once ROOT .'_sakura/components/Hashing.php';
require_once ROOT .'_sakura/components/Configuration.php';
require_once ROOT .'_sakura/components/Templates.php';
require_once ROOT .'_sakura/components/Sessions.php';
require_once ROOT .'_sakura/components/Users.php';

// Generate path to database driver
$_DBNGNPATH = ROOT .'_sakura/components/database/'. $sakuraConf['db']['driver'] .'.php';

// Include database driver
if(file_exists($_DBNGNPATH))
    require_once $_DBNGNPATH;
else
    die('<h1>Failed to load database driver.</h1>');

// Set Error handler
set_error_handler(array('Sakura\Main', 'ErrorHandler'));

// Initialise Flashii Class
Main::init($sakuraConf);

// Set base page rendering data
$renderData = array(
    'sakura' => [
        'version'       => SAKURA_VERSION,
        'urls'          => Configuration::getLocalConfig('urls'),
        'charset'       => Configuration::getConfig('charset'),
        'currentpage'   => $_SERVER['PHP_SELF']
    ],
    'php' => [
        'sessionid' => \session_id(),
        'time'      => \time()
    ],
    'user' => [
        'loggedin' => Users::loggedIn()
    ]
);
