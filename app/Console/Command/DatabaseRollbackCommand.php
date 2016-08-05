<?php
/**
 * Holds the migration rollback command controller.
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Sakura\DB;

/**
 * Rolls back the last database migration action.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class DatabaseRollbackCommand extends Command
{
    /**
     * A quick description of this command.
     * @return string.
     */
    public function brief()
    {
        return 'Rollback the last database migration';
    }

    /**
     * Does the rolling back.
     */
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
