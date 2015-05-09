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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


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

TRUNCATE `fii_config`;
INSERT INTO `fii_config` (`config_name`, `config_value`) VALUES
('recaptcha_public',	''),
('recaptcha_private',	''),
('charset',	'utf-8'),
('cookie_prefix',	'sakura_'),
('cookie_domain',	'yourdomain.com'),
('cookie_path',	'/'),
('site_style',	'yuuno'),
('manage_style',	'broomcloset'),
('allow_registration',	'0'),
('smtp_server',	''),
('smtp_auth',	'1'),
('smtp_secure',	''),
('smtp_port',	''),
('smtp_username',	''),
('smtp_password',	''),
('smtp_replyto_mail',	''),
('smtp_replyto_name',	''),
('smtp_from_email',	''),
('smtp_from_name',	''),
('sitename',	'Sakura'),
('recaptcha',	'1'),
('require_activation',	'1'),
('require_registration_code',	'0'),
('disable_registration',	'0'),
('max_reg_keys',	'5'),
('mail_signature',	'Team Flashii'),
('lock_authentication',	'0'),
('min_entropy',	'96');

DROP TABLE IF EXISTS `fii_faq`;
CREATE TABLE `fii_faq` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `short` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Used for linking directly to a question.',
  `question` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The question.',
  `answer` text COLLATE utf8_bin NOT NULL COMMENT 'The answer.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_forums`;
