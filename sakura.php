<?php
/*
 * Sakura Community Management System
 * (c) 2013-2015 Flashwave <http://flash.moe>
 */

// Declare namespace
namespace Sakura;

// Define Sakura version
define('SAKURA_VERSION', '20151213');
define('SAKURA_VLABEL', 'Eminence');
define('SAKURA_COLOUR', '#6C3082');

// Define Sakura Path
define('ROOT', __DIR__ . '/');

// Turn error reporting on for the initial startup sequence
error_reporting(-1);

// Set internal encoding method
mb_internal_encoding('utf-8');

// Stop the execution if the PHP Version is older than 5.4.0
if (version_compare(phpversion(), '5.4.0', '<')) {
    die('Sakura requires at least PHP 5.4.0, please upgrade to a newer PHP version.');
}

// Include third-party libraries
if (!@include_once ROOT . 'vendor/autoload.php') {
    die('Autoloader not found, did you run composer?');
}

// Include components
require_once ROOT . 'libraries/ActionCode.php';
require_once ROOT . 'libraries/Bans.php';
require_once ROOT . 'libraries/BBcode.php';
require_once ROOT . 'libraries/Comments.php';
require_once ROOT . 'libraries/Config.php';
require_once ROOT . 'libraries/Database.php';
require_once ROOT . 'libraries/File.php';
require_once ROOT . 'libraries/Hashing.php';
require_once ROOT . 'libraries/Main.php';
require_once ROOT . 'libraries/Manage.php';
require_once ROOT . 'libraries/News.php';
require_once ROOT . 'libraries/Payments.php';
require_once ROOT . 'libraries/Permissions.php';
require_once ROOT . 'libraries/Rank.php';
require_once ROOT . 'libraries/Session.php';
require_once ROOT . 'libraries/Template.php';
require_once ROOT . 'libraries/Trick.php';
require_once ROOT . 'libraries/Urls.php';
require_once ROOT . 'libraries/User.php';
require_once ROOT . 'libraries/Users.php';
require_once ROOT . 'libraries/Whois.php';
require_once ROOT . 'libraries/Forum/Forum.php';
require_once ROOT . 'libraries/Forum/Permissions.php';
require_once ROOT . 'libraries/Forum/Post.php';
require_once ROOT . 'libraries/Forum/Thread.php';

// Include database extensions
foreach (glob(ROOT . 'libraries/DBWrapper/*.php') as $driver) {
    require_once $driver;
}

// Set Error handler
set_error_handler(['Sakura\Main', 'errorHandler']);

// Load the local configuration
Config::init(ROOT . 'config/config.ini');

// Change error reporting according to the dev configuration
error_reporting(Config::local('dev', 'enable') ? -1 : 0);

// Make the database connection
Database::init(Config::local('database', 'driver'));

// Load the configuration stored in the database
Config::initDB();

// Assign servers file to whois class
Whois::setServers(ROOT . Config::local('data', 'whoisservers'));

// Check if we the system has a cron service
if (Config::get('no_cron_service')) {
    // If not do an "asynchronous" call to the cron.php script
    if (Config::get('no_cron_last') < (time() - Config::get('no_cron_interval'))) {
        // Check OS
        if (substr(strtolower(PHP_OS), 0, 3) == 'win') {
            pclose(popen('start /B ' . PHP_BINDIR . '\php.exe ' . addslashes(ROOT . 'cron.php'), 'r'));
        } else {
            pclose(popen(PHP_BINDIR . '/php ' . ROOT . 'cron.php > /dev/null 2>/dev/null &', 'r'));
        }

        // Update last execution time
        Database::update('config', [['config_value' => time()], ['config_name' => ['no_cron_last', '=']]]);
    }
}

// Start output buffering
ob_start(Config::get('use_gzip') ? 'ob_gzhandler' : null);

// Auth check
$authCheck = Users::checkLogin();

// Create a user object for the current logged in user
$currentUser = new User($authCheck[0]);

// Create the Urls object
$urls = new Urls();

// Prepare the name of the template to load (outside of SAKURA_NO_TPL because it's used in imageserve.php)
$templateName =
!defined('SAKURA_MANAGE')
&& isset($currentUser->optionFields()['useMisaki'])
&& $currentUser->optionFields()['useMisaki'] ?
'misaki' : Config::get('site_style');

if (!defined('SAKURA_NO_TPL')) {
    // Set base page rendering data
    $renderData = [
        'sakura' => [
            'versionInfo' => [
                'version' => SAKURA_VERSION,
                'label' => SAKURA_VLABEL,
                'colour' => SAKURA_COLOUR,
            ],

            'dev' => [
                'enable' => Config::local('dev', 'enable'),
            ],

            'cookie' => [
                'prefix' => Config::get('cookie_prefix'),
                'domain' => Config::get('cookie_domain'),
                'path' => Config::get('cookie_path'),
            ],

            'contentPath' => Config::get('content_path'),
            'resources' => Config::get('content_path') . '/data/' . $templateName,

            'charset' => Config::get('charset'),
            'siteName' => Config::get('sitename'),
            'siteLogo' => Config::get('sitelogo'),
            'siteDesc' => Config::get('sitedesc'),
            'siteTags' => implode(", ", json_decode(Config::get('sitetags'), true)),
            'dateFormat' => Config::get('date_format'),
            'currentPage' => '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'referrer' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null),
            'onlineTimeout' => Config::get('max_online_time'),
            'announcementImage' => Config::get('header_announcement_image'),
            'announcementLink' => Config::get('header_announcement_link'),

            'recaptchaPublic' => Config::get('recaptcha_public'),
            'recaptchaEnabled' => Config::get('recaptcha'),

            'disableRegistration' => Config::get('disable_registration'),
            'lockAuth' => Config::get('lock_authentication'),
            'requireRegCodes' => Config::get('require_registration_code'),
            'requireActivation' => Config::get('require_activation'),
            'minPwdEntropy' => Config::get('min_entropy'),
            'minUsernameLength' => Config::get('username_min_length'),
            'maxUsernameLength' => Config::get('username_max_length'),
        ],
        'php' => [
            'sessionid' => \session_id(),
            'time' => \time(),
            'self' => $_SERVER['PHP_SELF'],
        ],

        'session' => [
            'checkLogin' => $authCheck,
            'sessionId' => $authCheck[1],
            'userId' => $authCheck[0],
        ],

        'user' => $currentUser,
        'urls' => $urls,

        'get' => $_GET,
        'post' => $_POST,
    ];

    // Site closing
    if (Config::get('site_closed')) {
        // Additional render data
        $renderData = array_merge($renderData, [
            'page' => [
                'message' => Config::get('site_closed_reason'),
            ],
        ]);

        // Initialise templating engine
        $template = new Template();

        // Change templating engine
        $template->setTemplate($templateName);

        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        echo $template->render('global/information');
        exit;
    }

    // Ban checking
    if ($authCheck && !in_array($_SERVER['PHP_SELF'], [$urls->format('AUTH_ACTION', [], false)]) && $ban = Bans::checkBan($currentUser->id())) {
        // Additional render data
        $renderData = array_merge($renderData, [
            'ban' => [
                'reason' => $ban['reason'],
                'issued' => $ban['issued'],
                'expires' => $ban['expires'],
                'issuer' => (new User($ban['issuer'])),
            ],
        ]);

        // Initialise templating engine
        $template = new Template();

        // Change templating engine
        $template->setTemplate($templateName);

        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        echo $template->render('main/banned');
        exit;
    }
}
