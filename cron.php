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

// To prevent the CLI from showing up
define('SAKURA_CRON', true);

// Include components
require_once 'sakura.php';

// Clean expired sessions
Database::delete('sessions', [
    'session_expire' => [time(), '<'],
    'session_remember' => ['1', '!='],
]);

// Delete notifications that are older than a month but not unread
Database::delete('notifications', [
    'alert_timestamp' => [(time() - 109500), '<'],
    'alert_read' => ['1', '='],
]);

// Get expired premium accounts
$expiredPremium = Database::fetch('premium', true, [
    'premium_expire' => [time(), '<'],
]);

// Process expired premium accounts, make this not stupid in the future
foreach ($expiredPremium as $expired) {
    Users::updatePremiumMeta($expired['user_id']);
}