CREATE TABLE `fii_forums` (
  `forum_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `forum_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display name of the forum.',
  `forum_desc` text COLLATE utf8_bin NOT NULL COMMENT 'Description of the forum.',
  `forum_link` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'If set forum will display as a link.',
  `forum_category` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the category this forum falls under.',
  `forum_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Forum type, 0 for regular board, 1 for category and 2 for link.',
  `forum_posts` bigint(128) unsigned NOT NULL DEFAULT '0' COMMENT 'Post count of the forum',
  `forum_topics` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'Topic count of the forum.',
  `forum_last_post_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of last post in forum.',
  `forum_last_poster_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of last poster in forum.',
  `forum_icon` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display icon for the forum.',
  PRIMARY KEY (`forum_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


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


DROP TABLE IF EXISTS `fii_notifications`;
CREATE TABLE `fii_notifications` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `uid` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID this notification is intended for.',
  `timestamp` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp when this notification was created.',
  `notif_read` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Toggle for unread and read.',
  `notif_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title displayed on the notification.',
  `notif_text` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Text displayed.',
  `notif_link` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Link (empty for no link).',
  `notif_img` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Image path, prefix with font: to use a font class instead of an image.',
  `notif_timeout` int(16) unsigned NOT NULL DEFAULT '0' COMMENT 'How long the notification should stay on screen in milliseconds, 0 for forever.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_posts`;
CREATE TABLE `fii_posts` (
  `post_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `topic_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of topic this post is a part of.',
  `forum_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of forum this was posted in.',
  `poster_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of poster of this post.',
  `poster_ip` varchar(40) COLLATE utf8_bin NOT NULL COMMENT 'IP of poster.',
  `post_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Time this post was made.',
  `enable_markdown` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Toggle if markdown should be enabled.',
  `enable_sig` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Toggle if signature should be shown.',
  `post_subject` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Subject of the post.',
  `post_text` text COLLATE utf8_bin NOT NULL COMMENT 'Contents of the post.',
  `post_edit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Time this post was last edited.',
  `post_edit_reason` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Reason this was edited.',
  `post_edit_user` int(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of user that edited.',
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


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
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_sock_perms`;
CREATE TABLE `fii_sock_perms` (
  `rid` bigint(128) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of rank that this permission counts for (set to 0 if user).',
  `uid` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the user this permission counts for (set to 0 if rank).',
  `perms` varchar(128) COLLATE utf8_bin NOT NULL DEFAULT '1,0,0,0,0,0' COMMENT 'Permission data (has access, in-chat rank, user type, log access, nick access, channel creation)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

TRUNCATE `fii_sock_perms`;
INSERT INTO `fii_sock_perms` (`rid`, `uid`, `perms`) VALUES
(1,	0,	'0,0,0,0,0,0'),
(2,	0,	'1,0,0,0,0,0'),
(3,	0,	'1,3,1,1,1,1'),
(4,	0,	'1,4,2,1,1,2'),
(5,	0,	'1,2,1,1,1,1'),
(6,	0,	'1,0,0,0,0,0'),
(7,	0,	'1,2,1,1,1,1'),
(8,	0,	'1,1,0,1,1,1'),
(9,	0,	'1,1,0,1,1,1'),
(0,	1,	'1,945,1,1,1,2');

DROP TABLE IF EXISTS `fii_tenshi`;
CREATE TABLE `fii_tenshi` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `startdate` int(64) unsigned NOT NULL COMMENT 'Purchase timestamp.',
  `uid` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that purchased Tenshi.',
  `expiredate` int(64) unsigned NOT NULL COMMENT 'Expiration timestamp.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_topics`;
CREATE TABLE `fii_topics` (
  `topic_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `forum_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of forum this topic was created in.',
  `topic_hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Boolean to set the topic as hidden.',
  `topic_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title of the topic.',
  `topic_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp when the topic was created.',
  `topic_time_limit` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'After how long a topic should be locked.',
  `topic_last_reply` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Last time a post was posted in this topic.',
  `topic_views` bigint(64) unsigned NOT NULL DEFAULT '0' COMMENT 'Amount of times the topic has been viewed.',
  `topic_replies` bigint(128) unsigned NOT NULL DEFAULT '0' COMMENT 'Amount of replies the topic has.',
  `topic_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Status of topic.',
  `topic_status_change` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Date the topic status was changed (used for deletion cooldown as well).',
  `topic_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Type of the topic.',
  `topic_first_post_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of first post made in this topic.',
  `topic_first_poster_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID of person who made the first post.',
  `topic_last_post_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of last post made in this topic.',
  `topic_last_poster_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID of person who made the last post.',
  PRIMARY KEY (`topic_id`)
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
  `rank_main` mediumint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Main rank of the user.',
  `ranks` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '[0]' COMMENT 'Array containing the ranks the user is part of.',
  `name_colour` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Additional name colour, when empty colour defaults to group colour.',
  `register_ip` varchar(16) COLLATE utf8_bin NOT NULL COMMENT 'IP used for the creation of this account.',
  `last_ip` varchar(16) COLLATE utf8_bin NOT NULL COMMENT 'Last IP that was used to log into this account.',
  `usertitle` varchar(64) COLLATE utf8_bin DEFAULT NULL COMMENT 'Custom user title of the user, when empty reverts to their derault group name.',
  `profile_md` text COLLATE utf8_bin COMMENT 'Markdown customise page thing on the profile of the user.',
  `avatar_url` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Full url to the user''s avatar.',
  `background_url` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Full url to the user''s profile background.',
  `regdate` int(16) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp of account creation.',
  `lastdate` int(16) unsigned NOT NULL DEFAULT '0' COMMENT 'Last time anything was done on this account.',
  `lastunamechange` int(16) unsigned NOT NULL DEFAULT '0' COMMENT 'Last username change.',
  `birthday` varchar(16) COLLATE utf8_bin DEFAULT NULL COMMENT 'Birthdate of the user.',
  `posts` int(16) unsigned NOT NULL DEFAULT '0' COMMENT 'Amount of posts the user has made on the forum.',
  `country` varchar(4) COLLATE utf8_bin NOT NULL COMMENT 'Contains ISO 3166 country code of user''s registration location.',
  `profile_data` text COLLATE utf8_bin NOT NULL COMMENT 'Modular array containing profile data.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_clean` (`username_clean`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `fii_warnings`;
CREATE TABLE `fii_warnings` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `uid` bigint(128) unsigned NOT NULL COMMENT 'ID of user that was warned.',
  `iid` bigint(128) unsigned NOT NULL COMMENT 'ID of the user that issued the warning.',
  `issued` int(64) unsigned NOT NULL COMMENT 'Timestamp of the date the warning was issued.',
  `expire` int(64) unsigned NOT NULL COMMENT 'Timstamp when the warning should expire, 0 for a permanent warning.',
  `reason` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason for the warning.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-- 2015-05-08 17:47:36
