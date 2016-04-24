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
-- Dumping data for table sakura-development.sakura_config: ~79 rows (approximately)
/*!40000 ALTER TABLE `sakura_config` DISABLE KEYS */;
INSERT INTO `sakura_config` (`config_name`, `config_value`) VALUES
    ('admin_email', 'sakura@localhost'),
    ('alumni_rank_id', '9'),
    ('avatar_max_fsize', '2097152'),
    ('avatar_max_height', '512'),
    ('avatar_max_width', '512'),
    ('avatar_min_height', '20'),
    ('avatar_min_width', '20'),
    ('background_max_fsize', '5242880'),
    ('background_max_height', '1440'),
    ('background_max_width', '2560'),
    ('background_min_height', '16'),
    ('background_min_width', '16'),
    ('banned_avatar_img', 'public/content/data/{{ TPL }}/images/banned-av.png'),
    ('charset', 'utf-8'),
    ('comment_max_length', '500'),
    ('comment_min_length', '1'),
    ('content_path', '/content'),
    ('cookie_domain', 'localhost'),
    ('cookie_path', '/'),
    ('cookie_prefix', 'fii_'),
    ('date_format', 'D Y-m-d H:i:s T'),
    ('deactivated_avatar_img', 'public/content/data/{{ TPL }}/images/deactivated-av.png'),
    ('deactive_rank_id', '1'),
    ('default_rank_id', '2'),
    ('disable_registration', '0'),
    ('enable_tpl_cache', '0'),
    ('forum_text_max', '60000'),
    ('forum_text_min', '1'),
    ('forum_title_max', '128'),
    ('forum_title_min', '4'),
    ('forum_trash_id', '19'),
    ('front_page_news_posts', '3'),
    ('header_announcement_image', ''),
    ('header_announcement_link', ''),
    ('header_max_fsize', '2097152'),
    ('header_max_height', '500'),
    ('header_max_width', '2048'),
    ('header_min_height', '125'),
    ('header_min_width', '512'),
    ('lock_authentication', '0'),
    ('mail_signature', 'Sakura | http://localhost/'),
    ('max_online_time', '120'),
    ('max_reg_keys', '5'),
    ('members_per_page', '30'),
    ('min_entropy', '1'),
    ('news_posts_per_page', '3'),
    ('no_avatar_img', 'public/content/data/{{ TPL }}/images/no-av.png'),
    ('no_background_img', 'public/content/pixel.png'),
    ('no_cron_interval', '30'),
    ('no_cron_last', '1460167058'),
    ('no_cron_service', '1'),
    ('no_header_img', 'public/content/pixel.png'),
    ('old_username_reserve', '90'),
    ('paypal_client_id', ''),
    ('paypal_mode', 'sandbox'),
    ('paypal_secret', ''),
    ('pixel_img', 'public/content/pixel.png'),
    ('premium_amount_max', '24'),
    ('premium_price_per_month', '1.49'),
    ('premium_rank_id', '8'),
    ('recaptcha', '0'),
    ('recaptcha_private', ''),
    ('recaptcha_public', ''),
    ('require_activation', '1'),
    ('restricted_rank_id', '10'),
    ('session_check', '4'),
    ('site_closed', '0'),
    ('site_closed_reason', 'meow'),
    ('site_news_category', 'site-news'),
    ('site_style', 'yuuno'),
    ('sitedesc', 'Live development environment for the script that powers Flashii.net called Sakura.'),
    ('sitelogo', ''),
    ('sitename', 'Sakura'),
    ('sitetags', '["Flashii","Media","Flashwave","Circle","Zeniea","MalwareUp","Cybernetics","Saibateku","Community","osu!","osu"]'),
    ('smtp_auth', ''),
    ('smtp_from_email', ''),
    ('smtp_from_name', ''),
    ('smtp_password', '[ base 64 encoded ]'),
    ('smtp_port', ''),
    ('smtp_replyto_mail', ''),
    ('smtp_replyto_name', ''),
    ('smtp_secure', ''),
    ('smtp_server', ''),
    ('smtp_username', ''),
    ('url_main', 'locahost'),
    ('url_rewrite', '1'),
    ('use_gzip', '0'),
    ('user_uploads', 'uploads'),
    ('username_max_length', '16'),
    ('username_min_length', '3');
