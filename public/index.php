<?php
/*
 * Sakura Router
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once __DIR__ . '/../sakura.php';

// Start output buffering
ob_start(config('performance.compression') ? 'ob_gzhandler' : null);

// Initialise the current session
$cookiePrefix = config('cookie.prefix');
CurrentSession::start(
    intval($_COOKIE["{$cookiePrefix}id"] ?? 0),
    $_COOKIE["{$cookiePrefix}session"] ?? '',
    Net::ip()
);

// Start templating engine and set base variables
Template::set(CurrentSession::$user->design());
Template::vars([
    'get' => $_GET,
    'user' => CurrentSession::$user,
    'post' => $_POST,
    'server' => $_SERVER,
    'request' => $_REQUEST,
    'session' => $_SESSION,
]);

// Handle requests
echo Routerv1::handle($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
