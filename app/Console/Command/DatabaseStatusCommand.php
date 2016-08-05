<?php
/**
 * Holds the migration status command controller.
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use CLIFramework\Component\Table\Table;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Sakura\DB;

/**
 * Returns the status of the database migrations.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class DatabaseStatusCommand extends Command
{
    /**
     * The database migrations directory.
     */
    const MIGRATIONS = "database/";

    /**
     * A quick description of this command.
     * @return string.
     */
    public function brief()
    {
        return 'Show the status of each migration';
    }

    /**
     * Fulfills the purpose of what is described above this class.
     */
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
