<?php
use Sakura\Migration\IMigration;
use Sakura\DB;

class TenshiAndProfileFields implements IMigration
{
    public function up()
    {
        // Create the profile fields table
        DB::prepare("CREATE TABLE `{prefix}profilefields` (
            `id` int(64) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) COLLATE utf8_bin NOT NULL,
            `formtype` varchar(255) COLLATE utf8_bin NOT NULL,
            `description` varchar(255) COLLATE utf8_bin NOT NULL,
            `additional` varchar(255) COLLATE utf8_bin NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Create the tenshi table
        DB::prepare("CREATE TABLE `{prefix}tenshi` (
            `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
            `uid` bigint(255) unsigned NOT NULL,
            `startdate` int(64) unsigned NOT NULL,
            `expiredate` int(64) unsigned NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();
    }

    public function down()
    {
        // Drop the profile fields table
        DB::prepare("DROP TABLE `{prefix}profilefields`")
            ->execute();

        // Drop the tenshi table
        DB::prepare("DROP TABLE `{prefix}tenshi`")
            ->execute();
    }
}
