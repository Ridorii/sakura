<?php
/*
 * CLI Main
 */

namespace Sakura\Console;

/**
 * Class Console
 * @package Sakura
 */
class Application extends \CLIFramework\Application
{
    // Application info
    const NAME = 'Sakura';
    const VERSION = SAKURA_VERSION;

    // Initialiser
    public function init()
    {
        parent::init();
    }
}
