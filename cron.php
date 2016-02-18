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
$cleanSessions = DB::prepare('DELETE FROM `{prefix}sessions` WHERE `session_expire` < :time AND `session_remember` != 1');
$cleanSessions->execute([
    'time' => time(),
]);

// Delete notifications that are older than a month but not unread
$cleanAlerts = DB::prepare('DELETE FROM `{prefix}notifications` WHERE `alert_timestamp` < :time AND `alert_read` = 1');
$cleanAlerts->execute([
    'time' => (time() - 109500),
]);

// Get expired premium accounts
$expiredPremium = DB::prepare('SELECT * FROM `{prefix}premium` WHERE `premium_expire` < :time');
$expiredPremium->execute([
    'time' => time(),
]);
$expiredPremium = $expiredPremium->fetchAll();

// Process expired premium accounts, make this not stupid in the future
foreach ($expiredPremium as $expired) {
    Users::updatePremiumMeta($expired->user_id);
}
