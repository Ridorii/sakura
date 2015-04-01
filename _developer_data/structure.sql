-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `fii_apikeys`;
CREATE TABLE `fii_apikeys` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `owner` bigint(128) unsigned NOT NULL COMMENT 'ID of user that owns this API key.',
  `apikey` varchar(32) COLLATE utf8_bin NOT NULL COMMENT 'The API key.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_bans`;
CREATE TABLE `fii_bans` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `uid` bigint(128) unsigned NOT NULL COMMENT 'ID of user that was banned, 0 for just an IP ban.',
  `ip` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IP to disallow access to the site.',
  `type` tinyint(1) unsigned NOT NULL COMMENT 'The type of ban that should be enforced.',
  `timestamp` int(64) unsigned NOT NULL COMMENT 'Timestamp when the user was banned.',
  `bannedtill` int(64) unsigned NOT NULL COMMENT 'Timestamp when the user should regain access to the site.',
  `modid` bigint(128) unsigned NOT NULL COMMENT 'ID of moderator that banned this user,',
  `modip` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IP of moderator that banned this user.',
  `reason` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason given for the ban.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_config`;
CREATE TABLE `fii_config` (
  `config_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Array key for configuration value',
  `config_value` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The value, obviously.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_groups`;
CREATE TABLE `fii_groups` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `groupname` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display name of the group.',
  `multi` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Can this group name have an s at the end?',
  `colour` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Colour used for the username of a member of this group.',
  `description` text COLLATE utf8_bin NOT NULL COMMENT 'A description of what a user in this group can do/is supposed to do.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_infopages`;
CREATE TABLE `fii_infopages` (
  `shorthand` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Name used for calling this page up in the /r/URL',
  `pagetitle` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title displayed on the top of the page',
  `content` text COLLATE utf8_bin NOT NULL COMMENT 'Content of the page'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_messages`;
CREATE TABLE `fii_messages` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `fromUser` bigint(128) unsigned NOT NULL COMMENT 'ID of the user that sent this message.',
  `toUsers` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IDs of users that should "receive" this message.',
  `readBy` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IDs of users who read this message.',
  `deletedBy` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IDs of users who deleted this message, if all are set to true delete database entry.',
  `date` int(64) unsigned NOT NULL COMMENT 'Timestamp of the time this message was sent',
  `title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title of the message',
  `content` text COLLATE utf8_bin NOT NULL COMMENT 'Contents of the message.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_news`;
CREATE TABLE `fii_news` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `uid` bigint(128) unsigned NOT NULL COMMENT 'ID of user who posted this news message.',
  `date` int(64) unsigned NOT NULL COMMENT 'News post timestamp.',
  `title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title of the post.',
  `content` text COLLATE utf8_bin NOT NULL COMMENT 'Contents of the post',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_regcodes`;
CREATE TABLE `fii_regcodes` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `code` varchar(32) COLLATE utf8_bin NOT NULL COMMENT 'Randomly generated registration key.',
  `created_by` bigint(128) unsigned NOT NULL COMMENT 'ID of user who generated this code.',
  `used_by` bigint(128) unsigned NOT NULL COMMENT 'ID of user who used this code.',
  `key_used` tinyint(1) unsigned NOT NULL COMMENT 'Boolean for setting this key as used.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_sessions`;
CREATE TABLE `fii_sessions` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management. ',
  `userip` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IP of the user this session is spawned for.',
  `useragent` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'User agent of the user this session is spawned for.',
  `userid` bigint(128) unsigned NOT NULL COMMENT 'ID of the user this session is spawned for. ',
  `skey` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Session key, allow direct access to the user''s account. ',
  `started` int(64) unsigned NOT NULL COMMENT 'The timestamp for when the session was started. ',
  `expire` int(64) unsigned NOT NULL COMMENT 'The timestamp for when this session should end, -1 for permanent. ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_users`;
CREATE TABLE `fii_users` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management. ',
  `username` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Username set at registration.',
  `username_clean` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'A more cleaned up version of the username for backend usage.',
  `password_hash` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Hashing algo used for the password hash.',
  `password_salt` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Salt used for the password hash.',
  `password_algo` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Algorithm used for the password hash.',
  `password_iter` int(16) unsigned NOT NULL COMMENT 'Password hash iterations.',
  `password_chan` int(16) unsigned NOT NULL COMMENT 'Last time the user changed their password.',
  `password_new` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Field with array containing new password data beit that they requested a password change.',
  `email` varchar(32) COLLATE utf8_bin NOT NULL COMMENT 'E-mail of the user for password restoring etc.',
  `group_main` mediumint(4) unsigned NOT NULL COMMENT 'Main usergroup of the user.',
  `groups` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Array containing the groups the user is in.',
  `name_colour` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Additional name colour, when empty colour defaults to group colour.',
  `register_ip` varchar(16) COLLATE utf8_bin NOT NULL COMMENT 'IP used for the creation of this account.',
  `last_ip` varchar(16) COLLATE utf8_bin NOT NULL COMMENT 'Last IP that was used to log into this account.',
  `usertitle` varchar(64) COLLATE utf8_bin DEFAULT NULL COMMENT 'Custom user title of the user, when empty reverts to their derault group name.',
  `profile_md` text COLLATE utf8_bin COMMENT 'Markdown customise page thing on the profile of the user.',
  `avatar_url` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Full url to the user''s avatar.',
  `background_url` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Full url to the user''s profile background.',
  `regdate` int(16) unsigned NOT NULL COMMENT 'Timestamp of account creation.',
  `lastdate` int(16) unsigned NOT NULL COMMENT 'Last time anything was done on this account.',
  `lastunamechange` int(16) unsigned NOT NULL COMMENT 'Last username change.',
  `birthday` varchar(16) COLLATE utf8_bin DEFAULT NULL COMMENT 'Birthdate of the user.',
  `profile_data` text COLLATE utf8_bin NOT NULL COMMENT 'Modular array containing profile data.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_clean` (`username_clean`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_warnings`;
CREATE TABLE `fii_warnings` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `uid` bigint(128) unsigned NOT NULL COMMENT 'ID of user that was warned.',
  `mod` bigint(128) unsigned NOT NULL COMMENT 'ID of the moderator that issued the warning.',
  `issued` int(64) unsigned NOT NULL COMMENT 'Timestamp of the date the warning was issued.',
  `expire` int(64) unsigned NOT NULL COMMENT 'Timstamp when the warning should expire, -1 for a permanent warning.',
  `reason` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason for the warning.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-- 2015-04-01 17:26:49
