<?php
/**
 * Holds the migration status command controller.
 *
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use CLIFramework\Component\Table\Table;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Sakura\DB;

class DatabaseStatusCommand extends Command
{
    const MIGRATIONS = "database/";

    public function brief()
    {
        return 'Show the status of each migration';
    }

    public function execute()
    {
        $repository = DB::getMigrationRepository();
        $migrator = new Migrator($repository, $repository->getConnectionResolver(), new Filesystem);

        if (!$migrator->repositoryExists()) {
            $this->getLogger()->writeln("No migrations found!");
            return;
        }

        $ran = $repository->getRan();

        $migrations = new Table;

        $migrations->setHeaders([
            'Ran?',
            'Migration',
        ]);

        foreach ($migrator->getMigrationFiles(ROOT . self::MIGRATIONS) as $migration) {
            $migrations->addRow([in_array($migration, $ran) ? 'Y' : 'N', $migration]);
        }

        $this->getLogger()->write($migrations->render());
    }
}
