<?php
use Sakura\Migration\IMigration;
use Sakura\DB;

class InfoPagesAndNulls implements IMigration
{
    public function up()
    {
        // Add multi column to the groups table
        DB::prepare("ALTER TABLE `{prefix}groups` ADD `multi` tinyint(1) unsigned NOT NULL DEFAULT '0'")
            ->execute();

        // Create info pages table
        DB::prepare("CREATE TABLE `{prefix}infopages` (
            `shorthand` varchar(255) COLLATE utf8_bin NOT NULL,
            `pagetitle` varchar(255) COLLATE utf8_bin NOT NULL,
            `content` text COLLATE utf8_bin NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Make certain fields in the users table nullable
        DB::prepare('ALTER TABLE `{prefix}users` MODIFY `usertitle` DEFAULT NULL')
            ->execute();
        DB::prepare('ALTER TABLE `{prefix}users` MODIFY `avatar_url` DEFAULT NULL')
            ->execute();
        DB::prepare('ALTER TABLE `{prefix}users` MODIFY `background_url` DEFAULT NULL')
            ->execute();
    }

    public function down()
    {
        // Drop the multi column from the groups table
        DB::prepare("ALTER TABLE `{prefix}groups` DROP COLUMN `multi`")
            ->execute();

        // Drop info pages table
        DB::prepare("DROP TABLE `{prefix}infopages`")
            ->execute();

        // Revert the null
        DB::prepare('ALTER TABLE `{prefix}users` MODIFY `usertitle` NOT NULL')
            ->execute();
        DB::prepare('ALTER TABLE `{prefix}users` MODIFY `avatar_url` NOT NULL')
            ->execute();
        DB::prepare('ALTER TABLE `{prefix}users` MODIFY `background_url` NOT NULL')
            ->execute();
    }
}
