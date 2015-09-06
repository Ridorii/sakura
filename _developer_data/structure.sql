-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `sakura-development`;

DROP TABLE IF EXISTS `sakura_actioncodes`;
CREATE TABLE `sakura_actioncodes` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `action` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Action identifier so the backend knows what to do.',
  `userid` bigint(255) NOT NULL COMMENT 'ID of the user that would be affected by this action',
  `actkey` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The URL key for using this code.',
  `instruction` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Things the backend should do upon using this code',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_apikeys`;
CREATE TABLE `sakura_apikeys` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `owner` bigint(128) unsigned NOT NULL COMMENT 'ID of user that owns this API key.',
  `apikey` varchar(32) COLLATE utf8_bin NOT NULL COMMENT 'The API key.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_bans`;
CREATE TABLE `sakura_bans` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `uid` bigint(255) unsigned NOT NULL COMMENT 'ID of user that was banned, 0 for just an IP ban.',
  `ban_begin` int(11) unsigned NOT NULL COMMENT 'Timestamp when the user was banned.',
  `ban_end` int(11) unsigned NOT NULL COMMENT 'Timestamp when the user should regain access to the site.',
  `ban_reason` varchar(512) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason given for the ban.',
  `mod_id` bigint(255) unsigned NOT NULL COMMENT 'ID of moderator that banned this user,',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `mod_id` (`mod_id`),
  CONSTRAINT `sakura_bans_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `sakura_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sakura_bans_ibfk_2` FOREIGN KEY (`mod_id`) REFERENCES `sakura_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_bbcodes`;
CREATE TABLE `sakura_bbcodes` (
  `id` int(64) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `regex` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Regular expression string for the BBCode.',
  `replace` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'What to replace it with.',
  `title` varchar(128) COLLATE utf8_bin NOT NULL COMMENT 'Button title for this bbcode.',
  `description` varchar(512) COLLATE utf8_bin NOT NULL COMMENT 'Description of what this does.',
  `on_posting` tinyint(1) unsigned NOT NULL COMMENT 'Set if this bbcode is displayed on the posting page.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `sakura_bbcodes` (`id`, `regex`, `replace`, `title`, `description`, `on_posting`) VALUES
(1, '/\\[b\\](.*?)\\[\\/b\\]/is', '<b>$1</b>',  'Bold', 'Make text bold. Usage: [b]text[/b].',  1),
(2, '/\\[i\\](.*?)\\[\\/i\\]/is', '<i>$1</i>',  'Italics',  'Make text italic. Usage: [i]text[/i].',  1),
(3, '/\\[u\\](.*?)\\[\\/u\\]/is', '<u>$1</u>',  'Underline',  'Make text underlined. Usage: [u]text[/u].',  1),
(4, '/\\[s\\](.*?)\\[\\/s\\]/is', '<del>$1</del>',  'Strikethrough',  'Put a line through text. Usage: [s]text[/s].', 1),
(5, '/\\[img\\]([a-zA-Z0-9\\.\\$\\-\\_\\.\\+\\*\\!\\\'\\(\\)\\/\\:\\#]+)\\[\\/img\\]/is', '<img src=\"$1\" alt=\"Image\" />', 'Image',  'Embed an image. Usage: [img]url[/img]',  1),
(6, '/\\[url=([a-zA-Z0-9\\.\\$\\-\\_\\.\\+\\*\\!\\\'\\(\\)\\/\\:\\#]+)\\](.*?)\\[\\/url\\]/is', '<a href=\"$1\" target=\"_blank\">$2</a>',  'Link', 'Embed a URL. Usage: [url=http://google.com]Link to google![/url]', 0),
(7, '/\\[url\\]([a-zA-Z0-9\\.\\$\\-\\_\\.\\+\\*\\!\\\'\\(\\)\\/\\:\\#]+)\\[\\/url\\]/is', '<a href=\"$1\" target=\"_blank\">$1</a>',  'Link', 'Make a link clickable (if the automatic algorithm doesn\'t do it already). Usage: [url]http://google.com[/url]', 1),
(8, '/\\[quote\\=\\\"(.+)\\\"\\](.+)\\[\\/quote]/is', '<div class=\"quote\"><div class=\"quotee\">$1 wrote:</div><div class=\"text\">$2</div></div>', 'Quote',  'Quote a user\'s post. Usage: [quote=Flashwave]nookls is pretty[/quote]', 0),
(9, '/\\[quote\\](.+)\\[\\/quote]/is',  '<div class=\"quote\"><div class=\"quotee\">Quote:</div><div class=\"text\">$1</div></div>',  'Quote',  'Quote a user\'s post. Usage: [quote]nookls is pretty[/quote]', 1);

DROP TABLE IF EXISTS `sakura_config`;
CREATE TABLE `sakura_config` (
  `config_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Array key for configuration value',
  `config_value` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The value, obviously.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `sakura_config` (`config_name`, `config_value`) VALUES
