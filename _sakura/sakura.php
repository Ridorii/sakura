<?php
/*
 * Sakura Community Management System
 * (c) 2013-2015 Flashwave <http://flash.moe>
 */

// Declare namespace
namespace Sakura;

// Define Sakura version
define('SAKURA_VERSION', '20151125');
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

// Include third-party libraries
require_once ROOT . '_sakura/vendor/autoload.php';

// Include components
require_once ROOT . '_sakura/components/Bans.php';
require_once ROOT . '_sakura/components/Comments.php';
require_once ROOT . '_sakura/components/Config.php';
require_once ROOT . '_sakura/components/Database.php';
require_once ROOT . '_sakura/components/File.php';
require_once ROOT . '_sakura/components/Hashing.php';
require_once ROOT . '_sakura/components/Main.php';
require_once ROOT . '_sakura/components/Manage.php';
require_once ROOT . '_sakura/components/News.php';
require_once ROOT . '_sakura/components/Payments.php';
require_once ROOT . '_sakura/components/Permissions.php';
require_once ROOT . '_sakura/components/Rank.php';
require_once ROOT . '_sakura/components/Session.php';
require_once ROOT . '_sakura/components/Template.php';
require_once ROOT . '_sakura/components/Trick.php';
require_once ROOT . '_sakura/components/Urls.php';
require_once ROOT . '_sakura/components/User.php';
require_once ROOT . '_sakura/components/Users.php';
require_once ROOT . '_sakura/components/Whois.php';
require_once ROOT . '_sakura/components/BBcode/BBcode.php';
require_once ROOT . '_sakura/components/BBcode/Parse.php';
require_once ROOT . '_sakura/components/BBcode/Store.php';
require_once ROOT . '_sakura/components/Forum/Forum.php';
require_once ROOT . '_sakura/components/Forum/Forums.php';
require_once ROOT . '_sakura/components/Forum/Post.php';
require_once ROOT . '_sakura/components/Forum/Thread.php';

// Include database extensions
foreach (glob(ROOT . '_sakura/components/DBWrapper/*.php') as $driver) {
    require_once $driver;
}

// Set Error handler
set_error_handler(['Sakura\Main', 'errorHandler']);

// Initialise Main Class
Main::init(ROOT . '_sakura/config/config.ini');

// Assign servers file to whois class
Whois::setServers(ROOT . '_sakura/' . Config::getLocalConfig('data', 'whoisservers'));

// Check if we the system has a cron service
if (Config::getConfig('no_cron_service')) {
    // If not do an "asynchronous" call to the cron.php script
    if (Config::getConfig('no_cron_last') < (time() - Config::getConfig('no_cron_interval'))) {
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
ob_start(Config::getConfig('use_gzip') ? 'ob_gzhandler' : null);

// Auth check
$authCheck = Users::checkLogin();

// Create a user object for the current logged in user
$currentUser = new User($authCheck[0]);

// Create the Urls object
$urls = new Urls();

// Prepare the name of the template to load (outside of SAKURA_NO_TPL because it's used in imageserve.php)
$templateName =
defined('SAKURA_MANAGE') ?
Config::getConfig('manage_style') :
(
    isset($currentUser->optionFields()['useMisaki']) && $currentUser->optionFields()['useMisaki'] ?
    'misaki' :
    Config::getConfig('site_style')
);

if (!defined('SAKURA_NO_TPL')) {
    // Set base page rendering data
    $renderData = [
        'sakura' => [
            'versionInfo' => [
                'version' => SAKURA_VERSION,
                'label' => SAKURA_VLABEL,
                'colour' => SAKURA_COLOUR,
                'stable' => SAKURA_STABLE,
            ],

            'cookie' => [
                'prefix' => Config::getConfig('cookie_prefix'),
                'domain' => Config::getConfig('cookie_domain'),
                'path' => Config::getConfig('cookie_path'),
            ],

            'urlMain' => Config::getConfig('url_main'),
            'urlApi' => Config::getConfig('url_api'),

            'contentPath' => Config::getConfig('content_path'),
            'resources' => Config::getConfig('content_path') . '/data/' . $templateName,

            'charset' => Config::getConfig('charset'),
            'siteName' => Config::getConfig('sitename'),
            'siteLogo' => Config::getConfig('sitelogo'),
            'siteDesc' => Config::getConfig('sitedesc'),
            'siteTags' => implode(", ", json_decode(Config::getConfig('sitetags'), true)),
            'dateFormat' => Config::getConfig('date_format'),
            'currentPage' => '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'referrer' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null),
            'onlineTimeout' => Config::getConfig('max_online_time'),

            'recaptchaPublic' => Config::getConfig('recaptcha_public'),
            'recaptchaEnabled' => Config::getConfig('recaptcha'),

            'disableRegistration' => Config::getConfig('disable_registration'),
            'lockAuth' => Config::getConfig('lock_authentication'),
            'requireRegCodes' => Config::getConfig('require_registration_code'),
            'requireActivation' => Config::getConfig('require_activation'),
            'minPwdEntropy' => Config::getConfig('min_entropy'),
            'minUsernameLength' => Config::getConfig('username_min_length'),
            'maxUsernameLength' => Config::getConfig('username_max_length'),
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
    if (Config::getConfig('site_closed')) {
        // Additional render data
        $renderData = array_merge($renderData, [

            'page' => [
                'message' => Config::getConfig('site_closed_reason'),
            ],

        ]);

        // Initialise templating engine
        $template = new Template();

        // Change templating engine
        $template->setTemplate($templateName);

        // Set parse variables
        $template->setVariables($renderData);

        // Print page contents
        echo $template->render('global/information.tpl');
        exit;
    }

    // Ban checking
    if ($authCheck && !in_array($_SERVER['PHP_SELF'], ['/authenticate.php']) && $ban = Bans::checkBan($currentUser->id())) {
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
        echo $template->render('main/banned.tpl');
        exit;
    }
}
