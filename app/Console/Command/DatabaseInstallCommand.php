<?php
/**
 * Holds the migration repository installer command controller.
 *
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Sakura\DB;

class DatabaseInstallCommand extends Command
{
    public function brief()
    {
        return 'Create the migration repository';
    }

    public function execute()
    {
        $repository = DB::getMigrationRepository();
        $repository->createRepository();
        $this->getLogger()->writeln("Created the migration repository!");
    }
}
