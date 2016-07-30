<?php
/**
 * Holds the migration reset command controller.
 *
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Sakura\DB;

class DatabaseResetCommand extends Command
{
    public function brief()
    {
        return 'Rollback all database migrations';
    }

    public function execute()
    {
        $repository = DB::getMigrationRepository();
        $migrator = new Migrator($repository, $repository->getConnectionResolver(), new Filesystem);

        if (!$migrator->repositoryExists()) {
            $this->getLogger()->writeln("The migration repository doesn't exist!");
            return;
        }

        $migrator->reset();

        foreach ($migrator->getNotes() as $note) {
            $this->getLogger()->writeln(strip_tags($note));
        }
    }
}
