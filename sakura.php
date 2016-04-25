<?php
/*
 * Sakura Community Management System
 * (c) 2013-2016 Julian van de Groep <http://flash.moe>
 */

// Declare namespace
namespace Sakura;

// Define Sakura version
define('SAKURA_VERSION', 20160425);

// Define Sakura Path
define('ROOT', __DIR__ . '/');

// Turn error reporting on for the initial startup sequence
error_reporting(-1);

// Override expiration variables
ignore_user_abort(true);
set_time_limit(0);

// Set internal encoding method
mb_internal_encoding('utf-8');

// Stop the execution if the PHP Version is older than 7.0.0
if (version_compare(phpversion(), '7.0.0', '<')) {
    throw new Exception('Sakura requires at least PHP 7.0.0, please upgrade to a newer PHP version.');
}

// Check if the composer autoloader exists
if (!file_exists(ROOT . 'vendor/autoload.php')) {
    throw new Exception('Autoloader not found, did you run composer install?');
}

// Require composer libraries
require_once ROOT . 'vendor/autoload.php';

// Load the local configuration
Config::init(ROOT . 'config/config.ini');

// Set Error handler
set_error_handler('error_handler');

// Change error reporting according to the dev configuration
error_reporting(Config::local('dev', 'show_errors') ? -1 : 0);

// Create a new database capsule
$capsule = new \Illuminate\Database\Capsule\Manager;

// Add the connection
$capsule->addConnection(Config::local('database'));

// Make the capsule globally accessible
$capsule->setAsGlobal();

// Check if we the system has a cron service
if (Config::get('no_cron_service')) {
    // If not do an "asynchronous" call to the cron.php script
    if (Config::get('no_cron_last') < (time() - Config::get('no_cron_interval'))) {
        $phpDir = PHP_BINDIR;
        $cronPath = ROOT . 'cron.php';

        // Check OS
        if (substr(strtolower(PHP_OS), 0, 3) == 'win') {
            $cronPath = addslashes($cronPath);

            pclose(popen("start /B {$phpDir}\php.exe {$cronPath}", 'r'));
        } else {
            pclose(popen("{$phpDir}/php {$cronPath} > /dev/null 2>/dev/null &", 'r'));
        }

        unset($phpDir, $cronPath);

        // Update last execution time
        Config::set('no_cron_last', time());
    }
}

// Start output buffering
ob_start(Config::get('use_gzip') ? 'ob_gzhandler' : null);

// Initialise the router
Router::init();

// Include routes file
include_once ROOT . 'routes.php';

// Initialise the current session
$cookiePrefix = Config::get('cookie_prefix');
ActiveUser::init(
    intval($_COOKIE["{$cookiePrefix}id"] ?? 0),
    $_COOKIE["{$cookiePrefix}session"] ?? ''
);

if (!defined('SAKURA_NO_TPL')) {
    // Start templating engine
    Template::set(Config::get('site_style'));

    // Set base page rendering data
    Template::vars([
        'get' => $_GET,
        'user' => ActiveUser::$user,
        'post' => $_POST,
        'server' => $_SERVER,
        'request' => $_REQUEST,
        'session' => $_SESSION,
    ]);
}