('recaptcha_public',  ''),
('recaptcha_private', ''),
('charset', 'utf-8'),
('cookie_prefix', 'fii_'),
('cookie_domain', 'flashii.test'),
('cookie_path', '/'),
('site_style',  'yuuno'),
('manage_style',  'broomcloset'),
('smtp_server', ''),
('smtp_auth', ''),
('smtp_secure', ''),
('smtp_port', ''),
('smtp_username', ''),
('smtp_password', ''),
('smtp_replyto_mail', 'admin@flashii.net'),
('smtp_replyto_name', 'Flashwave'),
('smtp_from_email', ''),
('smtp_from_name',  'Flashii Noreply'),
('sitename',  'Cutting Edgii'),
('recaptcha', '0'),
('require_activation',  '0'),
('require_registration_code', '0'),
('disable_registration',  '0'),
('max_reg_keys',  '5'),
('mail_signature',  'Team Flashii'),
('lock_authentication', '0'),
('min_entropy', '1'),
('sitedesc',  'Live development environment for the script that powers Flashii.net called Sakura.'),
('sitetags',  '[\"Flashii\",\"Media\",\"Flashwave\",\"Circle\",\"Zeniea\",\"MalwareUp\",\"Cybernetics\",\"Saibateku\",\"Community\",\"osu!\",\"osu\"]'),
('username_min_length', '3'),
('username_max_length', '16'),
('lock_site', '1'),
('lock_site_reason',  'meow'),
('use_gzip',  '0'),
('enable_tpl_cache',  '0'),
('paypal_client_id',  ''),
('paypal_secret', ''),
('premium_price_per_month', '1.49'),
('premium_rank_id', '8'),
('premium_amount_max',  '24'),
('alumni_rank_id',  '9'),
('url_main',  'flashii.test'),
('front_page_news_posts', '3'),
('date_format', 'D Y-m-d H:i:s T'),
('news_posts_per_page', '3'),
('avatar_min_width',  '20'),
('avatar_min_height', '20'),
('avatar_max_height', '512'),
('avatar_max_width',  '512'),
('avatar_max_fsize',  '2097152'),
('url_api', 'api.flashii.test'),
('content_path',  '/content'),
('user_uploads',  'uploads'),
('no_background_img', 'main/content/pixel.png'),
('no_header_img', 'main/content/images/triangles.png'),
('pixel_img', 'main/content/pixel.png'),
('background_max_fsize',  '5242880'),
('background_max_width',  '2560'),
('background_max_height', '1440'),
('background_min_height', '16'),
('background_min_width',  '16'),
('max_online_time', '500'),
('no_avatar_img', 'main/content/data/{{ TPL }}/images/no-av.png'),
('deactivated_avatar_img',  'main/content/data/{{ TPL }}/images/deactivated-av.png'),
('banned_avatar_img', 'main/content/data/{{ TPL }}/images/banned-av.png'),
('session_check', '2'),
('url_rewrite', '1'),
('members_per_page',  '30');

