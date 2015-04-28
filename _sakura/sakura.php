<?php
/*
 * Sakura C/PMS
 * (c)Flashwave/Flashii.net 2013-2015 <http://flash.moe>
 */

// Declare namespace
namespace Sakura;

// Define Sakura version
define('SAKURA_VERSION',    '20150428');
define('SAKURA_VLABEL',     'Heliotrope');
define('SAKURA_VTYPE',      'Development');
define('SAKURA_COLOUR',     '#DF73FF');

// Define Sakura Path
define('ROOT', str_replace(basename(__DIR__), '', dirname(__FILE__)));

// Error Reporting: 0 for production and -1 for testing
error_reporting(-1);
ini_set('log_errors', 1);
ini_set('error_log', ROOT .'errors.log');

// Start output buffering
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
require_once ROOT .'_sakura/components/Whois.php';
require_once ROOT .'_sakura/components/SockChat.php';

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
        'version'           => SAKURA_VERSION,
        'urls'              => Configuration::getLocalConfig('urls'),
        'charset'           => Configuration::getConfig('charset'),
        'currentpage'       => '//'. $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'],
        'recaptcha_public'  => Configuration::getConfig('recaptcha_public'),
        'recaptcha_enable'  => Configuration::getConfig('recaptcha'),
        'resources'         => '//'. Configuration::getLocalConfig('urls')['content'] .'/data/'. strtolower(Templates::$_TPL),
        'disableregister'   => Configuration::getConfig('disable_registration'),
        'lockauth'          => Configuration::getConfig('lock_authentication'),
        'requireregcodes'   => Configuration::getConfig('require_registration_code'),
        'requireactiveate'  => Configuration::getConfig('require_activation'),
        'sitename'          => Configuration::getConfig('sitename')
    ],
    'php' => [
        'sessionid' => \session_id(),
        'time'      => \time(),
        'self'      => $_SERVER['PHP_SELF']
    ],
    'user' => [
        'checklogin'    => Users::checkLogin(),
        'session'       => Session::$sessionId,
        'data'          => ($_init_udata = Users::getUser(Session::$userId)),
        'rank'          => ($_init_rdata = Users::getRank($_init_udata['rank_main'])),
        'colour'        => ($_init_udata['name_colour'] == null ? $_init_rdata['colour'] : $_init_udata['name_colour'])
    ]
);
