<?php
/**
 * Holds the database migration interface.
 * 
 * @package Sakura
 */

namespace Sakura\Migration;

/**
 * Migration interface.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
interface IMigration
{
    /**
     * Upgrade the database to a newer version.
     */
    public function up();

    /**
     * Downgrade the database to an older version.
     */
    public function down();
}
