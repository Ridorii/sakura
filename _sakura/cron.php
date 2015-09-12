<?php
/*
 * Sakura Cron Agent
 */

// Declare Namespace
namespace Sakura;

// Define that this page won't require templating
define('SAKURA_NO_TPL', true);

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Override expiration variables
ignore_user_abort(true);
set_time_limit(0);
