<?php
/*
 * Community Management System
 * (c) 2013-2016 Julian van de Groep <http://flash.moe>
 */

namespace Sakura;

// Define version and root path
define('SAKURA_VERSION', 20160804);
define('ROOT', __DIR__ . '/');

// CLI mode
if (php_sapi_name() === 'cli') {
    define('IN_CLI', true);
}

// Turn error reporting on regardless of anything
error_reporting(-1);

// Override expiration variables
ignore_user_abort(true);
set_time_limit(0);

// Set internal encoding method
mb_internal_encoding('utf-8');

// Check the PHP version
if (version_compare(phpversion(), '7.0.0', '<')) {
    die('Sakura requires at least PHP 7.0.0, please upgrade to a newer PHP version.');
}

// Check if the composer autoloader exists
if (!file_exists(ROOT . 'vendor/autoload.php')) {
    die('Autoloader not found, did you run composer install?');
}

// Include the autoloader
require_once ROOT . 'vendor/autoload.php';

// Register the handlers
set_exception_handler(['Sakura\ExceptionHandler', 'exception']);
set_error_handler(['Sakura\ExceptionHandler', 'error']);

// Load the configuration
Config::init(ROOT . 'config/config.ini');

// Start the database module
$capsule = new DB;
$capsule->addConnection(config('database'));
$capsule->setAsGlobal();

if (!defined('IN_CLI')) {
    // Start output buffering
    ob_start(config('performance.compression') ? 'ob_gzhandler' : null);

    // Initialise the router and include the routes file
    Router::init();
    include_once ROOT . 'routes.php';

    // Initialise the current session
    $cookiePrefix = config('cookie.prefix');
    ActiveUser::init(
        intval($_COOKIE["{$cookiePrefix}id"] ?? 0),
        $_COOKIE["{$cookiePrefix}session"] ?? ''
    );

    // Start templating engine and set base variables
    Template::set(config('general.design'));
    Template::vars([
        'get' => $_GET,
        'user' => ActiveUser::$user,
        'post' => $_POST,
        'server' => $_SERVER,
        'request' => $_REQUEST,
        'session' => $_SESSION,
    ]);
}
