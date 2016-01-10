-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 24, 2015 at 04:58 PM
-- Server version: 5.7.10-log
-- PHP Version: 7.0.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sakura-development`
--
CREATE DATABASE IF NOT EXISTS `sakura-development` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `sakura-development`;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_actioncodes`
--

DROP TABLE IF EXISTS `sakura_actioncodes`;
CREATE TABLE `sakura_actioncodes` (
  `id` bigint(255) NOT NULL COMMENT 'Automatically generated ID by MySQL for management.',
  `action` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Action identifier so the backend knows what to do.',
  `userid` bigint(255) NOT NULL COMMENT 'ID of the user that would be affected by this action',
  `actkey` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The URL key for using this code.',
  `instruction` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Things the backend should do upon using this code'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_bans`
--

DROP TABLE IF EXISTS `sakura_bans`;
CREATE TABLE `sakura_bans` (
  `ban_id` bigint(255) UNSIGNED NOT NULL COMMENT 'Automatically generated ID by MySQL for management.',
  `user_id` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of user that was banned, 0 for just an IP ban.',
  `ban_begin` int(11) UNSIGNED NOT NULL COMMENT 'Timestamp when the user was banned.',
  `ban_end` int(11) UNSIGNED NOT NULL COMMENT 'Timestamp when the user should regain access to the site.',
  `ban_reason` varchar(512) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason given for the ban.',
  `ban_moderator` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of moderator that banned this user,'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_comments`
--

DROP TABLE IF EXISTS `sakura_comments`;
CREATE TABLE `sakura_comments` (
  `comment_id` bigint(255) UNSIGNED NOT NULL COMMENT 'MySQL Generated ID used for sorting.',
  `comment_category` varchar(32) COLLATE utf8_bin NOT NULL COMMENT 'Comment category.',
  `comment_timestamp` int(11) UNSIGNED NOT NULL COMMENT 'Timestamp of when this comment was posted.',
  `comment_poster` bigint(255) UNSIGNED NOT NULL COMMENT 'User ID of the poster.',
  `comment_reply_to` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of the comment this comment is a reply to',
  `comment_text` text COLLATE utf8_bin NOT NULL COMMENT 'Content of the comment.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_comment_votes`
--

DROP TABLE IF EXISTS `sakura_comment_votes`;
CREATE TABLE `sakura_comment_votes` (
  `vote_comment` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of the comment that was voted on.',
  `vote_user` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of the voter.',
  `vote_state` tinyint(1) UNSIGNED NOT NULL COMMENT '0 = dislike, 1 = like.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_config`
--

DROP TABLE IF EXISTS `sakura_config`;
CREATE TABLE `sakura_config` (
  `config_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Array key for configuration value',
  `config_value` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The value, obviously.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_emoticons`
--

DROP TABLE IF EXISTS `sakura_emoticons`;
CREATE TABLE `sakura_emoticons` (
  `emote_string` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'String to catch and replace',
  `emote_path` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Path to the image file relative to the content domain.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_error_log`
--

DROP TABLE IF EXISTS `sakura_error_log`;
CREATE TABLE `sakura_error_log` (
  `error_id` varchar(32) COLLATE utf8_bin NOT NULL COMMENT 'An ID that is created when an error occurs.',
  `error_timestamp` varchar(128) COLLATE utf8_bin NOT NULL COMMENT 'A datestring from when the error occurred.',
  `error_revision` int(16) UNSIGNED NOT NULL COMMENT 'Sakura Revision number.',
  `error_type` int(16) UNSIGNED NOT NULL COMMENT 'The PHP error type of this error.',
  `error_line` int(32) UNSIGNED NOT NULL COMMENT 'The line that caused this error.',
  `error_string` varchar(512) COLLATE utf8_bin NOT NULL COMMENT 'PHP''s description of this error.',
  `error_file` varchar(512) COLLATE utf8_bin NOT NULL COMMENT 'The file in which this error occurred.',
  `error_backtrace` text COLLATE utf8_bin NOT NULL COMMENT 'A full base64 and json encoded backtrace containing all environment data.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_faq`
--

DROP TABLE IF EXISTS `sakura_faq`;
CREATE TABLE `sakura_faq` (
  `faq_id` bigint(128) UNSIGNED NOT NULL COMMENT 'MySQL Generated ID used for sorting.',
  `faq_shorthand` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Used for linking directly to a question.',
  `faq_question` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The question.',
  `faq_answer` text COLLATE utf8_bin NOT NULL COMMENT 'The answer.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_forums`
--

DROP TABLE IF EXISTS `sakura_forums`;
CREATE TABLE `sakura_forums` (
  `forum_id` bigint(255) UNSIGNED NOT NULL COMMENT 'MySQL Generated ID used for sorting.',
  `forum_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display name of the forum.',
  `forum_desc` text COLLATE utf8_bin NOT NULL COMMENT 'Description of the forum.',
  `forum_link` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'If set forum will display as a link.',
  `forum_category` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of the category this forum falls under.',
  `forum_type` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Forum type, 0 for regular board, 1 for category and 2 for link.',
  `forum_icon` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display icon for the forum.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_forums_permissions`
--

DROP TABLE IF EXISTS `sakura_forums_permissions`;
CREATE TABLE `sakura_forums_permissions` (
  `forum_id` bigint(255) UNSIGNED NOT NULL COMMENT 'Forum ID',
  `rank_id` bigint(128) UNSIGNED NOT NULL COMMENT 'Rank ID, leave 0 for a user',
  `user_id` bigint(255) UNSIGNED NOT NULL COMMENT 'User ID, leave 0 for a rank',
  `forum_perms` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Forum action permission string'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_friends`
--

DROP TABLE IF EXISTS `sakura_friends`;
CREATE TABLE `sakura_friends` (
  `user_id` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of the user that added the friend.',
  `friend_id` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of the user that was added as a friend.',
  `friend_timestamp` int(11) UNSIGNED NOT NULL COMMENT 'Timestamp of action.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_infopages`
--

DROP TABLE IF EXISTS `sakura_infopages`;
CREATE TABLE `sakura_infopages` (
  `page_shorthand` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Name used for calling this page up in the /r/URL',
  `page_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title displayed on the top of the page',
  `page_content` text COLLATE utf8_bin NOT NULL COMMENT 'Content of the page'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_login_attempts`
--

DROP TABLE IF EXISTS `sakura_login_attempts`;
CREATE TABLE `sakura_login_attempts` (
  `attempt_id` bigint(255) UNSIGNED NOT NULL COMMENT 'MySQL Generated ID used for sorting.',
  `attempt_success` tinyint(1) UNSIGNED NOT NULL COMMENT 'Success boolean.',
  `attempt_timestamp` int(11) UNSIGNED NOT NULL COMMENT 'Unix timestamp of the event.',
  `attempt_ip` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IP that made this attempt.',
  `user_id` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of the user that was attempted to log in to.'
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_messages`
--

DROP TABLE IF EXISTS `sakura_messages`;
CREATE TABLE `sakura_messages` (
  `id` bigint(128) UNSIGNED NOT NULL COMMENT 'Automatically generated ID by MySQL for management.',
  `from_user` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of the user that sent this message.',
  `to_user` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of user that should receive this message.',
  `read` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IDs of users who read this message.',
  `deleted` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Indicator if one of the parties deleted the message, if it is already 1 the script will remove this row.',
  `timestamp` int(11) UNSIGNED NOT NULL COMMENT 'Timestamp of the time this message was sent',
  `subject` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title of the message',
  `content` text COLLATE utf8_bin NOT NULL COMMENT 'Contents of the message.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_news`
--

DROP TABLE IF EXISTS `sakura_news`;
CREATE TABLE `sakura_news` (
  `news_id` bigint(255) UNSIGNED NOT NULL COMMENT 'Automatically generated ID by MySQL for management.',
  `news_category` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Category ID.',
  `user_id` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of user who posted this news message.',
  `news_timestamp` int(11) UNSIGNED NOT NULL COMMENT 'News post timestamp.',
  `news_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title of the post.',
  `news_content` text COLLATE utf8_bin NOT NULL COMMENT 'Contents of the post'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_notifications`
--

DROP TABLE IF EXISTS `sakura_notifications`;
CREATE TABLE `sakura_notifications` (
  `alert_id` bigint(255) UNSIGNED NOT NULL COMMENT 'Automatically generated ID by MySQL for management.',
  `user_id` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'User ID this notification is intended for.',
  `alert_timestamp` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Timestamp when this notification was created.',
  `alert_read` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Toggle for unread and read.',
  `alert_sound` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Toggle if a sound should be played upon receiving the notification.',
  `alert_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title displayed on the notification.',
  `alert_text` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Text displayed.',
  `alert_link` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Link (empty for no link).',
  `alert_img` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Image path, prefix with font: to use a font class instead of an image.',
  `alert_timeout` int(16) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'How long the notification should stay on screen in milliseconds, 0 for forever.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_optionfields`
--

DROP TABLE IF EXISTS `sakura_optionfields`;
CREATE TABLE `sakura_optionfields` (
  `option_id` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Unique identifier for accessing this option.',
  `option_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Description of the field in a proper way.',
  `option_description` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Longer description of the option.',
  `option_type` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Type attribute in the input element.',
  `option_permission` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The minimum permission level this option requires.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_permissions`
--

DROP TABLE IF EXISTS `sakura_permissions`;
CREATE TABLE `sakura_permissions` (
  `rank_id` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of the rank this permissions set is used for.',
  `user_id` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of the user this permissions set is used for.',
  `permissions_site` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0' COMMENT 'Site permissions.',
  `permissions_manage` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0' COMMENT 'Site management permissions',
  `permissions_forums` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0' COMMENT 'Forum permissions.',
  `permissions_inherit` varchar(4) COLLATE utf8_bin NOT NULL DEFAULT '0' COMMENT 'Rank inheritance, only used when user specific.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_posts`
--

DROP TABLE IF EXISTS `sakura_posts`;
CREATE TABLE `sakura_posts` (
  `post_id` bigint(255) UNSIGNED NOT NULL COMMENT 'MySQL Generated ID used for sorting.',
  `topic_id` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of topic this post is a part of.',
  `forum_id` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of forum this was posted in.',
  `poster_id` bigint(255) UNSIGNED DEFAULT '0' COMMENT 'ID of poster of this post.',
  `poster_ip` varchar(40) COLLATE utf8_bin NOT NULL COMMENT 'IP of poster.',
  `post_time` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Time this post was made.',
  `post_signature` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Toggle if signature should be shown.',
  `post_subject` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Subject of the post.',
  `post_text` text COLLATE utf8_bin NOT NULL COMMENT 'Contents of the post.',
  `post_edit_time` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Time this post was last edited.',
  `post_edit_reason` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason this was edited.',
  `post_edit_user` int(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of user that edited.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_premium`
--

DROP TABLE IF EXISTS `sakura_premium`;
CREATE TABLE `sakura_premium` (
  `user_id` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of the user that purchased Tenshi.',
  `premium_start` int(11) UNSIGNED NOT NULL COMMENT 'Timestamp of first purchase.',
  `premium_expire` int(11) UNSIGNED NOT NULL COMMENT 'Expiration timestamp.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_premium_log`
--

DROP TABLE IF EXISTS `sakura_premium_log`;
CREATE TABLE `sakura_premium_log` (
  `transaction_id` int(16) UNSIGNED NOT NULL COMMENT 'MySQL Generated ID used for sorting.',
  `user_id` bigint(255) UNSIGNED NOT NULL COMMENT 'User ID of purchaser',
  `transaction_amount` float NOT NULL COMMENT 'Amount that was transferred.',
  `transaction_date` int(11) UNSIGNED NOT NULL COMMENT 'Date when the purchase was made.',
  `transaction_comment` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'A short description of the action taken.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_profilefields`
--

DROP TABLE IF EXISTS `sakura_profilefields`;
CREATE TABLE `sakura_profilefields` (
  `field_id` int(64) UNSIGNED NOT NULL COMMENT 'ID used for ordering on the userpage.',
  `field_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Name of the field.',
  `field_type` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Type attribute in the input element.',
  `field_link` tinyint(1) UNSIGNED NOT NULL COMMENT 'Set if this value should be put in a href.',
  `field_linkformat` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'If the form is a link how should it be formatted? {{ VAL }} gets replace with the value.',
  `field_description` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Description of the field displayed in the control panel.',
  `field_additional` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Undocumented JSON array containing special options if needed (probably only going to be used for the YouTube field).'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_ranks`
--

DROP TABLE IF EXISTS `sakura_ranks`;
CREATE TABLE `sakura_ranks` (
  `rank_id` bigint(128) UNSIGNED NOT NULL COMMENT 'Automatically generated ID by MySQL for management.',
  `rank_hierarchy` int(11) UNSIGNED NOT NULL COMMENT 'Rank hierarchy.',
  `rank_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display name of the rank.',
  `rank_multiple` varchar(10) COLLATE utf8_bin DEFAULT NULL COMMENT 'Used when addressing this rank as a multiple',
  `rank_hidden` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Don''t show any public links to this rank.',
  `rank_colour` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Colour used for the username of a member of this rank.',
  `rank_description` text COLLATE utf8_bin NOT NULL COMMENT 'A description of what a user of this rank can do/is supposed to do.',
  `rank_title` varchar(64) COLLATE utf8_bin NOT NULL COMMENT 'Default user title if user has none set.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_reports`
--

DROP TABLE IF EXISTS `sakura_reports`;
CREATE TABLE `sakura_reports` (
  `id` bigint(255) UNSIGNED NOT NULL COMMENT 'MySQL Generated ID used for sorting.',
  `type` int(32) UNSIGNED NOT NULL COMMENT 'Report type, entirely handled on the script side.',
  `issuer` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of the person who issued this report.',
  `subject` bigint(255) UNSIGNED NOT NULL COMMENT 'ID pointing out what was reported (a more accurate description isn''t possible due to the type column).',
  `title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'A quick description of this report.',
  `description` text COLLATE utf8_bin NOT NULL COMMENT 'And a detailed description.',
  `reviewed` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of the moderator that reviewed this report.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_sessions`
--

DROP TABLE IF EXISTS `sakura_sessions`;
CREATE TABLE `sakura_sessions` (
  `session_id` bigint(255) UNSIGNED NOT NULL COMMENT 'Automatically generated ID by MySQL for management. ',
  `user_id` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of the user this session is spawned for. ',
  `user_ip` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IP of the user this session is spawned for.',
  `user_agent` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'User agent of the user this session is spawned for.',
  `session_key` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Session key, allow direct access to the user''s account. ',
  `session_start` int(16) UNSIGNED NOT NULL COMMENT 'The timestamp for when the session was started. ',
  `session_expire` int(16) UNSIGNED NOT NULL COMMENT 'The timestamp for when this session should end, -1 for permanent. ',
  `session_remember` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'If set to 1 session will be extended each time a page is loaded.'
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_topics`
--

DROP TABLE IF EXISTS `sakura_topics`;
CREATE TABLE `sakura_topics` (
  `topic_id` bigint(255) UNSIGNED NOT NULL COMMENT 'MySQL Generated ID used for sorting.',
  `forum_id` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of forum this topic was created in.',
  `topic_hidden` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Boolean to set the topic as hidden.',
  `topic_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title of the topic.',
  `topic_time` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Timestamp when the topic was created.',
  `topic_time_limit` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'After how long a topic should be locked.',
  `topic_views` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Amount of times the topic has been viewed.',
  `topic_status` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Status of topic.',
  `topic_status_change` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Date the topic status was changed (used for deletion cooldown as well).',
  `topic_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Type of the topic.',
  `topic_last_reply` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Timestamp of when the last reply made to this thread.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_topics_track`
--

DROP TABLE IF EXISTS `sakura_topics_track`;
CREATE TABLE `sakura_topics_track` (
  `user_id` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of the user this row applies to.',
  `topic_id` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of the thread in question.',
  `forum_id` bigint(255) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of the forum in question.',
  `mark_time` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Timestamp of the event.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_username_history`
--

DROP TABLE IF EXISTS `sakura_username_history`;
CREATE TABLE `sakura_username_history` (
  `change_id` int(11) UNSIGNED NOT NULL COMMENT 'Identifier',
  `change_time` int(11) UNSIGNED NOT NULL COMMENT 'Timestamp of change',
  `user_id` bigint(255) UNSIGNED NOT NULL COMMENT 'User ID',
  `username_new` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'New username',
  `username_new_clean` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Clean new username',
  `username_old` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Old username',
  `username_old_clean` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Clean old username'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_users`
--

DROP TABLE IF EXISTS `sakura_users`;
CREATE TABLE `sakura_users` (
  `user_id` bigint(255) UNSIGNED NOT NULL COMMENT 'Automatically generated ID by MySQL for management. ',
  `username` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Username set at registration.',
  `username_clean` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'A more cleaned up version of the username for backend usage.',
  `password_hash` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Hashing algo used for the password hash.',
  `password_salt` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Salt used for the password hash.',
  `password_algo` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Algorithm used for the password hash.',
  `password_iter` int(11) UNSIGNED NOT NULL COMMENT 'Password hash iterations.',
  `password_chan` int(11) UNSIGNED NOT NULL COMMENT 'Last time the user changed their password.',
  `email` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'E-mail of the user for password restoring etc.',
  `rank_main` mediumint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Main rank of the user.',
  `user_ranks` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '[0]' COMMENT 'Array containing the ranks the user is part of.',
  `user_colour` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Additional name colour, when empty colour defaults to group colour.',
  `register_ip` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IP used for the creation of this account.',
  `last_ip` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Last IP that was used to log into this account.',
  `user_title` varchar(64) COLLATE utf8_bin DEFAULT NULL COMMENT 'Custom user title of the user, when empty reverts to their derault group name.',
  `user_registered` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Timestamp of account creation.',
  `user_last_online` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Last time anything was done on this account.',
  `user_birthday` date NOT NULL COMMENT 'Birthdate of the user.',
  `user_country` char(2) COLLATE utf8_bin NOT NULL COMMENT 'Contains ISO 3166 country code of user''s registration location.',
  `user_data` text COLLATE utf8_bin COMMENT 'All additional profile data.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sakura_warnings`
--

DROP TABLE IF EXISTS `sakura_warnings`;
CREATE TABLE `sakura_warnings` (
  `warning_id` bigint(255) UNSIGNED NOT NULL COMMENT 'Automatically generated ID by MySQL for management.',
  `user_id` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of user that was warned.',
  `moderator_id` bigint(255) UNSIGNED NOT NULL COMMENT 'ID of the user that issued the warning.',
  `warning_issued` int(16) UNSIGNED NOT NULL COMMENT 'Timestamp of the date the warning was issued.',
  `warning_expires` int(16) UNSIGNED NOT NULL COMMENT 'Timstamp when the warning should expire, 0 for a permanent warning.',
  `warning_action` tinyint(1) UNSIGNED DEFAULT NULL COMMENT 'Action taken.',
  `warning_reason` varchar(512) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason for the warning.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sakura_actioncodes`
--
ALTER TABLE `sakura_actioncodes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sakura_bans`
--
ALTER TABLE `sakura_bans`
  ADD PRIMARY KEY (`ban_id`),
  ADD KEY `uid` (`user_id`),
  ADD KEY `mod_id` (`ban_moderator`);

--
-- Indexes for table `sakura_bbcodes`
--
ALTER TABLE `sakura_bbcodes`
  ADD PRIMARY KEY (`bbcode_id`);

--
-- Indexes for table `sakura_comments`
--
ALTER TABLE `sakura_comments`
  ADD PRIMARY KEY (`comment_id`);

--
-- Indexes for table `sakura_config`
--
ALTER TABLE `sakura_config`
  ADD UNIQUE KEY `config_name` (`config_name`);

--
-- Indexes for table `sakura_faq`
--
ALTER TABLE `sakura_faq`
  ADD PRIMARY KEY (`faq_id`);

--
-- Indexes for table `sakura_forums`
--
ALTER TABLE `sakura_forums`
  ADD PRIMARY KEY (`forum_id`);

--
-- Indexes for table `sakura_friends`
--
ALTER TABLE `sakura_friends`
  ADD KEY `uid` (`user_id`),
  ADD KEY `fid` (`friend_id`);

--
-- Indexes for table `sakura_login_attempts`
--
ALTER TABLE `sakura_login_attempts`
  ADD PRIMARY KEY (`attempt_id`);

--
-- Indexes for table `sakura_messages`
--
ALTER TABLE `sakura_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sakura_news`
--
ALTER TABLE `sakura_news`
  ADD PRIMARY KEY (`news_id`);

--
-- Indexes for table `sakura_notifications`
--
ALTER TABLE `sakura_notifications`
  ADD PRIMARY KEY (`alert_id`),
  ADD KEY `uid` (`user_id`);

--
-- Indexes for table `sakura_optionfields`
--
ALTER TABLE `sakura_optionfields`
  ADD UNIQUE KEY `id` (`option_id`);

--
-- Indexes for table `sakura_posts`
--
ALTER TABLE `sakura_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `forum_id` (`forum_id`),
  ADD KEY `poster_id` (`poster_id`);

--
-- Indexes for table `sakura_premium`
--
ALTER TABLE `sakura_premium`
  ADD UNIQUE KEY `uid` (`user_id`);

--
-- Indexes for table `sakura_premium_log`
--
ALTER TABLE `sakura_premium_log`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `sakura_profilefields`
--
ALTER TABLE `sakura_profilefields`
  ADD PRIMARY KEY (`field_id`);

--
-- Indexes for table `sakura_ranks`
--
ALTER TABLE `sakura_ranks`
  ADD PRIMARY KEY (`rank_id`);

--
-- Indexes for table `sakura_reports`
--
ALTER TABLE `sakura_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sakura_sessions`
--
ALTER TABLE `sakura_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `userid` (`user_id`);

--
-- Indexes for table `sakura_topics`
--
ALTER TABLE `sakura_topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `forum_id` (`forum_id`);

--
-- Indexes for table `sakura_username_history`
--
ALTER TABLE `sakura_username_history`
  ADD PRIMARY KEY (`change_id`);

--
-- Indexes for table `sakura_users`
--
ALTER TABLE `sakura_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username_clean` (`username_clean`);

--
-- Indexes for table `sakura_warnings`
--
ALTER TABLE `sakura_warnings`
  ADD PRIMARY KEY (`warning_id`),
  ADD KEY `uid` (`user_id`),
  ADD KEY `iid` (`moderator_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sakura_actioncodes`
--
ALTER TABLE `sakura_actioncodes`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.';
--
-- AUTO_INCREMENT for table `sakura_bans`
--
ALTER TABLE `sakura_bans`
  MODIFY `ban_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.';
--
-- AUTO_INCREMENT for table `sakura_bbcodes`
--
ALTER TABLE `sakura_bbcodes`
  MODIFY `bbcode_id` int(64) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.', AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `sakura_comments`
--
ALTER TABLE `sakura_comments`
  MODIFY `comment_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.', AUTO_INCREMENT=90;
--
-- AUTO_INCREMENT for table `sakura_faq`
--
ALTER TABLE `sakura_faq`
  MODIFY `faq_id` bigint(128) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.', AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `sakura_forums`
--
ALTER TABLE `sakura_forums`
  MODIFY `forum_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.', AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `sakura_login_attempts`
--
ALTER TABLE `sakura_login_attempts`
  MODIFY `attempt_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.', AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `sakura_messages`
--
ALTER TABLE `sakura_messages`
  MODIFY `id` bigint(128) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.', AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `sakura_news`
--
ALTER TABLE `sakura_news`
  MODIFY `news_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.', AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `sakura_notifications`
--
ALTER TABLE `sakura_notifications`
  MODIFY `alert_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.', AUTO_INCREMENT=92;
--
-- AUTO_INCREMENT for table `sakura_posts`
--
ALTER TABLE `sakura_posts`
  MODIFY `post_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.', AUTO_INCREMENT=304;
--
-- AUTO_INCREMENT for table `sakura_premium_log`
--
ALTER TABLE `sakura_premium_log`
  MODIFY `transaction_id` int(16) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.', AUTO_INCREMENT=157;
--
-- AUTO_INCREMENT for table `sakura_profilefields`
--
ALTER TABLE `sakura_profilefields`
  MODIFY `field_id` int(64) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID used for ordering on the userpage.', AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `sakura_ranks`
--
ALTER TABLE `sakura_ranks`
  MODIFY `rank_id` bigint(128) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.', AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `sakura_reports`
--
ALTER TABLE `sakura_reports`
  MODIFY `id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.';
--
-- AUTO_INCREMENT for table `sakura_sessions`
--
ALTER TABLE `sakura_sessions`
  MODIFY `session_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management. ', AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `sakura_topics`
--
ALTER TABLE `sakura_topics`
  MODIFY `topic_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.', AUTO_INCREMENT=120;
--
-- AUTO_INCREMENT for table `sakura_username_history`
--
ALTER TABLE `sakura_username_history`
  MODIFY `change_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identifier', AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `sakura_users`
--
ALTER TABLE `sakura_users`
  MODIFY `user_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management. ', AUTO_INCREMENT=32;
--
-- AUTO_INCREMENT for table `sakura_warnings`
--
ALTER TABLE `sakura_warnings`
  MODIFY `warning_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
