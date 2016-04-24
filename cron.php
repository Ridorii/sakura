<?php
/*
 * Sakura Cron Agent
 */

// Declare Namespace
namespace Sakura;

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
    ->get(['user_id']);

foreach ($expiredPremium as $premium) {
    DB::table('premium')
        ->where('user_id', $premium->user_id)
        ->delete();

    User::construct($premium->user_id)
        ->isPremium();
}
