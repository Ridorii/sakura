<?php
use Sakura\Migration\IMigration;
use Sakura\DB;

class ActionCodes implements IMigration
{
    public function up()
    {
        // Create the profile fields table
        DB::prepare("CREATE TABLE `{prefix}actioncodes` (
            `id` bigint(255) NOT NULL AUTO_INCREMENT,
            `action` varchar(255) COLLATE utf8_bin NOT NULL,
            `userid` bigint(255) NOT NULL,
            `actkey` varchar(255) COLLATE utf8_bin NOT NULL,
            `instruction` varchar(255) COLLATE utf8_bin NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();
    }

    public function down()
    {
        // Drop the profile fields table
        DB::prepare("DROP TABLE `{prefix}actioncodes`")
            ->execute();
    }
}
