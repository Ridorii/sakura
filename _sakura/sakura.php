<?php
/*
 * Sakura Community Management System
 * (c) 2013-2015 Flashwave <http://flash.moe>
 */

// Declare namespace
namespace Sakura;

// Define Sakura version
define('SAKURA_VERSION', '20151011');
define('SAKURA_VLABEL', 'Eminence');
define('SAKURA_COLOUR', '#6C3082');
define('SAKURA_STABLE', false);

// Define Sakura Path
define('ROOT', str_replace(basename(__DIR__), '', dirname(__FILE__)));

// Error Reporting: 0 for production and -1 for testing
error_reporting(SAKURA_STABLE ? 0 : -1);

// Set internal encoding method
mb_internal_encoding('utf-8');

// Stop the execution if the PHP Version is older than 5.4.0
if (version_compare(phpversion(), '5.4.0', '<')) {
    die('<h3>Sakura requires at least PHP 5.4.0, please upgrade to a newer PHP version.</h3>');
}

// Include libraries
require_once ROOT . '_sakura/vendor/autoload.php';
require_once ROOT . '_sakura/components/Main.php';
require_once ROOT . '_sakura/components/Hashing.php';
require_once ROOT . '_sakura/components/Configuration.php';
require_once ROOT . '_sakura/components/Database.php';
require_once ROOT . '_sakura/components/Urls.php';
require_once ROOT . '_sakura/components/Templates.php';
require_once ROOT . '_sakura/components/Permissions.php';
require_once ROOT . '_sakura/components/Sessions.php';
require_once ROOT . '_sakura/components/User.php';
require_once ROOT . '_sakura/components/Rank.php';
require_once ROOT . '_sakura/components/Users.php';
require_once ROOT . '_sakura/components/Forum.php';
require_once ROOT . '_sakura/components/News.php';
require_once ROOT . '_sakura/components/Comments.php';
require_once ROOT . '_sakura/components/Manage.php';
require_once ROOT . '_sakura/components/Bans.php';
require_once ROOT . '_sakura/components/Whois.php';
require_once ROOT . '_sakura/components/Payments.php';

// Include database extensions
foreach (glob(ROOT . '_sakura/components/database/*.php') as $driver) {
    require_once $driver;
}

// Set Error handler
set_error_handler(array('Sakura\Main', 'errorHandler'));

// Initialise Main Class
Main::init(ROOT . '_sakura/config/config.ini');

// Assign servers file to whois class
Whois::setServers(ROOT . '_sakura/' . Configuration::getLocalConfig('data', 'whoisservers'));

// Check if we the system has a cron service
if (Configuration::getConfig('no_cron_service')) {
    // If not do an "asynchronous" call to the cron.php script
    if (Configuration::getConfig('no_cron_last') < (time() - Configuration::getConfig('no_cron_interval'))) {
        // Check OS
        if (substr(strtolower(PHP_OS), 0, 3) == 'win') {
            pclose(popen('start /B ' . PHP_BINDIR . '\php.exe ' . addslashes(ROOT . '_sakura\cron.php'), 'r'));
        } else {
            pclose(popen(PHP_BINDIR . '/php ' . ROOT . '_sakura/cron.php > /dev/null 2>/dev/null &', 'r'));
        }

        // Update last execution time
        Database::update('config', [
            [

                'config_value' => time(),

            ],
            [

                'config_name' => ['no_cron_last', '='],

            ],
        ]);

    }

}

// Start output buffering
ob_start(Configuration::getConfig('use_gzip') ? 'ob_gzhandler' : null);

// Create a user object for the current logged in user
$currentUser = new User(Session::$userId);

// Create the Urls object
$urls = new Urls();

// Prepare the name of the template to load (outside of SAKURA_NO_TPL because it's used in imageserve.php)
$templateName =
defined('SAKURA_MANAGE') ?
Configuration::getConfig('manage_style') :
(
    isset($currentUser->data['user_data']['userOptions']['useMisaki']) &&
    $currentUser->data['user_data']['userOptions']['useMisaki'] &&
    $currentUser->checkPermission('SITE', 'ALTER_PROFILE')
) ?
'misaki' :
Configuration::getConfig('site_style');

if (!defined('SAKURA_NO_TPL')) {
    // Initialise templating engine
    Templates::init($templateName);

    // Set base page rendering data
    $renderData = [

        /*
         * Idea for flexibility in templates and to reduce redundancy;
         * Attempt to use a class instead of an assoc. array for the
         *  template variables since twig supports this to make accessing
         *  certain functions, like the time elapsed function easier.
         * Update 2015-09-08: Apparently this will be added in PHP 7 so
         *  we'll be looking out for that.
         */

        'sakura' => [

            'versionInfo' => [

                'version' => SAKURA_VERSION,
                'label' => SAKURA_VLABEL,
                'colour' => SAKURA_COLOUR,
                'stable' => SAKURA_STABLE,

            ],

            'cookie' => [

                'prefix' => Configuration::getConfig('cookie_prefix'),
                'domain' => Configuration::getConfig('cookie_domain'),
                'path' => Configuration::getConfig('cookie_path'),

            ],

            'urlMain' => Configuration::getConfig('url_main'),
            'urlApi' => Configuration::getConfig('url_api'),

            'contentPath' => Configuration::getConfig('content_path'),
            'resources' => Configuration::getConfig('content_path') . '/data/' . strtolower(Templates::$template),

            'charset' => Configuration::getConfig('charset'),
            'siteName' => Configuration::getConfig('sitename'),
            'siteLogo' => Configuration::getConfig('sitelogo'),
            'siteDesc' => Configuration::getConfig('sitedesc'),
            'siteTags' => implode(", ", json_decode(Configuration::getConfig('sitetags'), true)),
            'dateFormat' => Configuration::getConfig('date_format'),
            'currentPage' => '//' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],

            'recaptchaPublic' => Configuration::getConfig('recaptcha_public'),
            'recaptchaEnabled' => Configuration::getConfig('recaptcha'),

            'disableRegistration' => Configuration::getConfig('disable_registration'),
            'lockAuth' => Configuration::getConfig('lock_authentication'),
            'requireRegCodes' => Configuration::getConfig('require_registration_code'),
            'requireActivation' => Configuration::getConfig('require_activation'),
            'minPwdEntropy' => Configuration::getConfig('min_entropy'),
            'minUsernameLength' => Configuration::getConfig('username_min_length'),
            'maxUsernameLength' => Configuration::getConfig('username_max_length'),

        ],

        'php' => [

            'sessionid' => \session_id(),
            'time' => \time(),
            'self' => $_SERVER['PHP_SELF'],

        ],

        'session' => [

            'checkLogin' => Users::checkLogin(),
            'sessionId' => Session::$sessionId,
            'userId' => Session::$userId,

        ],

        'user' => $currentUser,
        'urls' => $urls,

    ];

    // Site closing
    if (Configuration::getConfig('site_closed')) {
        // Additional render data
        $renderData = array_merge($renderData, [

            'page' => [
                'message' => Configuration::getConfig('site_closed_reason'),
            ],

        ]);

        print Templates::render('global/information.tpl', $renderData);
        exit;
    }

    // Ban checking
    if (Users::checkLogin() && $ban = Bans::checkBan(Session::$userId)) {
        // Additional render data
        $renderData = array_merge($renderData, [

            'ban' => [
                'reason' => $ban['reason'],
                'issued' => $ban['issued'],
                'expires' => $ban['expires'],
                'issuer' => Users::getUser($ban['issuer']),
            ],

        ]);

        Users::logout();
        print Templates::render('main/banned.tpl', $renderData);
        exit;
    }
}
