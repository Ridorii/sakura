<?php
/*
 * Sakura Cron Agent
 */

// Declare Namespace
namespace Sakura;

// Check if the script isn't executed by root
if (function_exists('posix_getuid')) {
    if (posix_getuid() === 0) {
        trigger_error('Running cron as root is disallowed for security reasons.', E_USER_ERROR);
        exit;
    }
}

// Define that this page won't require templating
define('SAKURA_NO_TPL', true);

// Include components
require_once 'sakura.php';

// Clean expired sessions
DB::table('sessions')
    ->where('session_expire', '<', time())
    ->where('session_remember', '!=', 1)
    ->delete();

// Delete notifications that are older than a month but not unread
DB::table('notifications')
    ->where('alert_timestamp', '<', (time() - 109500))
    ->where('alert_read', 1)
    ->delete();

// Get expired premium accounts
$expiredPremium = DB::table('premium')
    ->where('premium_expire', '<', time())
    ->get();

// Process expired premium accounts, make this not stupid in the future
foreach ($expiredPremium as $expired) {
    Users::updatePremiumMeta($expired->user_id);
}
