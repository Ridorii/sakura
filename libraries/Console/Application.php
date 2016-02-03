<?php
/**
 * Holds the console application meta.
 * 
 * @package Sakura
 */

namespace Sakura\Console;

/**
 * Command line interface main.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Application extends \CLIFramework\Application
{
    /**
     * CLI Application name
     */
    const NAME = 'Sakura';

    /**
     * CLI Application version
     */
    const VERSION = SAKURA_VERSION;

    /**
     * CLI initialiser
     */
    public function init()
    {
        parent::init();
    }
}
