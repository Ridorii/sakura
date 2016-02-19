<?php
use Sakura\Migration\IMigration;
use Sakura\DB;

class FaqForumAndSocks implements IMigration
{
    public function up()
    {
        // Add faq table
        DB::prepare("CREATE TABLE `{prefix}faq` (
            `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT,
            `short` varchar(255) COLLATE utf8_bin NOT NULL,
            `question` varchar(255) COLLATE utf8_bin NOT NULL,
            `answer` text COLLATE utf8_bin NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Add forums table
        DB::prepare("CREATE TABLE `{prefix}forums` (
            `forum_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
            `forum_name` varchar(255) COLLATE utf8_bin NOT NULL,
            `forum_desc` text COLLATE utf8_bin NOT NULL,
            `forum_link` varchar(255) COLLATE utf8_bin NOT NULL,
            `forum_category` bigint(255) unsigned NOT NULL DEFAULT '0',
            `forum_type` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `forum_posts` bigint(128) unsigned NOT NULL DEFAULT '0',
            `forum_topics` bigint(255) unsigned NOT NULL DEFAULT '0',
            `forum_last_post_id` bigint(255) unsigned NOT NULL DEFAULT '0',
            `forum_last_poster_id` bigint(255) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`forum_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Add posts table
        DB::prepare("CREATE TABLE `{prefix}posts` (
            `post_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
            `topic_id` bigint(255) unsigned NOT NULL DEFAULT '0',
            `forum_id` bigint(255) unsigned NOT NULL DEFAULT '0',
            `poster_id` bigint(255) unsigned NOT NULL DEFAULT '0',
            `poster_ip` varchar(40) COLLATE utf8_bin NOT NULL,
            `post_time` int(11) unsigned NOT NULL DEFAULT '0',
            `enable_markdown` tinyint(1) unsigned NOT NULL DEFAULT '1',
            `enable_sig` tinyint(1) unsigned NOT NULL DEFAULT '1',
            `post_subject` varchar(255) COLLATE utf8_bin NOT NULL,
            `post_text` text COLLATE utf8_bin NOT NULL,
            `post_edit_time` int(11) unsigned NOT NULL DEFAULT '0',
            `post_edit_reason` varchar(255) COLLATE utf8_bin NOT NULL,
            `post_edit_user` int(255) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`post_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Add sock_perms table
        DB::prepare("CREATE TABLE `{prefix}sock_perms` (
            `rid` bigint(128) unsigned NOT NULL DEFAULT '0',
            `uid` bigint(255) unsigned NOT NULL DEFAULT '0',
            `perms` varchar(128) COLLATE utf8_bin NOT NULL DEFAULT '1,0,0,0,0,0'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Add topics table
        DB::prepare("CREATE TABLE `{prefix}topics` (
            `topic_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
            `forum_id` bigint(255) unsigned NOT NULL DEFAULT '0',
            `topic_hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
            `topic_title` varchar(255) COLLATE utf8_bin NOT NULL,
            `topic_time` int(11) unsigned NOT NULL DEFAULT '0',
            `topic_time_limit` int(11) unsigned NOT NULL DEFAULT '0',
            `topic_last_reply` int(11) unsigned NOT NULL DEFAULT '0',
            `topic_views` bigint(64) unsigned NOT NULL DEFAULT '0',
            `topic_replies` bigint(128) unsigned NOT NULL DEFAULT '0',
            `topic_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
            `topic_status_change` int(11) unsigned NOT NULL DEFAULT '0',
            `topic_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
            `topic_first_post_id` bigint(255) unsigned NOT NULL DEFAULT '0',
            `topic_first_poster_id` bigint(255) unsigned NOT NULL DEFAULT '0',
            `topic_last_post_id` bigint(255) unsigned NOT NULL DEFAULT '0',
            `topic_last_poster_id` bigint(255) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`topic_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin")
        ->execute();

        // Change mod to iid in warnings table
        DB::prepare('ALTER TABLE `{prefix}warnings` CHANGE `mod` `iid` bigint(128)')
            ->execute();
    }

    public function down()
    {
        // Drop the faq table
        DB::prepare("DROP TABLE `{prefix}faq`")->execute();

        // Drop the forums table
        DB::prepare("DROP TABLE `{prefix}forums`")->execute();

        // Drop the posts table
        DB::prepare("DROP TABLE `{prefix}posts`")->execute();

        // Drop the sock_perms table
        DB::prepare("DROP TABLE `{prefix}sock_perms`")->execute();

        // Drop the topics table
        DB::prepare("DROP TABLE `{prefix}topics`")->execute();

        // Change iid to mod in warnings table
        DB::prepare('ALTER TABLE `{prefix}warnings` CHANGE `iid` `mod` bigint(128)')
            ->execute();
    }
}
