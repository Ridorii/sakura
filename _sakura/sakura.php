<?php
/*
 * Sakura Community Management System
 * (c) 2013-2015 Flashwave <http://flash.moe> & Circlestorm <http://circlestorm.net>
 */

// Declare namespace
namespace Sakura;

// Define Sakura version
define('SAKURA_VERSION',    '20150705');
define('SAKURA_VLABEL',     'Eminence');
define('SAKURA_STABLE',     false);
define('SAKURA_COLOUR',     '#6C3082');

// Define Sakura Path
define('ROOT', str_replace(basename(__DIR__), '', dirname(__FILE__)));

// Error Reporting: 0 for production and -1 for testing
error_reporting(SAKURA_STABLE ? 0 : -1);

// Set internal encoding method
mb_internal_encoding('utf-8');

// Include libraries
require_once ROOT .'_sakura/vendor/autoload.php';
require_once ROOT .'_sakura/components/Main.php';
require_once ROOT .'_sakura/components/Hashing.php';
require_once ROOT .'_sakura/components/Configuration.php';
require_once ROOT .'_sakura/components/Database.php';
require_once ROOT .'_sakura/components/Templates.php';
require_once ROOT .'_sakura/components/Permissions.php';
require_once ROOT .'_sakura/components/Sessions.php';
require_once ROOT .'_sakura/components/Users.php';
require_once ROOT .'_sakura/components/Forum.php';
require_once ROOT .'_sakura/components/Manage.php';
require_once ROOT .'_sakura/components/Whois.php';
require_once ROOT .'_sakura/components/Payments.php';
require_once ROOT .'_sakura/components/SockChat.php';

// Include database extensions
foreach(glob(ROOT .'_sakura/components/database/*.php') as $driver)
    require_once($driver);

// Set Error handler
set_error_handler(array('Sakura\Main', 'errorHandler'));

// Initialise Flashii Class
Main::init(ROOT .'_sakura/config/config.ini');

// Start output buffering
ob_start(Configuration::getConfig('use_gzip') ? 'ob_gzhandler' : null);

// Set base page rendering data
$renderData = [

    'sakura' => [

        'version'           => SAKURA_VERSION,
        'vlabel'            => SAKURA_VLABEL,
        'vcolour'           => SAKURA_COLOUR,
        'stable'            => SAKURA_STABLE,
        'urls'              => Configuration::getLocalConfig('urls'),
        'charset'           => Configuration::getConfig('charset'),
        'currentpage'       => '//'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],
        'recaptcha_public'  => Configuration::getConfig('recaptcha_public'),
        'recaptcha_enable'  => Configuration::getConfig('recaptcha'),
        'resources'         => '//'. Configuration::getLocalConfig('urls')['content'] .'/data/'. strtolower(Templates::$_TPL),
        'disableregister'   => Configuration::getConfig('disable_registration'),
        'locksite'          => Configuration::getConfig('lock_site'),
        'locksitereason'    => Configuration::getConfig('lock_site_reason'),
        'lockauth'          => Configuration::getConfig('lock_authentication'),
        'requireregcodes'   => Configuration::getConfig('require_registration_code'),
        'requireactiveate'  => Configuration::getConfig('require_activation'),
        'sitename'          => Configuration::getConfig('sitename'),
        'sitedesc'          => Configuration::getConfig('sitedesc'),
        'sitetags'          => implode(", ", json_decode(Configuration::getConfig('sitetags'), true)),
        'cookieprefix'      => Configuration::getConfig('cookie_prefix'),
        'cookiedomain'      => Configuration::getConfig('cookie_domain'),
        'cookiepath'        => Configuration::getConfig('cookie_path'),
        'minpwdentropy'     => Configuration::getConfig('min_entropy'),
        'minusernamelength' => Configuration::getConfig('username_min_length'),
        'maxusernamelength' => Configuration::getConfig('username_max_length')

    ],

    'perms' => [

        'canGetPremium' => Permissions::check('SITE',   'OBTAIN_PREMIUM',   Session::$userId, 1),
        'canUseForums'  => Permissions::check('FORUM',  'USE_FORUM',        Session::$userId, 1)

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

];
