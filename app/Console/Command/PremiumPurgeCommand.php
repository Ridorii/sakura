<?php
/**
 * Holds the purge premium command controller.
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Sakura\DB;
use Sakura\User;

/**
 * Starts up a development server.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class PremiumPurgeCommand extends Command
{
    /**
     * A quick description of this command.
     * @return string.
     */
    public function brief()
    {
        return 'Purge expired premium.';
    }

    /**
     * Purge expired premium subs.
     */
    public function execute()
    {
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
    }
}