DROP TABLE IF EXISTS `sakura_emoticons`;
CREATE TABLE `sakura_emoticons` (
  `emote_string` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'String to catch and replace',
  `emote_path` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Path to the image file relative to the content domain.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `sakura_emoticons` (`emote_string`, `emote_path`) VALUES
(':amu:', '/content/images/emoticons/amu.png'),
(':angrier:', '/content/images/emoticons/angrier.png'),
(':angriest:',  '/content/images/emoticons/angriest.png'),
(':angry:', '/content/images/emoticons/angry.gif'),
(':blank:', '/content/images/emoticons/blank.png'),
(':childish:',  '/content/images/emoticons/childish.png'),
(':congrats:',  '/content/images/emoticons/congrats.png'),
(':crying:',  '/content/images/emoticons/crying.gif'),
(':dizzy:', '/content/images/emoticons/dizzy.gif'),
(':eat:', '/content/images/emoticons/eat.gif'),
(':evil:',  '/content/images/emoticons/evil.png'),
(':extreme:', '/content/images/emoticons/extreme.png'),
(':glare:', '/content/images/emoticons/glare.gif'),
(':happy:', '/content/images/emoticons/happy.gif'),
(':horror:',  '/content/images/emoticons/horror.gif'),
(':huh:', '/content/images/emoticons/huh.png'),
(':idea:',  '/content/images/emoticons/idea.png'),
(':jew:', '/content/images/emoticons/jew.png'),
(':kiss:',  '/content/images/emoticons/kiss.gif'),
(':lmao:',  '/content/images/emoticons/lmao.gif'),
(':lol:', '/content/images/emoticons/lol.gif'),
(':love:',  '/content/images/emoticons/love.png'),
(':meow:',  '/content/images/emoticons/meow.png'),
(':omg:', '/content/images/emoticons/omg.gif'),
(':ouch:',  '/content/images/emoticons/ouch.gif'),
(':puke:',  '/content/images/emoticons/puke.gif'),
(':ruse:',  '/content/images/emoticons/ruse.png'),
(':sad:', '/content/images/emoticons/sad.png'),
(':sigh:',  '/content/images/emoticons/sigh.gif'),
(':suspicious:',  '/content/images/emoticons/suspicious.gif'),
(':sweat:', '/content/images/emoticons/sweat.gif'),
(':tired:', '/content/images/emoticons/tired.gif'),
(':yay:', '/content/images/emoticons/vhappy.gif'),
(':winxp:', '/content/images/emoticons/winxp.png'),
(':wtf:', '/content/images/emoticons/wtf.gif'),
(':sleep:', '/content/images/emoticons/zzz.gif'),
(':what:',  '/content/images/emoticons/what.png'),
(':smug:',  '/content/images/emoticons/smug.png');


DROP TABLE IF EXISTS `sakura_error_log`;
CREATE TABLE `sakura_error_log` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL,
  `timestamp` varchar(128) COLLATE utf8_bin NOT NULL,
  `error_type` int(16) unsigned NOT NULL,
  `error_line` int(32) unsigned NOT NULL,
  `error_string` varchar(512) COLLATE utf8_bin NOT NULL,
  `error_file` varchar(512) COLLATE utf8_bin NOT NULL,
  `backtrace` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_faq`;
CREATE TABLE `sakura_faq` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `short` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Used for linking directly to a question.',
  `question` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The question.',
  `answer` text COLLATE utf8_bin NOT NULL COMMENT 'The answer.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_forums`;
CREATE TABLE `sakura_forums` (
  `forum_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `forum_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display name of the forum.',
  `forum_desc` text COLLATE utf8_bin NOT NULL COMMENT 'Description of the forum.',
  `forum_link` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'If set forum will display as a link.',
  `forum_category` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the category this forum falls under.',
  `forum_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Forum type, 0 for regular board, 1 for category and 2 for link.',
  `forum_icon` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display icon for the forum.',
  PRIMARY KEY (`forum_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_friends`;
CREATE TABLE `sakura_friends` (
  `uid` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that added the friend.',
  `fid` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that was added as a friend.',
  `timestamp` int(11) unsigned NOT NULL COMMENT 'Timestamp of action.',
  KEY `uid` (`uid`),
  KEY `fid` (`fid`),
  CONSTRAINT `sakura_friends_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `sakura_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sakura_friends_ibfk_2` FOREIGN KEY (`fid`) REFERENCES `sakura_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_infopages`;
CREATE TABLE `sakura_infopages` (
  `shorthand` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Name used for calling this page up in the /r/URL',
  `pagetitle` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title displayed on the top of the page',
  `content` text COLLATE utf8_bin NOT NULL COMMENT 'Content of the page'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_logs`;
CREATE TABLE `sakura_logs` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `uid` bigint(255) unsigned NOT NULL COMMENT 'User ID of user that took this action.',
  `action` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Action identifier.',
  `attribs` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Optional attributes, vsprintf() style.',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  CONSTRAINT `sakura_logs_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `sakura_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_logtypes`;
CREATE TABLE `sakura_logtypes` (
  `id` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Identifier of action (has to match things in the logs table).',
  `string` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'String to format using vsprintf and the attributes in the logs table.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_messages`;
CREATE TABLE `sakura_messages` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `from_user` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that sent this message.',
  `to_user` bigint(255) unsigned NOT NULL COMMENT 'ID of user that should receive this message.',
  `read` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IDs of users who read this message.',
  `deleted` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Indicator if one of the parties deleted the message, if it is already 1 the script will remove this row.',
  `timestamp` int(11) unsigned NOT NULL COMMENT 'Timestamp of the time this message was sent',
  `subject` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title of the message',
  `content` text COLLATE utf8_bin NOT NULL COMMENT 'Contents of the message.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_news`;
CREATE TABLE `sakura_news` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `category` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Category ID.',
  `uid` bigint(255) unsigned NOT NULL COMMENT 'ID of user who posted this news message.',
  `date` int(11) unsigned NOT NULL COMMENT 'News post timestamp.',
  `title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title of the post.',
  `content` text COLLATE utf8_bin NOT NULL COMMENT 'Contents of the post',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_notifications`;
CREATE TABLE `sakura_notifications` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `uid` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID this notification is intended for.',
  `timestamp` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp when this notification was created.',
  `notif_read` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Toggle for unread and read.',
  `notif_sound` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Toggle if a sound should be played upon receiving the notification.',
  `notif_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title displayed on the notification.',
  `notif_text` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Text displayed.',
  `notif_link` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Link (empty for no link).',
  `notif_img` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Image path, prefix with font: to use a font class instead of an image.',
  `notif_timeout` int(16) unsigned NOT NULL DEFAULT '0' COMMENT 'How long the notification should stay on screen in milliseconds, 0 for forever.',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  CONSTRAINT `sakura_notifications_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `sakura_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_optionfields`;
CREATE TABLE `sakura_optionfields` (
  `id` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Unique identifier for accessing this option.',
  `name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Description of the field in a proper way.',
  `description` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Longer description of the option.',
  `formtype` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Type attribute in the input element.',
  `require_perm` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The minimum permission level this option requires.',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `sakura_optionfields` (`id`, `name`, `description`, `formtype`, `require_perm`) VALUES
('disableProfileParallax',  'Disable Parallaxing',  'This will stop your background from responding to your mouse movement, this will only affect your background.',  'checkbox', 'CHANGE_BACKGROUND'),
('profileBackgroundSiteWide', 'Display profile background site wide', 'This will make the profile background you set on your profile appear on the entire site (except on other profiles).',  'checkbox', 'CREATE_BACKGROUND'),
('useMisaki', 'Use the testing style',  'This will make the site use the new Misaki style instead of Yuuno.', 'checkbox', 'ALTER_PROFILE');

DROP TABLE IF EXISTS `sakura_permissions`;
CREATE TABLE `sakura_permissions` (
  `rid` bigint(128) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the rank this permissions set is used for.',
  `uid` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the user this permissions set is used for.',
  `siteperms` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '000000000000000000000000000' COMMENT 'Site permissions.',
  `manageperms` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0' COMMENT 'Site management permissions',
  `forumperms` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0' COMMENT 'Forum permissions.',
  `rankinherit` varchar(4) COLLATE utf8_bin NOT NULL DEFAULT '000' COMMENT 'Rank inheritance, only used when user specific.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `sakura_permissions` (`rid`, `uid`, `siteperms`, `manageperms`, `forumperms`, `rankinherit`) VALUES
(1, 0,  '0000000000000000000000000001', '00', '0',  '000'),
(2, 0,  '0000111111111100111101101100', '00', '1',  '000'),
(3, 0,  '0001111111111111111111111100', '11', '1',  '000'),
(4, 0,  '1111111111111111111111111100', '11', '1',  '000'),
(5, 0,  '0001111111111111111111111100', '11', '1',  '000'),
(6, 0,  '0000111111111100111101101100', '00', '0',  '000'),
(7, 0,  '0001111111111111111111111100', '01', '1',  '000'),
(8, 0,  '0001111111111111111111111100', '00', '1',  '000'),
(9, 0,  '0001111111111111111111111100', '00', '1',  '000'),
(10,  0,  '0000000011010100101000100010', '00', '0',  '000');

DROP TABLE IF EXISTS `sakura_posts`;
CREATE TABLE `sakura_posts` (
  `post_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `topic_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of topic this post is a part of.',
  `forum_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of forum this was posted in.',
  `poster_id` bigint(255) unsigned DEFAULT '0' COMMENT 'ID of poster of this post.',
  `poster_ip` varchar(40) COLLATE utf8_bin NOT NULL COMMENT 'IP of poster.',
  `post_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Time this post was made.',
  `parse_mode` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Switch the type of parser that''s used.',
  `enable_sig` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Toggle if signature should be shown.',
  `enable_emotes` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Toggle if emoticons should be parsed.',
  `post_subject` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Subject of the post.',
  `post_text` text COLLATE utf8_bin NOT NULL COMMENT 'Contents of the post.',
  `post_edit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Time this post was last edited.',
  `post_edit_reason` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason this was edited.',
  `post_edit_user` int(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of user that edited.',
  PRIMARY KEY (`post_id`),
  KEY `topic_id` (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `poster_id` (`poster_id`),
  CONSTRAINT `sakura_posts_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `sakura_topics` (`topic_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sakura_posts_ibfk_2` FOREIGN KEY (`forum_id`) REFERENCES `sakura_forums` (`forum_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_premium`;
CREATE TABLE `sakura_premium` (
  `uid` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that purchased Tenshi.',
  `startdate` int(11) unsigned NOT NULL COMMENT 'Timestamp of first purchase.',
  `expiredate` int(11) unsigned NOT NULL COMMENT 'Expiration timestamp.',
  UNIQUE KEY `uid` (`uid`),
  CONSTRAINT `sakura_premium_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `sakura_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_premium_log`;
CREATE TABLE `sakura_premium_log` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `uid` bigint(255) unsigned NOT NULL COMMENT 'User ID of purchaser',
  `amount` float NOT NULL COMMENT 'Amount that was transferred.',
  `date` int(11) unsigned NOT NULL COMMENT 'Date when the purchase was made.',
  `comment` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'A short description of the action taken.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_profilefields`;
CREATE TABLE `sakura_profilefields` (
  `id` int(64) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID used for ordering on the userpage.',
  `name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Name of the field.',
  `formtype` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Type attribute in the input element.',
  `islink` tinyint(1) unsigned NOT NULL COMMENT 'Set if this value should be put in a href.',
  `linkformat` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'If the form is a link how should it be formatted? {{ VAL }} gets replace with the value.',
  `description` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Description of the field displayed in the control panel.',
  `additional` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Undocumented JSON array containing special options if needed (probably only going to be used for the YouTube field).',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `sakura_profilefields` (`id`, `name`, `formtype`, `islink`, `linkformat`, `description`, `additional`) VALUES
(1, 'Website',  'url',  1,  '{{ VAL }}',  'URL to your website',  ''),
(2, 'Twitter',  'text', 1,  'https://twitter.com/{{ VAL }}',  'Your @twitter Username', ''),
(3, 'BitBucket',  'text', 1,  'https://bitbucket.org/{{ VAL }}',  'Your BitBucket Username',  ''),
(4, 'Skype',  'text', 1,  'skype:{{ VAL }}?userinfo', 'Your Skype Username',  ''),
(5, 'YouTube',  'text', 0,  '', 'ID or Username excluding http://youtube.com/*/', '{\"youtubetype\": [\"checkbox\", \"I <b>do not</b> have a Channel Username (url looks like https://www.youtube.com/channel/UCXZcw5hw5C7Neto-T_nRXBQ).\"]}'),
(6, 'SoundCloud', 'text', 1,  'https://soundcloud.com/{{ VAL }}', 'Your SoundCloud username', ''),
(7, 'Steam',  'text', 1,  'https://steamcommunity.com/id/{{ VAL }}',  'Your Steam Community Username (may differ from login username)', ''),
(8, 'osu!', 'text', 1,  'https://osu.ppy.sh/u/{{ VAL }}', 'Your osu! Username', ''),
(9, 'Origin', 'text', 0,  '', 'Your Origin User ID',  ''),
(10,  'Xbox Live',  'text', 1,  'https://account.xbox.com/en-GB/Profile?Gamertag={{ VAL }}',  'Your Xbox User ID',  ''),
(11,  'PSN',  'text', 1,  'http://psnprofiles.com/{{ VAL }}', 'Your PSN User ID', ''),
(12,  'Last.fm',  'text', 1,  'http://last.fm/user/{{ VAL }}',  'Your Last.fm username',  '');

DROP TABLE IF EXISTS `sakura_ranks`;
CREATE TABLE `sakura_ranks` (
  `id` bigint(128) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Display name of the rank.',
  `multi` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Can the rank name have an s at the end?',
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Don''t show any public links to this rank.',
  `colour` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Colour used for the username of a member of this rank.',
  `description` text COLLATE utf8_bin NOT NULL COMMENT 'A description of what a user of this rank can do/is supposed to do.',
  `title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Default user title if user has none set.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `sakura_ranks` (`id`, `name`, `multi`, `hidden`, `colour`, `description`, `title`) VALUES
(1, 'Deactivated',  0,  1,  '#555', 'Users that are yet to be activated or that deactivated their own account.',  'Deactivated'),
(2, 'Regular user', 1,  0,  'inherit',  'Regular users with regular permissions.',  'Regular user'),
(3, 'Site moderator', 1,  0,  '#0A0', 'Users with special permissions like being able to ban and modify users if needed.',  'Staff'),
(4, 'Administrator',  1,  0,  '#C00', 'Users that manage the server and everything around that.', 'Administrator'),
(5, 'Developer',  1,  0,  '#824CA0',  'Users that either create or test new features of the site.', 'Staff'),
(6, 'Bot',  1,  1,  '#9E8DA7',  'Reserved user accounts for services.', 'Bot'),
(7, 'Chat moderator', 1,  0,  '#09F', 'Moderators of the chat room.', 'Staff'),
(8, 'Tenshi', 0,  0,  '#EE9400',  'Users that bought premium to help us keep the site and its services alive!', 'Tenshi'),
(9, 'Alumnii',  0,  0,  '#FF69B4',  'People who have contributed to the community but have moved on or resigned.',  'Alumnii'),
(10,  'Restricted', 0,  1,  '#333', 'Users that are restricted.', 'Restricted');

DROP TABLE IF EXISTS `sakura_regcodes`;
CREATE TABLE `sakura_regcodes` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `code` varchar(32) COLLATE utf8_bin NOT NULL COMMENT 'Randomly generated registration key.',
  `created_by` bigint(255) unsigned NOT NULL COMMENT 'ID of user who generated this code.',
  `used_by` bigint(255) unsigned NOT NULL COMMENT 'ID of user who used this code.',
  `key_used` tinyint(1) unsigned NOT NULL COMMENT 'Boolean for setting this key as used.',
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `sakura_regcodes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `sakura_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_reports`;
CREATE TABLE `sakura_reports` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `type` int(32) unsigned NOT NULL COMMENT 'Report type, entirely handled on the script side.',
  `issuer` bigint(255) unsigned NOT NULL COMMENT 'ID of the person who issued this report.',
  `subject` bigint(255) unsigned NOT NULL COMMENT 'ID pointing out what was reported (a more accurate description isn''t possible due to the type column).',
  `title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'A quick description of this report.',
  `description` text COLLATE utf8_bin NOT NULL COMMENT 'And a detailed description.',
  `reviewed` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of the moderator that reviewed this report.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_sessions`;
CREATE TABLE `sakura_sessions` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management. ',
  `userip` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'IP of the user this session is spawned for.',
  `useragent` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'User agent of the user this session is spawned for.',
  `userid` bigint(255) unsigned NOT NULL COMMENT 'ID of the user this session is spawned for. ',
  `skey` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Session key, allow direct access to the user''s account. ',
  `started` int(16) unsigned NOT NULL COMMENT 'The timestamp for when the session was started. ',
  `expire` int(16) unsigned NOT NULL COMMENT 'The timestamp for when this session should end, -1 for permanent. ',
  `remember` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'If set to 1 session will be extended each time a page is loaded.',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  CONSTRAINT `sakura_sessions_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `sakura_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_topics`;
CREATE TABLE `sakura_topics` (
  `topic_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'MySQL Generated ID used for sorting.',
  `forum_id` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of forum this topic was created in.',
  `topic_hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Boolean to set the topic as hidden.',
  `topic_title` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Title of the topic.',
  `topic_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp when the topic was created.',
  `topic_time_limit` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'After how long a topic should be locked.',
  `topic_last_reply` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Last time a post was posted in this topic.',
  `topic_views` bigint(255) unsigned NOT NULL DEFAULT '0' COMMENT 'Amount of times the topic has been viewed.',
  `topic_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Status of topic.',
  `topic_status_change` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Date the topic status was changed (used for deletion cooldown as well).',
  `topic_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Type of the topic.',
  PRIMARY KEY (`topic_id`),
  KEY `forum_id` (`forum_id`),
  CONSTRAINT `sakura_topics_ibfk_1` FOREIGN KEY (`forum_id`) REFERENCES `sakura_forums` (`forum_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_users`;
CREATE TABLE `sakura_users` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management. ',
  `username` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Username set at registration.',
  `username_clean` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'A more cleaned up version of the username for backend usage.',
  `password_hash` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Hashing algo used for the password hash.',
  `password_salt` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Salt used for the password hash.',
  `password_algo` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Algorithm used for the password hash.',
  `password_iter` int(11) unsigned NOT NULL COMMENT 'Password hash iterations.',
  `password_chan` int(11) unsigned NOT NULL COMMENT 'Last time the user changed their password.',
  `password_new` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Field with array containing new password data beit that they requested a password change.',
  `email` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'E-mail of the user for password restoring etc.',
  `rank_main` mediumint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Main rank of the user.',
  `ranks` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '[0]' COMMENT 'Array containing the ranks the user is part of.',
  `name_colour` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Additional name colour, when empty colour defaults to group colour.',
  `register_ip` varchar(16) COLLATE utf8_bin NOT NULL COMMENT 'IP used for the creation of this account.',
  `last_ip` varchar(16) COLLATE utf8_bin NOT NULL COMMENT 'Last IP that was used to log into this account.',
  `usertitle` varchar(64) COLLATE utf8_bin DEFAULT NULL COMMENT 'Custom user title of the user, when empty reverts to their derault group name.',
  `regdate` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp of account creation.',
  `lastdate` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Last time anything was done on this account.',
  `lastunamechange` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Last username change.',
  `birthday` date DEFAULT NULL COMMENT 'Birthdate of the user.',
  `country` char(2) COLLATE utf8_bin NOT NULL COMMENT 'Contains ISO 3166 country code of user''s registration location.',
  `userData` text COLLATE utf8_bin COMMENT 'All additional profile data.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_clean` (`username_clean`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `sakura_warnings`;
CREATE TABLE `sakura_warnings` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Automatically generated ID by MySQL for management.',
  `uid` bigint(255) unsigned NOT NULL COMMENT 'ID of user that was warned.',
  `iid` bigint(255) unsigned NOT NULL COMMENT 'ID of the user that issued the warning.',
  `issued` int(16) unsigned NOT NULL COMMENT 'Timestamp of the date the warning was issued.',
  `expire` int(16) unsigned NOT NULL COMMENT 'Timstamp when the warning should expire, 0 for a permanent warning.',
  `reason` varchar(512) COLLATE utf8_bin DEFAULT NULL COMMENT 'Reason for the warning.',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `iid` (`iid`),
  CONSTRAINT `sakura_warnings_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `sakura_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sakura_warnings_ibfk_2` FOREIGN KEY (`iid`) REFERENCES `sakura_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-- 2015-09-05 16:08:34
