<?php
/**
 * Holds the migration repository installer command controller.
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Sakura\DB;

/**
 * Installs the database migration repository.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class DatabaseInstallCommand extends Command
{
    /**
     * A quick description of this command.
     * @return string.
     */
    public function brief()
    {
        return 'Create the migration repository';
    }

    /**
     * Does the repository installing.
     */
    public function execute()
    {
        $repository = DB::getMigrationRepository();
        $migrator = new Migrator($repository, $repository->getConnectionResolver(), new Filesystem);

        if ($migrator->repositoryExists()) {
            $this->getLogger()->writeln("The migration repository already exists!");
            return;
        }

        $repository->createRepository();
        $this->getLogger()->writeln("Created the migration repository!");
    }
}
