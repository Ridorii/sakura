-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `flashiidev`;

DROP TABLE IF EXISTS `fii_actioncodes`;
CREATE TABLE `fii_actioncodes` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `action` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Action identifier so the backend knows what to do.',
  `userid` bigint(255) NOT NULL COMMENT 'ID of the user that would be affected by this action',
  `actkey` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The URL key for using this code.',
  `instruction` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Things the backend should do upon using this code',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_profilefields`;
CREATE TABLE `fii_profilefields` (
  `id` int(64) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID used for ordering on the userpage.',
  `name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Name of the field.',
  `formtype` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Type attribute in the input element.',
  `islink` tinyint(1) unsigned NOT NULL COMMENT 'Set if this value should be put in a href.',
  `linkformat` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'If the form is a link how should it be formatted? {{ VAL }} gets replace with the value.',
  `description` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Description of the field displayed in the control panel.',
  `additional` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Undocumented JSON array containing special options if needed (probably only going to be used for the YouTube field).',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

TRUNCATE `fii_profilefields`;
INSERT INTO `fii_profilefields` (`id`, `name`, `formtype`, `islink`, `linkformat`, `description`, `additional`) VALUES
(1,	'Website',	'url',	1,	'{{ VAL }}',	'URL to your website',	''),
(2,	'Twitter',	'text',	1,	'https://twitter.com/{{ VAL }}',	'Your @twitter Username',	''),
(3,	'GitHub',	'text',	1,	'https://github.com/{{ VAL }}',	'Your GitHub Username',	''),
(4,	'Skype',	'text',	1,	'skype:{{ VAL }}?userinfo',	'Your Skype Username',	''),
(5,	'YouTube',	'text',	0,	'',	'ID or Username excluding http://youtube.com/*/',	'{\"youtubetype\": [\"checkbox\", \"I <b>don\'t</b> have a Channel Username (url looks like https://www.youtube.com/channel/UCXZcw5hw5C7Neto-T_nRXBQ).\"]}'),
(6,	'SoundCloud',	'text',	1,	'https://soundcloud.com/{{ VAL }}',	'Your SoundCloud username',	''),
(7,	'Steam',	'text',	1,	'https://steamcommunity.com/id/{{ VAL }}',	'Your Steam Community Username (may differ from login username)',	''),
(8,	'osu!',	'text',	1,	'https://osu.ppy.sh/u/{{ VAL }}',	'Your osu! Username',	''),
(9,	'Origin',	'text',	0,	'',	'Your Origin User ID',	''),
(10,	'Xbox Live',	'text',	1,	'https://account.xbox.com/en-GB/Profile?Gamertag={{ VAL }}',	'Your Xbox User ID',	''),
(11,	'PSN',	'text',	1,	'http://psnprofiles.com/{{ VAL }}',	'Your PSN User ID',	'');

DROP TABLE IF EXISTS `fii_ranks`;
CREATE TABLE `fii_ranks` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display name of the rank.',
  `multi` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Can the rank name have an s at the end?',
  `colour` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Colour used for the username of a member of this rank.',
  `description` text COLLATE utf8_bin NOT NULL COMMENT 'A description of what a user of this rank can do/is supposed to do.',
  `title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Default user title if user has none set.',
  `is_premium` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Flag to set if the user group is a premium group.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

TRUNCATE `fii_ranks`;
INSERT INTO `fii_ranks` (`id`, `name`, `multi`, `colour`, `description`, `title`, `is_premium`) VALUES
(1,	'Deactivated',	0,	'#555',	'Users that are yet to be activated or that deactivated their own account.',	'Deactivated',	0),
(2,	'Regular user',	1,	'inherit',	'Regular users with regular permissions.',	'Regular user',	0),
(3,	'Site moderator',	1,	'#0A0',	'Users with special permissions like being able to ban and modify users if needed.',	'Staff',	1),
(4,	'Administrator',	1,	'#C00',	'Users that manage the server and everything around that.',	'Administrator',	1),
(5,	'Developer',	1,	'#824CA0',	'Users that either create or test new features of the site.',	'Staff',	1),
(6,	'Bot',	1,	'#9E8DA7',	'Reserved user accounts for services.',	'Bot',	0),
(7,	'Chat moderator',	1,	'#09F',	'Moderators of the chat room.',	'Staff',	1),
(8,	'Tenshi',	0,	'#EE9400',	'Users that donated $5.00 or more in order to keep the site and it\'s services alive!',	'Tenshi',	1),
(9,	'Alumnii',	0,	'#FF69B4',	'People who have contributed to the community but have moved on or resigned.',	'Alumnii',	1);

DROP TABLE IF EXISTS `fii_regcodes`;
CREATE TABLE `fii_regcodes` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `code` varchar(32) COLLATE utf8_bin NOT NULL COMMENT 'Randomly generated registration key.',
  `created_by` bigint(128) unsigned NOT NULL COMMENT 'ID of user who generated this code.',
  `used_by` bigint(128) unsigned NOT NULL COMMENT 'ID of user who used this code.',
  `key_used` tinyint(1) unsigned NOT NULL COMMENT 'Boolean for setting this key as used.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_sessions`;
CREATE TABLE `fii_sessions` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management. ',
  `userip` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IP of the user this session is spawned for.',
  `useragent` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'User agent of the user this session is spawned for.',
  `userid` bigint(128) unsigned NOT NULL COMMENT 'ID of the user this session is spawned for. ',
  `skey` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Session key, allow direct access to the user''s account. ',
  `started` int(64) unsigned NOT NULL COMMENT 'The timestamp for when the session was started. ',
  `expire` int(64) unsigned NOT NULL COMMENT 'The timestamp for when this session should end, -1 for permanent. ',
  `remember` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'If set to 1 session will be extended each time a page is loaded.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_tenshi`;
CREATE TABLE `fii_tenshi` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `startdate` int(64) unsigned NOT NULL COMMENT 'Purchase timestamp.',
  `uid` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that purchased Tenshi.',
  `expiredate` int(64) unsigned NOT NULL COMMENT 'Expiration timestamp.',
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
  `rank_main` mediumint(4) unsigned NOT NULL COMMENT 'Main rank of the user.',
  `ranks` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Array containing the ranks the user is part of.',
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
  `country` varchar(4) COLLATE utf8_bin NOT NULL COMMENT 'Contains ISO 3166 country code of user''s registration location.',
  `profile_data` text COLLATE utf8_bin NOT NULL COMMENT 'Modular array containing profile data.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_clean` (`username_clean`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


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


-- 2015-04-27 00:38:20
