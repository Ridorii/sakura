<?php
use Sakura\Migration\IMigration;
use Sakura\DB;

class InitialStructure implements IMigration
{
    public function up()
    {
        // Create API key table
        DB::prepare("CREATE TABLE `{prefix}apikeys` (
            `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT,
            `owner` bigint(128) unsigned NOT NULL,
            `apikey` varchar(32) COLLATE utf8_bin NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Create bans table
        DB::prepare("CREATE TABLE `{prefix}bans` (
            `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT,
            `uid` bigint(128) unsigned NOT NULL,
            `ip` varchar(255) COLLATE utf8_bin NOT NULL,
            `type` tinyint(1) unsigned NOT NULL,
            `timestamp` int(64) unsigned NOT NULL,
            `bannedtill` int(64) unsigned NOT NULL,
            `modid` bigint(128) unsigned NOT NULL,
            `modip` varchar(255) COLLATE utf8_bin NOT NULL,
            `reason` varchar(255) COLLATE utf8_bin DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Create config table
        DB::prepare("CREATE TABLE `{prefix}config` (
            `config_name` varchar(255) COLLATE utf8_bin NOT NULL,
            `config_value` varchar(255) COLLATE utf8_bin NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Create groups table
        DB::prepare("CREATE TABLE `{prefix}groups` (
            `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT,
            `groupname` varchar(255) COLLATE utf8_bin NOT NULL,
            `colour` varchar(255) COLLATE utf8_bin NOT NULL,
            `description` text COLLATE utf8_bin NOT NULL',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();
        
        // Create messages table
        DB::prepare("CREATE TABLE `{prefix}messages` (
            `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT,
            `fromUser` bigint(128) unsigned NOT NULL,
            `toUsers` varchar(255) COLLATE utf8_bin NOT NULL,
            `readBy` varchar(255) COLLATE utf8_bin NOT NULL,
            `deletedBy` varchar(255) COLLATE utf8_bin NOT NULL,
            `date` int(64) unsigned NOT NULL,
            `title` varchar(255) COLLATE utf8_bin NOT NULL,
            `content` text COLLATE utf8_bin NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Create news table
        DB::prepare("CREATE TABLE `{prefix}news` (
            `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT,
            `uid` bigint(128) unsigned NOT NULL,
            `date` int(64) unsigned NOT NULL,
            `title` varchar(255) COLLATE utf8_bin NOT NULL,
            `content` text COLLATE utf8_bin NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Create registration codes table
        DB::prepare("CREATE TABLE `{prefix}regcodes` (
            `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT,
            `code` varchar(32) COLLATE utf8_bin NOT NULL,
            `created_by` bigint(128) unsigned NOT NULL,
            `used_by` bigint(128) unsigned NOT NULL,
            `key_used` tinyint(1) unsigned NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Create sessions table
        DB::prepare("CREATE TABLE `{prefix}sessions` (
            `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT,
            `userip` varchar(255) COLLATE utf8_bin NOT NULL,
            `useragent` varchar(255) COLLATE utf8_bin DEFAULT NULL,
            `userid` bigint(128) unsigned NOT NULL,
            `skey` varchar(255) COLLATE utf8_bin NOT NULL,
            `started` int(64) unsigned NOT NULL,
            `expire` int(64) unsigned NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Create users table
        DB::prepare("CREATE TABLE `{prefix}users` (
            `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
            `username` varchar(255) COLLATE utf8_bin NOT NULL,
            `username_clean` varchar(255) COLLATE utf8_bin NOT NULL,
            `password_hash` varchar(255) COLLATE utf8_bin NOT NULL,
            `password_salt` varchar(255) COLLATE utf8_bin NOT NULL,
            `password_algo` varchar(255) COLLATE utf8_bin NOT NULL,
            `password_iter` int(16) unsigned NOT NULL,
            `password_chan` int(16) unsigned NOT NULL,
            `password_new` varchar(255) COLLATE utf8_bin DEFAULT NULL,
            `email` varchar(32) COLLATE utf8_bin NOT NULL,
            `group_main` mediumint(4) unsigned NOT NULL,
            `groups` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0',
            `name_colour` varchar(255) COLLATE utf8_bin DEFAULT NULL DEFAULT '[0]',
            `register_ip` varchar(16) COLLATE utf8_bin NOT NULL,
            `last_ip` varchar(16) COLLATE utf8_bin NOT NULL,
            `usertitle` varchar(64) COLLATE utf8_bin NOT NULL,
            `profile_md` text COLLATE utf8_bin,
            `avatar_url` varchar(255) COLLATE utf8_bin NOT NULL,
            `background_url` varchar(255) COLLATE utf8_bin NOT NULL,
            `regdate` int(16) unsigned NOT NULL DEFAULT '0',
            `lastdate` int(16) unsigned NOT NULL DEFAULT '0',
            `lastunamechange` int(16) unsigned NOT NULL DEFAULT '0',
            `birthday` varchar(16) COLLATE utf8_bin DEFAULT NULL,
            `profile_data` text COLLATE utf8_bin NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username_clean` (`username_clean`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Create warnings table
        DB::prepare("CREATE TABLE `{prefix}warnings` (
            `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT,
            `uid` bigint(128) unsigned NOT NULL,
            `mod` bigint(128) unsigned NOT NULL,
            `issued` int(64) unsigned NOT NULL,
            `expire` int(64) unsigned NOT NULL,
            `reason` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason for the warning.',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();
    }

    public function down()
    {
        // Drop API keys table
        DB::prepare("DROP TABLE `{prefix}apikeys`")
            ->execute();

        // Drop bans table
        DB::prepare("DROP TABLE `{prefix}bans`")
            ->execute();

        // Drop config table
        DB::prepare("DROP TABLE `{prefix}config`")
            ->execute();

        // Drop groups table
        DB::prepare("DROP TABLE `{prefix}groups`")
            ->execute();

        // Drop messages table
        DB::prepare("DROP TABLE `{prefix}messages`")
            ->execute();

        // Drop news table
        DB::prepare("DROP TABLE `{prefix}news`")
            ->execute();

        // Drop registration codes table
        DB::prepare("DROP TABLE `{prefix}regcodes`")
            ->execute();

        // Drop sessions table
        DB::prepare("DROP TABLE `{prefix}sessions`")
            ->execute();

        // Drop users table
        DB::prepare("DROP TABLE `{prefix}users`")
            ->execute();

        // Drop warnings table
        DB::prepare("DROP TABLE `{prefix}warnings`")
            ->execute();
    }
}
