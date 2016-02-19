<?php
use Sakura\Migration\IMigration;
use Sakura\DB;

class NotificationsTable implements IMigration
{
    public function up()
    {
        // Add notifications table
        DB::prepare("CREATE TABLE `{prefix}notifications` (
            `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
            `uid` bigint(255) unsigned NOT NULL DEFAULT '0',
            `timestamp` int(11) unsigned NOT NULL DEFAULT '0',
            `notif_read` tinyint(1) unsigned NOT NULL DEFAULT '0',
            `notif_title` varchar(255) COLLATE utf8_bin NOT NULL,
            `notif_text` varchar(255) COLLATE utf8_bin NOT NULL,
            `notif_link` varchar(255) COLLATE utf8_bin DEFAULT NULL,
            `notif_img` varchar(255) COLLATE utf8_bin NOT NULL,
            `notif_timeout` int(16) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Change mod to iid in warnings table
        DB::prepare('ALTER TABLE `{prefix}warnings` CHANGE `mod` `iid` bigint(128)')
            ->execute();
    }

    public function down()
    {
        // Drop the notifications table
        DB::prepare("DROP TABLE `{prefix}notifications`")->execute();

        // Change iid to mod in warnings table
        DB::prepare('ALTER TABLE `{prefix}warnings` CHANGE `iid` `mod` bigint(128)')
            ->execute();
    }
}
