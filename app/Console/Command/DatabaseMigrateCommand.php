<?php
/**
 * Holds the migration command controller.
 *
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Sakura\DB;

class DatabaseMigrateCommand extends Command
{
    const MIGRATIONS = "database/";

    public function brief()
    {
        return 'Run the database migrations';
    }

    public function execute()
    {
        $repository = DB::getMigrationRepository();
        $migrator = new Migrator($repository, $repository->getConnectionResolver(), new Filesystem);

        if (!$migrator->repositoryExists()) {
            $this->getLogger()->writeln("Run 'database-install' first!");
            return;
        }

        $migrator->run(ROOT . self::MIGRATIONS);

        foreach ($migrator->getNotes() as $note) {
            $this->getLogger()->writeln($note);
        }
    }
}