/*!40000 ALTER TABLE `sakura_config` ENABLE KEYS */;

-- Dumping data for table sakura-development.sakura_emoticons: ~38 rows (approximately)
/*!40000 ALTER TABLE `sakura_emoticons` DISABLE KEYS */;
INSERT INTO `sakura_emoticons` (`emote_string`, `emote_path`) VALUES
    (':amu:', '/content/images/emoticons/amu.png'),
    (':angrier:', '/content/images/emoticons/angrier.png'),
    (':angriest:', '/content/images/emoticons/angriest.png'),
    (':angry:', '/content/images/emoticons/angry.gif'),
    (':blank:', '/content/images/emoticons/blank.png'),
    (':childish:', '/content/images/emoticons/childish.png'),
    (':congrats:', '/content/images/emoticons/congrats.png'),
    (':crying:', '/content/images/emoticons/crying.gif'),
    (':dizzy:', '/content/images/emoticons/dizzy.gif'),
    (':eat:', '/content/images/emoticons/eat.gif'),
    (':evil:', '/content/images/emoticons/evil.png'),
    (':extreme:', '/content/images/emoticons/extreme.png'),
    (':glare:', '/content/images/emoticons/glare.gif'),
    (':happy:', '/content/images/emoticons/happy.gif'),
    (':horror:', '/content/images/emoticons/horror.gif'),
    (':huh:', '/content/images/emoticons/huh.png'),
    (':idea:', '/content/images/emoticons/idea.png'),
    (':jew:', '/content/images/emoticons/jew.png'),
    (':kiss:', '/content/images/emoticons/kiss.gif'),
    (':lmao:', '/content/images/emoticons/lmao.gif'),
    (':lol:', '/content/images/emoticons/lol.gif'),
    (':love:', '/content/images/emoticons/love.png'),
    (':meow:', '/content/images/emoticons/meow.png'),
    (':omg:', '/content/images/emoticons/omg.gif'),
    (':ouch:', '/content/images/emoticons/ouch.gif'),
    (':puke:', '/content/images/emoticons/puke.gif'),
    (':ruse:', '/content/images/emoticons/ruse.png'),
    (':sad:', '/content/images/emoticons/sad.png'),
    (':sigh:', '/content/images/emoticons/sigh.gif'),
    (':suspicious:', '/content/images/emoticons/suspicious.gif'),
    (':sweat:', '/content/images/emoticons/sweat.gif'),
    (':tired:', '/content/images/emoticons/tired.gif'),
    (':yay:', '/content/images/emoticons/vhappy.gif'),
    (':winxp:', '/content/images/emoticons/winxp.png'),
    (':wtf:', '/content/images/emoticons/wtf.gif'),
    (':sleep:', '/content/images/emoticons/zzz.gif'),
    (':what:', '/content/images/emoticons/what.png'),
    (':smug:', '/content/images/emoticons/smug.png');
/*!40000 ALTER TABLE `sakura_emoticons` ENABLE KEYS */;

-- Dumping data for table sakura-development.sakura_forums: ~18 rows (approximately)
/*!40000 ALTER TABLE `sakura_forums` DISABLE KEYS */;
INSERT INTO `sakura_forums` (`forum_id`, `forum_order`, `forum_name`, `forum_desc`, `forum_link`, `forum_category`, `forum_type`, `forum_icon`) VALUES
    (1, 1, 'General', 'General category', '', 0, 1, ''),
    (3, 2, 'General Discussion', 'Civilised discussions about things that don\'t have their own subforum.', '', 1, 0, 'fa-comments'),
    (15, 3, 'Staff', 'Staff discussion', '', 0, 1, ''),
    (18, 4, 'General', 'Discuss Staff Stuff.', '', 15, 0, 'fa-user-secret'),
    (19, 5, 'Purgatorii', 'This is where deleted threads rot.', '', 15, 0, 'fa-trash');
