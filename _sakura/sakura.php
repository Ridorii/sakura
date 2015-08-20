<?php
/*
 * Sakura Community Management System
 * (c) 2013-2015 Flashwave <http://flash.moe> & Circlestorm <http://circlestorm.net>
 */

// Declare namespace
namespace Sakura;

// Define Sakura version
define('SAKURA_VERSION',    '20150820');
define('SAKURA_VLABEL',     'Eminence');
define('SAKURA_COLOUR',     '#6C3082');
define('SAKURA_STABLE',     false);

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
require_once ROOT .'_sakura/components/User.php';
require_once ROOT .'_sakura/components/Users.php';
require_once ROOT .'_sakura/components/Forum.php';
require_once ROOT .'_sakura/components/Manage.php';
require_once ROOT .'_sakura/components/Bans.php';
require_once ROOT .'_sakura/components/Whois.php';
require_once ROOT .'_sakura/components/Payments.php';
require_once ROOT .'_sakura/components/SockChat.php';

// Include database extensions
foreach(glob(ROOT .'_sakura/components/database/*.php') as $driver) {

    require_once $driver;

}

// Set Error handler
set_error_handler(array('Sakura\Main', 'errorHandler'));

// Initialise Main Class
Main::init(ROOT .'_sakura/config/config.ini');

// Start output buffering
ob_start(Configuration::getConfig('use_gzip') ? 'ob_gzhandler' : null);

if(!defined('SAKURA_NO_TPL')) {

    // Set base page rendering data
    $renderData = [

        'sakura' => [

            'versionInfo' => [

                'version'   => SAKURA_VERSION,
                'label'     => SAKURA_VLABEL,
                'colour'    => SAKURA_COLOUR,
                'stable'    => SAKURA_STABLE

            ],

            'cookie' => [

                'prefix'    => Configuration::getConfig('cookie_prefix'),
                'domain'    => Configuration::getConfig('cookie_domain'),
                'path'      => Configuration::getConfig('cookie_path'),

            ],

            'urlMain'   => Configuration::getConfig('url_main'),
            'urlApi'    => Configuration::getConfig('url_api'),

            'contentPath'   => Configuration::getConfig('content_path'),
            'resources'     => Configuration::getConfig('content_path') .'/data/'. strtolower(Templates::$_TPL),

            'charset'       => Configuration::getConfig('charset'),
            'siteName'      => Configuration::getConfig('sitename'),
            'siteDesc'      => Configuration::getConfig('sitedesc'),
            'siteTags'      => implode(", ", json_decode(Configuration::getConfig('sitetags'), true)),
            'dateFormat'    => Configuration::getConfig('date_format'),
            'currentPage'   => '//'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],

            'recaptchaPublic'   => Configuration::getConfig('recaptcha_public'),
            'recaptchaEnabled'  => Configuration::getConfig('recaptcha'),

            'disableRegistration'   => Configuration::getConfig('disable_registration'),
            'lockSite'              => Configuration::getConfig('lock_site'),
            'lockSiteReason'        => Configuration::getConfig('lock_site_reason'),
            'lockAuth'              => Configuration::getConfig('lock_authentication'),
            'requireRegCodes'       => Configuration::getConfig('require_registration_code'),
            'requireActivation'     => Configuration::getConfig('require_activation'),
            'minPwdEntropy'         => Configuration::getConfig('min_entropy'),
            'minUsernameLength'     => Configuration::getConfig('username_min_length'),
            'maxUsernameLength'     => Configuration::getConfig('username_max_length'),

            'disqus_shortname'  => Configuration::getConfig('disqus_shortname'),
            'disqus_api_key'    => Configuration::getConfig('disqus_api_key')

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

        'session' => [

            'checkLogin'    => Users::checkLogin(),
            'sessionId'     => Session::$sessionId,
            'userId'        => Session::$userId

        ],

        'user' => new User(Session::$userId)

    ];

    // Ban checking
    if(Users::checkLogin() && $ban = Bans::checkBan(Session::$userId)) {

        // Additional render data
        $renderData = array_merge($renderData, [
            'ban' => [
                'reason'    => $ban['reason'],
                'issued'    => $ban['issued'],
                'expires'   => $ban['expires'],
                'issuer'    => Users::getUser($ban['issuer'])
            ],
            'page' => [
                'title' => 'You are banned!'
            ]
        ]);

        Users::logout();
        print Templates::render('errors/banned.tpl', $renderData);
        exit;

    }

}
