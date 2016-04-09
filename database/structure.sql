-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.10-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table sakura-development.sakura_actioncodes
CREATE TABLE IF NOT EXISTS `sakura_actioncodes` (
  `code_action` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Action identifier so the backend knows what to do.',
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that would be affected by this action',
  `action_code` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The URL key for using this code.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_comments
CREATE TABLE IF NOT EXISTS `sakura_comments` (
  `comment_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `comment_category` varchar(32) COLLATE utf8_bin NOT NULL COMMENT 'Comment category.',
  `comment_timestamp` int(11) unsigned NOT NULL COMMENT 'Timestamp of when this comment was posted.',
  `comment_poster` bigint(255) unsigned NOT NULL COMMENT 'User ID of the poster.',
  `comment_reply_to` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the comment this comment is a reply to',
  `comment_text` text COLLATE utf8_bin NOT NULL COMMENT 'Content of the comment.',
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_comment_votes
CREATE TABLE IF NOT EXISTS `sakura_comment_votes` (
  `vote_comment` bigint(255) unsigned NOT NULL COMMENT 'ID of the comment that was voted on.',
  `vote_user` bigint(255) unsigned NOT NULL COMMENT 'ID of the voter.',
  `vote_state` tinyint(1) unsigned NOT NULL COMMENT '0 = dislike, 1 = like.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_config
CREATE TABLE IF NOT EXISTS `sakura_config` (
  `config_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Array key for configuration value',
  `config_value` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The value, obviously.',
  UNIQUE KEY `config_name` (`config_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_emoticons
CREATE TABLE IF NOT EXISTS `sakura_emoticons` (
  `emote_string` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'String to catch and replace',
  `emote_path` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Path to the image file relative to the content domain.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_error_log
CREATE TABLE IF NOT EXISTS `sakura_error_log` (
  `error_id` varchar(32) COLLATE utf8_bin NOT NULL COMMENT 'An ID that is created when an error occurs.',
  `error_timestamp` varchar(128) COLLATE utf8_bin NOT NULL COMMENT 'A datestring from when the error occurred.',
  `error_revision` int(16) unsigned NOT NULL COMMENT 'Sakura Revision number.',
  `error_type` int(16) unsigned NOT NULL COMMENT 'The PHP error type of this error.',
  `error_line` int(32) unsigned NOT NULL COMMENT 'The line that caused this error.',
  `error_string` varchar(512) COLLATE utf8_bin NOT NULL COMMENT 'PHP''s description of this error.',
  `error_file` varchar(512) COLLATE utf8_bin NOT NULL COMMENT 'The file in which this error occurred.',
  `error_backtrace` text COLLATE utf8_bin NOT NULL COMMENT 'A full base64 and json encoded backtrace containing all environment data.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_faq
CREATE TABLE IF NOT EXISTS `sakura_faq` (
  `faq_id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `faq_shorthand` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Used for linking directly to a question.',
  `faq_question` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The question.',
  `faq_answer` text COLLATE utf8_bin NOT NULL COMMENT 'The answer.',
  PRIMARY KEY (`faq_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_forums
CREATE TABLE IF NOT EXISTS `sakura_forums` (
  `forum_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `forum_order` bigint(255) unsigned NOT NULL COMMENT 'Forum sorting order.',
  `forum_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display name of the forum.',
  `forum_desc` text COLLATE utf8_bin NOT NULL COMMENT 'Description of the forum.',
  `forum_link` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'If set forum will display as a link.',
  `forum_category` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the category this forum falls under.',
  `forum_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Forum type, 0 for regular board, 1 for category and 2 for link.',
  `forum_icon` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display icon for the forum.',
  PRIMARY KEY (`forum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_forum_permissions
CREATE TABLE IF NOT EXISTS `sakura_forum_permissions` (
  `forum_id` bigint(255) unsigned NOT NULL COMMENT 'Forum ID',
  `rank_id` bigint(128) unsigned NOT NULL COMMENT 'Rank ID, leave 0 for a user',
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'User ID, leave 0 for a rank',
  `forum_perms` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Forum action permission string'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_friends
CREATE TABLE IF NOT EXISTS `sakura_friends` (
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that added the friend.',
  `friend_id` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that was added as a friend.',
  `friend_timestamp` int(11) unsigned NOT NULL COMMENT 'Timestamp of action.',
  KEY `uid` (`user_id`),
  KEY `fid` (`friend_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_infopages
CREATE TABLE IF NOT EXISTS `sakura_infopages` (
  `page_shorthand` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Name used for calling this page up in the /r/URL',
  `page_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title displayed on the top of the page',
  `page_content` text COLLATE utf8_bin NOT NULL COMMENT 'Content of the page'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_login_attempts
CREATE TABLE IF NOT EXISTS `sakura_login_attempts` (
  `attempt_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `attempt_success` tinyint(1) unsigned NOT NULL COMMENT 'Success boolean.',
  `attempt_timestamp` int(11) unsigned NOT NULL COMMENT 'Unix timestamp of the event.',
  `attempt_ip` varbinary(50) NOT NULL COMMENT 'IP that made this attempt.',
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that was attempted to log in to.',
  PRIMARY KEY (`attempt_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_news
CREATE TABLE IF NOT EXISTS `sakura_news` (
  `news_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `news_category` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Category ID.',
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'ID of user who posted this news message.',
  `news_timestamp` int(11) unsigned NOT NULL COMMENT 'News post timestamp.',
  `news_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title of the post.',
  `news_content` text COLLATE utf8_bin NOT NULL COMMENT 'Contents of the post',
  PRIMARY KEY (`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_notifications
CREATE TABLE IF NOT EXISTS `sakura_notifications` (
  `alert_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `user_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID this notification is intended for.',
  `alert_timestamp` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp when this notification was created.',
  `alert_read` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Toggle for unread and read.',
  `alert_sound` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Toggle if a sound should be played upon receiving the notification.',
  `alert_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title displayed on the notification.',
  `alert_text` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Text displayed.',
  `alert_link` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Link (empty for no link).',
  `alert_img` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Image path, prefix with font: to use a font class instead of an image.',
  `alert_timeout` int(16) unsigned NOT NULL DEFAULT '0' COMMENT 'How long the notification should stay on screen in milliseconds, 0 for forever.',
  PRIMARY KEY (`alert_id`),
  KEY `uid` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_optionfields
CREATE TABLE IF NOT EXISTS `sakura_optionfields` (
  `option_id` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Unique identifier for accessing this option.',
  `option_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Description of the field in a proper way.',
  `option_description` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Longer description of the option.',
  `option_type` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Type attribute in the input element.',
  `option_permission` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The minimum permission level this option requires.',
  UNIQUE KEY `id` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_permissions
CREATE TABLE IF NOT EXISTS `sakura_permissions` (
  `rank_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the rank this permissions set is used for.',
  `user_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the user this permissions set is used for.',
  `permissions_site` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0' COMMENT 'Site permissions.',
  `permissions_manage` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0' COMMENT 'Site management permissions'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_posts
CREATE TABLE IF NOT EXISTS `sakura_posts` (
  `post_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `topic_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of topic this post is a part of.',
  `forum_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of forum this was posted in.',
  `poster_id` bigint(255) unsigned DEFAULT '0' COMMENT 'ID of poster of this post.',
  `poster_ip` varchar(40) COLLATE utf8_bin NOT NULL COMMENT 'IP of poster.',
  `post_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Time this post was made.',
  `post_subject` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Subject of the post.',
  `post_text` text COLLATE utf8_bin NOT NULL COMMENT 'Contents of the post.',
  `post_edit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Time this post was last edited.',
  `post_edit_reason` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason this was edited.',
  `post_edit_user` int(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of user that edited.',
  PRIMARY KEY (`post_id`),
  KEY `topic_id` (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `poster_id` (`poster_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_premium
CREATE TABLE IF NOT EXISTS `sakura_premium` (
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that purchased Tenshi.',
  `premium_start` int(11) unsigned NOT NULL COMMENT 'Timestamp of first purchase.',
  `premium_expire` int(11) unsigned NOT NULL COMMENT 'Expiration timestamp.',
  UNIQUE KEY `uid` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_premium_log
CREATE TABLE IF NOT EXISTS `sakura_premium_log` (
  `transaction_id` int(16) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'User ID of purchaser',
  `transaction_amount` float NOT NULL COMMENT 'Amount that was transferred.',
  `transaction_date` int(11) unsigned NOT NULL COMMENT 'Date when the purchase was made.',
  `transaction_comment` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'A short description of the action taken.',
  PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_profilefields
CREATE TABLE IF NOT EXISTS `sakura_profilefields` (
  `field_id` int(64) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID used for ordering on the userpage.',
  `field_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Name of the field.',
  `field_type` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Type attribute in the input element.',
  `field_link` tinyint(1) unsigned NOT NULL COMMENT 'Set if this value should be put in a href.',
  `field_linkformat` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'If the form is a link how should it be formatted? {{ VAL }} gets replace with the value.',
  `field_description` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Description of the field displayed in the control panel.',
  `field_additional` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Undocumented JSON array containing special options if needed (probably only going to be used for the YouTube field).',
  PRIMARY KEY (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_ranks
CREATE TABLE IF NOT EXISTS `sakura_ranks` (
  `rank_id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `rank_hierarchy` int(11) unsigned NOT NULL COMMENT 'Rank hierarchy.',
  `rank_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display name of the rank.',
  `rank_multiple` varchar(10) COLLATE utf8_bin DEFAULT NULL COMMENT 'Used when addressing this rank as a multiple',
  `rank_hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Don''t show any public links to this rank.',
  `rank_colour` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Colour used for the username of a member of this rank.',
  `rank_description` text COLLATE utf8_bin NOT NULL COMMENT 'A description of what a user of this rank can do/is supposed to do.',
  `rank_title` varchar(64) COLLATE utf8_bin NOT NULL COMMENT 'Default user title if user has none set.',
  PRIMARY KEY (`rank_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_reports
CREATE TABLE IF NOT EXISTS `sakura_reports` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `type` int(32) unsigned NOT NULL COMMENT 'Report type, entirely handled on the script side.',
  `issuer` bigint(255) unsigned NOT NULL COMMENT 'ID of the person who issued this report.',
  `subject` bigint(255) unsigned NOT NULL COMMENT 'ID pointing out what was reported (a more accurate description isn''t possible due to the type column).',
  `title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'A quick description of this report.',
  `description` text COLLATE utf8_bin NOT NULL COMMENT 'And a detailed description.',
  `reviewed` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the moderator that reviewed this report.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_sessions
CREATE TABLE IF NOT EXISTS `sakura_sessions` (
  `session_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management. ',
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'ID of the user this session is spawned for. ',
  `user_ip` varbinary(50) NOT NULL COMMENT 'IP of the user this session is spawned for.',
  `user_agent` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'User agent of the user this session is spawned for.',
  `session_key` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Session key, allow direct access to the user''s account. ',
  `session_start` int(16) unsigned NOT NULL COMMENT 'The timestamp for when the session was started. ',
  `session_expire` int(16) unsigned NOT NULL COMMENT 'The timestamp for when this session should end, -1 for permanent. ',
  `session_remember` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'If set to 1 session will be extended each time a page is loaded.',
  PRIMARY KEY (`session_id`),
  KEY `userid` (`user_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_topics
CREATE TABLE IF NOT EXISTS `sakura_topics` (
  `topic_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `forum_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of forum this topic was created in.',
  `topic_hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Boolean to set the topic as hidden.',
  `topic_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title of the topic.',
  `topic_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp when the topic was created.',
  `topic_time_limit` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'After how long a topic should be locked.',
  `topic_views` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'Amount of times the topic has been viewed.',
  `topic_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Status of topic.',
  `topic_status_change` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Date the topic status was changed (used for deletion cooldown as well).',
  `topic_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Type of the topic.',
  `topic_last_reply` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp of when the last reply made to this thread.',
  `topic_old_forum` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'Pre-move forum id.',
  PRIMARY KEY (`topic_id`),
  KEY `forum_id` (`forum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_topics_track
CREATE TABLE IF NOT EXISTS `sakura_topics_track` (
  `user_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the user this row applies to.',
  `topic_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the thread in question.',
  `forum_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the forum in question.',
  `mark_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp of the event.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_uploads
CREATE TABLE IF NOT EXISTS `sakura_uploads` (
  `file_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated value for management',
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that uploaded the file',
  `file_data` longblob NOT NULL COMMENT 'Contents of the file',
  `file_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Name of the file',
  `file_mime` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Static mime type of the file',
  `file_time` int(11) unsigned NOT NULL COMMENT 'Timestamp of when the file was uploaded',
  `file_expire` int(11) unsigned NOT NULL COMMENT 'When should the file be removed, 0 for never',
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_username_history
CREATE TABLE IF NOT EXISTS `sakura_username_history` (
  `change_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifier',
  `change_time` int(11) unsigned NOT NULL COMMENT 'Timestamp of change',
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'User ID',
  `username_new` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'New username',
  `username_new_clean` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Clean new username',
  `username_old` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Old username',
  `username_old_clean` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Clean old username',
  PRIMARY KEY (`change_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_users
CREATE TABLE IF NOT EXISTS `sakura_users` (
  `user_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management. ',
  `username` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Username set at registration.',
  `username_clean` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'A more cleaned up version of the username for backend usage.',
  `password_hash` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Hashing algo used for the password hash.',
  `password_salt` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Salt used for the password hash.',
  `password_algo` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Algorithm used for the password hash.',
  `password_iter` int(11) unsigned NOT NULL COMMENT 'Password hash iterations.',
  `password_chan` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Last time the user changed their password.',
  `email` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'E-mail of the user for password restoring etc.',
  `rank_main` mediumint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Main rank of the user.',
  `user_colour` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Additional name colour, when empty colour defaults to group colour.',
  `register_ip` varbinary(50) NOT NULL COMMENT 'IP used for the creation of this account.',
  `last_ip` varbinary(50) NOT NULL COMMENT 'Last IP that was used to log into this account.',
  `user_title` varchar(64) COLLATE utf8_bin DEFAULT NULL COMMENT 'Custom user title of the user, when empty reverts to their derault group name.',
  `user_registered` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp of account creation.',
  `user_last_online` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Last time anything was done on this account.',
  `user_birthday` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Birthdate of the user.',
  `user_country` char(2) COLLATE utf8_bin NOT NULL DEFAULT 'XX' COMMENT 'Contains ISO 3166 country code of user''s registration location.',
  `user_avatar` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the avatar in the uploads table.',
  `user_background` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the background in the uploads table.',
  `user_header` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the profile header in the uploads table.',
  `user_page` longtext COLLATE utf8_bin COMMENT 'Contents of the userpage.',
  `user_signature` text COLLATE utf8_bin COMMENT 'Signature displayed below forum posts.',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username_clean` (`username_clean`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_user_optionfields
CREATE TABLE IF NOT EXISTS `sakura_user_optionfields` (
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'User this field applies to',
  `field_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Identifier of the field',
  `field_value` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Value of the field'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_user_profilefields
CREATE TABLE IF NOT EXISTS `sakura_user_profilefields` (
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'User this field applies to',
  `field_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Identifier of the field',
  `field_value` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Value of the field'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_user_ranks
CREATE TABLE IF NOT EXISTS `sakura_user_ranks` (
  `user_id` bigint(255) unsigned NOT NULL,
  `rank_id` bigint(128) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table sakura-development.sakura_warnings
CREATE TABLE IF NOT EXISTS `sakura_warnings` (
  `warning_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `user_id` bigint(255) unsigned NOT NULL COMMENT 'ID of user that was warned.',
  `moderator_id` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that issued the warning.',
  `warning_issued` int(16) unsigned NOT NULL COMMENT 'Timestamp of the date the warning was issued.',
  `warning_expires` int(16) unsigned NOT NULL COMMENT 'Timstamp when the warning should expire, 0 for a permanent warning.',
  `warning_action` tinyint(1) unsigned DEFAULT NULL COMMENT 'Action taken.',
  `warning_reason` varchar(512) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason for the warning.',
  PRIMARY KEY (`warning_id`),
  KEY `uid` (`user_id`),
  KEY `iid` (`moderator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
