<?php
/*
 * Community Management System
 * (c) 2013-2016 Julian van de Groep <http://flash.moe>
 */

// Declare namespace
namespace Sakura;

// Define Sakura version
define('SAKURA_VERSION', 20160726);

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
error_reporting(config('dev.show_errors') ? -1 : 0);

// Create a new database capsule
$capsule = new DB;

// Add the connection
$capsule->addConnection(config('database'));

// Make the capsule globally accessible
$capsule->setAsGlobal();

// Start output buffering
ob_start(config('performance.compression') ? 'ob_gzhandler' : null);

// Initialise the router
Router::init();

// Include routes file
include_once ROOT . 'routes.php';

// Initialise the current session
$cookiePrefix = config('cookie.prefix');
// ActiveUser::init(
//     intval($_COOKIE["{$cookiePrefix}id"] ?? 0),
//     $_COOKIE["{$cookiePrefix}session"] ?? ''
// );

// Start templating engine
Template::set(config('general.design'));

// Set base page rendering data
Template::vars([
    'get' => $_GET,
    'user' => ActiveUser::$user,
    'post' => $_POST,
    'server' => $_SERVER,
    'request' => $_REQUEST,
    //'session' => $_SESSION,
]);
