<?php
/*
 * Sakura Community Management System
 * (c) 2013-2016 Julian van de Groep <http://flash.moe>
 */

// Declare namespace
namespace Sakura;

// Define Sakura version
define('SAKURA_VERSION', '20160214');
define('SAKURA_VLABEL', 'Amethyst');
define('SAKURA_COLOUR', '#9966CC');

// Define Sakura Path
define('ROOT', __DIR__ . '/');

// Turn error reporting on for the initial startup sequence
error_reporting(-1);

// Override expiration variables
ignore_user_abort(true);
set_time_limit(0);

// Set internal encoding method
mb_internal_encoding('utf-8');

// Stop the execution if the PHP Version is older than 5.4.0
if (version_compare(phpversion(), '5.5.0', '<')) {
    die('Sakura requires at least PHP 5.5.0, please upgrade to a newer PHP version.');
}

// Include third-party libraries
if (!@include_once ROOT . 'vendor/autoload.php') {
    die('Autoloader not found, did you run composer?');
}

// Setup the autoloader
spl_autoload_register(function ($className) {
    // Create a throwaway count variable
    $i = 1;

    // Replace the sakura namespace with the libraries directory
    $className = str_replace('Sakura\\', 'libraries/', $className, $i);

    // Require the file
    require_once ROOT . $className . '.php';
});

// Include database extensions
foreach (glob(ROOT . 'libraries/DBWrapper/*.php') as $driver) {
    require_once $driver;
}

// Set Error handler
set_error_handler(['Sakura\Utils', 'errorHandler']);

// Load the local configuration
Config::init(ROOT . 'config/config.ini');

// Change error reporting according to the dev configuration
error_reporting(Config::local('dev', 'show_errors') ? -1 : 0);

// Make the database connection
Database::init(Config::local('database', 'driver'));

// Load the configuration stored in the database
Config::initDB();

// Check if we're using console
if (php_sapi_name() === 'cli' && !defined('SAKURA_CRON')) {
    $console = new Console\Application;
    $console->run($argv);
    exit;
}

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

// Initialise the router
Router::init();

// Include routes file
include_once ROOT . 'routes.php';

// Auth check
$authCheck = Users::checkLogin();

// Create a user object for the current logged in user
$currentUser = User::construct($authCheck[0]);

// Create the Urls object
$urls = new Urls();

// Prepare the name of the template to load (outside of SAKURA_NO_TPL because it's used in imageserve.php)
$templateName =
!defined('SAKURA_MANAGE')
&& isset($currentUser->optionFields()['useMisaki'])
&& $currentUser->optionFields()['useMisaki'] ?
'misaki' : Config::get('site_style');

if (!defined('SAKURA_NO_TPL')) {
    // Start templating engine
    Template::set($templateName);

    // Set base page rendering data
    $renderData = [
        'sakura' => [
            'versionInfo' => [
                'version' => SAKURA_VERSION,
            ],

            'dev' => [
                'showChangelog' => Config::local('dev', 'show_changelog'),
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
            'siteTags' => json_decode(Config::get('sitetags'), true),
            'dateFormat' => 'r',
            'currentPage' => (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null),
            'referrer' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null),
            'onlineTimeout' => Config::get('max_online_time'),
            'announcementImage' => Config::get('header_announcement_image'),
            'announcementLink' => Config::get('header_announcement_link'),
            'trashForumId' => Config::get('forum_trash_id'),

            'recaptchaPublic' => Config::get('recaptcha_public'),
            'recaptchaEnabled' => Config::get('recaptcha'),

            'disableRegistration' => Config::get('disable_registration'),
            'lockAuth' => Config::get('lock_authentication'),
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

    // Add the default render data
    Template::vars($renderData);

    // Site closing
    if (Config::get('site_closed')) {
        // Set parse variables
        Template::vars([
            'page' => [
                'message' => Config::get('site_closed_reason'),
            ],
        ]);

        // Print page contents
        echo Template::render('global/information');
        exit;
    }

    // Ban checking
    if ($authCheck && !in_array($_SERVER['PHP_SELF'], [$urls->format('AUTH_ACTION', [], false)]) && $ban = Bans::checkBan($currentUser->id)) {
        // Additional render data
        Template::vars([
            'ban' => [
                'reason' => $ban['reason'],
                'issued' => $ban['issued'],
                'expires' => $ban['expires'],
                'issuer' => (User::construct($ban['issuer'])),
            ],
        ]);

        // Print page contents
        echo Template::render('main/banned');
        exit;
    }
}
