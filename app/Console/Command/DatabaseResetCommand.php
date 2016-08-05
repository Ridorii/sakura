<?php
/**
 * Holds the migration reset command controller.
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Sakura\DB;

/**
 * Resets the entire database.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class DatabaseResetCommand extends Command
{
    /**
     * A quick description of this command.
     * @return string.
     */
    public function brief()
    {
        return 'Rollback all database migrations';
    }

    /**
     * Does the resetting.
     */
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