/*!40000 ALTER TABLE `sakura_forums` ENABLE KEYS */;

-- Dumping data for table sakura-development.sakura_forum_permissions: ~55 rows (approximately)
/*!40000 ALTER TABLE `sakura_forum_permissions` DISABLE KEYS */;
INSERT INTO `sakura_forum_permissions` (`forum_id`, `rank_id`, `user_id`, `forum_perms`) VALUES
    (1, 1, 0, '00000000001'),
    (1, 2, 0, '00000011111'),
    (1, 3, 0, '11111111111'),
    (1, 4, 0, '11111111111'),
    (1, 5, 0, '11111111111'),
    (1, 6, 0, '00000011111'),
    (1, 7, 0, '00000111111'),
    (1, 8, 0, '00000011111'),
    (1, 9, 0, '00000011111'),
    (1, 10, 0, '00000000001'),
    (1, 11, 0, '00000011111'),
    (15, 1, 0, '00000000000'),
    (15, 2, 0, '00000000000'),
    (15, 3, 0, '00000111111'),
    (15, 4, 0, '11111111111'),
    (15, 5, 0, '00000111111'),
    (15, 6, 0, '00000000000'),
    (15, 7, 0, '00000111111'),
    (15, 8, 0, '00000000000'),
    (15, 9, 0, '00000000000'),
    (15, 10, 0, '00000000000'),
    (15, 11, 0, '00000000000');
/*!40000 ALTER TABLE `sakura_forum_permissions` ENABLE KEYS */;

-- Dumping data for table sakura-development.sakura_optionfields: ~3 rows (approximately)
/*!40000 ALTER TABLE `sakura_optionfields` DISABLE KEYS */;
INSERT INTO `sakura_optionfields` (`option_id`, `option_name`, `option_description`, `option_type`, `option_permission`) VALUES
    ('profileBackgroundSiteWide', 'Display profile background site wide', 'This will make the profile background you set on your profile appear on the entire site (except on other profiles).', 'checkbox', 'CHANGE_BACKGROUND'),
    ('useMisaki', 'Use the testing style', 'This will make the site use the new Misaki style instead of Yuuno.', 'checkbox', 'ALTER_PROFILE');
/*!40000 ALTER TABLE `sakura_optionfields` ENABLE KEYS */;

-- Dumping data for table sakura-development.sakura_permissions: ~11 rows (approximately)
/*!40000 ALTER TABLE `sakura_permissions` DISABLE KEYS */;
INSERT INTO `sakura_permissions` (`rank_id`, `user_id`, `permissions_site`, `permissions_manage`) VALUES
    (1, 0, '00000000000000000000000000000001', '00'),
    (2, 0, '11110000111111111100111101101100', '00'),
    (3, 0, '11110001111111111111111111111100', '11'),
    (4, 0, '11110111111111111111111111111100', '11'),
    (5, 0, '11110001111111111111111111111100', '11'),
    (6, 0, '11110000111111111100111101101100', '00'),
    (7, 0, '11110001111111111111111111111100', '01'),
    (8, 0, '11110001111111111111111111111100', '00'),
    (9, 0, '11110001111111111111111111111100', '00'),
    (10, 0, '11110000000011010100101000100010', '00'),
    (11, 0, '11110000111111111100111101101100', '00');
/*!40000 ALTER TABLE `sakura_permissions` ENABLE KEYS */;

