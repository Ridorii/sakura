<?php
/**
 * Holds the migration rollback command controller.
 *
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Sakura\DB;

class DatabaseRollbackCommand extends Command
{
    public function brief()
    {
        return 'Rollback the last database migration';
    }

    public function execute()
    {
        $repository = DB::getMigrationRepository();
        $migrator = new Migrator($repository, $repository->getConnectionResolver(), new Filesystem);

        $migrator->rollback();

        foreach ($migrator->getNotes() as $note) {
            $this->getLogger()->writeln(strip_tags($note));
        }
    }
}
