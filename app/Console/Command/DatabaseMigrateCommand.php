<?php
/**
 * Holds the migration command controller.
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Sakura\DB;

/**
 * Brings the database up to speed with the ones in the database folder.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class DatabaseMigrateCommand extends Command
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
        return 'Run the database migrations';
    }

    /**
     * Does the migrating.
     */
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
            $this->getLogger()->writeln(strip_tags($note));
        }
    }
}
