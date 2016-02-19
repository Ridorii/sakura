<?php
/**
 * Holds the serve command controller.
 * 
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;

class ServeCommand extends Command
{
    public function brief()
    {
        return 'Sets up a local development server.';
    }

    public function execute()
    {
        exec(PHP_BINDIR . '\php -S localhost:8000 -t ' . addslashes(ROOT . 'public/') . ' ' . addslashes(ROOT . 'server.php'));
    }
}
