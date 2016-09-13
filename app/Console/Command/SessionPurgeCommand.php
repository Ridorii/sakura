<?php
/**
 * Holds the session purge command controller.
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Sakura\DB;

/**
 * Starts up a development server.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class SessionPurgeCommand extends Command
{
    /**
     * A quick description of this command.
     * @return string.
     */
    public function brief()
    {
        return 'Purge expired sessions.';
    }

    /**
     * Purges sessions.
     */
    public function execute()
    {
        DB::table('sessions')
            ->where('session_expire', '<', time())
            ->where('session_remember', '!=', 1)
            ->delete();
    }
}
