<?php
/*
 * Community Management System
 * (c) 2013-2016 Julian van de Groep <http://flash.moe>
 */

namespace Sakura;

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
if (!file_exists('vendor/autoload.php')) {
    die('Autoloader not found, did you run composer install?');
}

// Include the autoloader
require_once 'vendor/autoload.php';

// Register the handlers
set_exception_handler([ExceptionHandler::class, 'exception']);
set_error_handler([ExceptionHandler::class, 'error']);

// Load the configuration
Config::init(path('config/config.ini'));

// Start the database module
$capsule = new DB;
$capsule->addConnection(config('database'));
$capsule->setAsGlobal();
