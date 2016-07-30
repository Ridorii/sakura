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
        $document_root = addslashes(ROOT . 'public/');
        $router_proxy = addslashes(ROOT . 'server.php');
        $php_dir = PHP_BINDIR;
        $host = config('dev.host');

        $this->getLogger()->writeln("Starting Sakura development server on {$host}.");

        exec("{$php_dir}/php -S {$host} -t {$document_root} {$router_proxy}");
    }
}