-- Dumping data for table sakura-development.sakura_profilefields: ~12 rows (approximately)
/*!40000 ALTER TABLE `sakura_profilefields` DISABLE KEYS */;
INSERT INTO `sakura_profilefields` (`field_id`, `field_name`, `field_type`, `field_link`, `field_linkformat`, `field_description`, `field_additional`) VALUES
    (1, 'Website', 'url', 1, '{{ VAL }}', 'URL to your website', ''),
    (2, 'Twitter', 'text', 1, 'https://twitter.com/{{ VAL }}', 'Your @twitter Username', ''),
    (3, 'BitBucket', 'text', 1, 'https://bitbucket.org/{{ VAL }}', 'Your BitBucket Username', ''),
    (4, 'Skype', 'text', 1, 'skype:{{ VAL }}?userinfo', 'Your Skype Username', ''),
    (5, 'YouTube', 'text', 0, '', 'ID or Username excluding http://youtube.com/*/', '{"youtubetype": ["checkbox", "I <b>do not</b> have a Channel Username (url looks like https://www.youtube.com/channel/UCXZcw5hw5C7Neto-T_nRXBQ)."]}'),
    (6, 'SoundCloud', 'text', 1, 'https://soundcloud.com/{{ VAL }}', 'Your SoundCloud username', ''),
    (7, 'Steam', 'text', 1, 'https://steamcommunity.com/id/{{ VAL }}', 'Your Steam Community Username (may differ from login username)', ''),
    (8, 'osu!', 'text', 1, 'https://osu.ppy.sh/u/{{ VAL }}', 'Your osu! Username', ''),
    (9, 'Origin', 'text', 0, '', 'Your Origin User ID', ''),
    (10, 'Xbox Live', 'text', 1, 'https://account.xbox.com/en-GB/Profile?Gamertag={{ VAL }}', 'Your Xbox User ID', ''),
    (11, 'PSN', 'text', 1, 'http://psnprofiles.com/{{ VAL }}', 'Your PSN User ID', ''),
    (12, 'Last.fm', 'text', 1, 'http://last.fm/user/{{ VAL }}', 'Your Last.fm username', '');
/*!40000 ALTER TABLE `sakura_profilefields` ENABLE KEYS */;

-- Dumping data for table sakura-development.sakura_ranks: ~11 rows (approximately)
/*!40000 ALTER TABLE `sakura_ranks` DISABLE KEYS */;
INSERT INTO `sakura_ranks` (`rank_id`, `rank_hierarchy`, `rank_name`, `rank_multiple`, `rank_hidden`, `rank_colour`, `rank_description`, `rank_title`) VALUES
    (1, 0, 'Deactivated', '', 1, '#555', 'Users that are yet to be activated or that deactivated their own account.', 'Deactivated'),
    (2, 1, 'Regular user', 's', 0, 'inherit', 'Regular users with regular permissions.', 'Regular user'),
    (3, 3, 'Site moderator', 's', 0, '#FA3703', 'Users with special permissions like being able to ban and modify users if needed.', 'Moderator'),
    (4, 4, 'Administrator', 's', 0, '#824CA0', 'Users that manage the server and everything around that.', 'Administrator'),
    (5, 3, 'Developer', 's', 0, '#6EAC0A', 'Users that either create or test new features of the site.', 'Developer'),
    (6, 1, 'Bot', 's', 1, '#9E8DA7', 'Reserved user accounts for services.', 'Bot'),
    (7, 2, 'Chat moderator', 's', 0, '#09F', 'Moderators of the chat room.', 'Moderator'),
    (8, 1, 'Tenshi', '', 0, '#EE9400', 'Users that bought premium to help us keep the site and its services alive!', 'Tenshi'),
    (9, 1, 'Alumnii', '', 0, '#FF69B4', 'People who have made big contributions to the community but have moved on.', 'Alumni'),
    (10, 0, 'Restricted', '', 1, '#666', 'Users that are restricted.', 'Restricted'),
    (11, 1, 'Early Supporter', 's', 0, '#0049EE', 'User that donated before the premium system.', 'Early Supporter');
/*!40000 ALTER TABLE `sakura_ranks` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
