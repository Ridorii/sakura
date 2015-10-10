-- Adminer 4.2.2 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `sakura-development`;

INSERT INTO `sakura_bbcodes` (`bbcode_id`, `bbcode_regex`, `bbcode_replace`, `bbcode_title`, `bbcode_description`, `bbcode_display`) VALUES
(1, '/\\[b\\](.*?)\\[\\/b\\]/is',   '<b>$1</b>',    'Bold', 'Make text bold. Usage: [b]text[/b].',  1),
(2, '/\\[i\\](.*?)\\[\\/i\\]/is',   '<i>$1</i>',    'Italics',  'Make text italic. Usage: [i]text[/i].',    1),
(3, '/\\[u\\](.*?)\\[\\/u\\]/is',   '<u>$1</u>',    'Underline',    'Make text underlined. Usage: [u]text[/u].',    1),
(4, '/\\[s\\](.*?)\\[\\/s\\]/is',   '<del>$1</del>',    'Strikethrough',    'Put a line through text. Usage: [s]text[/s].', 1),
(5, '/\\[img\\]([a-zA-Z0-9\\.\\$\\-\\_\\.\\+\\*\\!\\\'\\(\\)\\/\\:\\#]+)\\[\\/img\\]/is',   '<img src=\"$1\" alt=\"Image\" />', 'Image',    'Embed an image. Usage: [img]url[/img]',    1),
(6, '/\\[url=([a-zA-Z0-9\\.\\$\\-\\_\\.\\+\\*\\!\\\'\\(\\)\\/\\:\\#]+)\\](.*?)\\[\\/url\\]/is', '<a href=\"$1\" target=\"_blank\">$2</a>',  'Link', 'Embed a URL. Usage: [url=http://google.com]Link to google![/url]', 0),
(7, '/\\[url\\]([a-zA-Z0-9\\.\\$\\-\\_\\.\\+\\*\\!\\\'\\(\\)\\/\\:\\#]+)\\[\\/url\\]/is',   '<a href=\"$1\" target=\"_blank\">$1</a>',  'Link', 'Make a link clickable (if the automatic algorithm doesn\'t do it already). Usage: [url]http://google.com[/url]',   1),
(8, '/\\[quote\\=\\\"(.+)\\\"\\](.+)\\[\\/quote]/is',   '<div class=\"quote\"><div class=\"quotee\">$1 wrote:</div><div class=\"text\">$2</div></div>', 'Quote',    'Quote a user\'s post. Usage: [quote=Flashwave]nookls is pretty[/quote]',   0),
(9, '/\\[quote\\](.+)\\[\\/quote]/is',  '<div class=\"quote\"><div class=\"quotee\">Quote:</div><div class=\"text\">$1</div></div>',    'Quote',    'Quote a user\'s post. Usage: [quote]nookls is pretty[/quote]', 1)
ON DUPLICATE KEY UPDATE `bbcode_id` = VALUES(`bbcode_id`), `bbcode_regex` = VALUES(`bbcode_regex`), `bbcode_replace` = VALUES(`bbcode_replace`), `bbcode_title` = VALUES(`bbcode_title`), `bbcode_description` = VALUES(`bbcode_description`), `bbcode_display` = VALUES(`bbcode_display`);

INSERT INTO `sakura_config` (`config_name`, `config_value`) VALUES
('recaptcha_public',    ''),
('recaptcha_private',   ''),
('charset', 'utf-8'),
('cookie_prefix',   'sakura_'),
('cookie_domain',   'sakura.dev'),
('cookie_path', '/'),
('site_style',  'yuuno'),
('manage_style',    'broomcloset'),
('smtp_server', ''),
('smtp_auth',   '0'),
('smtp_secure', ''),
('smtp_port',   ''),
('smtp_username',   ''),
('smtp_password',   ''),
('smtp_replyto_mail',   ''),
('smtp_replyto_name',   ''),
('smtp_from_email', ''),
('smtp_from_name',  ''),
('sitename',    'Sakura'),
('recaptcha',   '0'),
('require_activation',  '0'),
('require_registration_code',   '0'),
('disable_registration',    '0'),
('max_reg_keys',    '5'),
('mail_signature',  ''),
('lock_authentication', '0'),
('min_entropy', '1'),
('sitedesc',    'The script that powers Flashii.'),
('sitetags',    ''),
('username_min_length', '3'),
('username_max_length', '16'),
('site_closed', '0'),
('site_closed_reason',  ''),
('use_gzip',    '0'),
('enable_tpl_cache',    '0'),
('paypal_client_id',    ''),
('paypal_secret',   ''),
('premium_price_per_month', '1.49'),
('premium_rank_id', '8'),
('premium_amount_max',  '24'),
('alumni_rank_id',  '9'),
('url_main',    'sakura.dev'),
('front_page_news_posts',   '3'),
('date_format', 'D Y-m-d H:i:s T'),
('news_posts_per_page', '3'),
('avatar_min_width',    '20'),
('avatar_min_height',   '20'),
('avatar_max_height',   '512'),
('avatar_max_width',    '512'),
('avatar_max_fsize',    '2097152'),
('url_api', 'api.sakura.dev'),
('content_path',    '/content'),
('user_uploads',    'uploads'),
('no_background_img',   'public/content/pixel.png'),
('no_header_img',   'public/content/images/triangles.png'),
('pixel_img',   'public/content/pixel.png'),
('background_max_fsize',    '5242880'),
('background_max_width',    '2560'),
('background_max_height',   '1440'),
('background_min_height',   '16'),
('background_min_width',    '16'),
('max_online_time', '500'),
('no_avatar_img',   'public/content/data/{{ TPL }}/images/no-av.png'),
('deactivated_avatar_img',  'public/content/data/{{ TPL }}/images/deactivated-av.png'),
('banned_avatar_img',   'public/content/data/{{ TPL }}/images/banned-av.png'),
('session_check',   '4'),
('url_rewrite', '1'),
('members_per_page',    '30'),
('admin_email', 'admin@sakura.dev'),
('site_news_category',  'site-news'),
('no_cron_service', '1'),
('no_cron_interval',    '30'),
('no_cron_last',    '1444511632'),
('old_username_reserve',    '90'),
('comment_max_length',  '500'),
('comment_min_length',  '1'),
('sitelogo',    '')
ON DUPLICATE KEY UPDATE `config_name` = VALUES(`config_name`), `config_value` = VALUES(`config_value`);

INSERT INTO `sakura_emoticons` (`emote_string`, `emote_path`) VALUES
(':amu:',   '/content/images/emoticons/amu.png'),
(':angrier:',   '/content/images/emoticons/angrier.png'),
(':angriest:',  '/content/images/emoticons/angriest.png'),
(':angry:', '/content/images/emoticons/angry.gif'),
(':blank:', '/content/images/emoticons/blank.png'),
(':childish:',  '/content/images/emoticons/childish.png'),
(':congrats:',  '/content/images/emoticons/congrats.png'),
(':crying:',    '/content/images/emoticons/crying.gif'),
(':dizzy:', '/content/images/emoticons/dizzy.gif'),
(':eat:',   '/content/images/emoticons/eat.gif'),
(':evil:',  '/content/images/emoticons/evil.png'),
(':extreme:',   '/content/images/emoticons/extreme.png'),
(':glare:', '/content/images/emoticons/glare.gif'),
(':happy:', '/content/images/emoticons/happy.gif'),
(':horror:',    '/content/images/emoticons/horror.gif'),
(':huh:',   '/content/images/emoticons/huh.png'),
(':idea:',  '/content/images/emoticons/idea.png'),
(':jew:',   '/content/images/emoticons/jew.png'),
(':kiss:',  '/content/images/emoticons/kiss.gif'),
(':lmao:',  '/content/images/emoticons/lmao.gif'),
(':lol:',   '/content/images/emoticons/lol.gif'),
(':love:',  '/content/images/emoticons/love.png'),
(':meow:',  '/content/images/emoticons/meow.png'),
(':omg:',   '/content/images/emoticons/omg.gif'),
(':ouch:',  '/content/images/emoticons/ouch.gif'),
(':puke:',  '/content/images/emoticons/puke.gif'),
(':ruse:',  '/content/images/emoticons/ruse.png'),
(':sad:',   '/content/images/emoticons/sad.png'),
(':sigh:',  '/content/images/emoticons/sigh.gif'),
(':suspicious:',    '/content/images/emoticons/suspicious.gif'),
(':sweat:', '/content/images/emoticons/sweat.gif'),
(':tired:', '/content/images/emoticons/tired.gif'),
(':yay:',   '/content/images/emoticons/vhappy.gif'),
(':winxp:', '/content/images/emoticons/winxp.png'),
(':wtf:',   '/content/images/emoticons/wtf.gif'),
(':sleep:', '/content/images/emoticons/zzz.gif'),
(':what:',  '/content/images/emoticons/what.png'),
(':smug:',  '/content/images/emoticons/smug.png')
ON DUPLICATE KEY UPDATE `emote_string` = VALUES(`emote_string`), `emote_path` = VALUES(`emote_path`);

INSERT INTO `sakura_optionfields` (`option_id`, `option_name`, `option_description`, `option_type`, `option_permission`) VALUES
('disableProfileParallax',  'Disable Parallaxing',  'This will stop your background from responding to your mouse movement, this will only affect your background.',    'checkbox', 'CHANGE_BACKGROUND'),
('profileBackgroundSiteWide',   'Display profile background site wide', 'This will make the profile background you set on your profile appear on the entire site (except on other profiles).',  'checkbox', 'CREATE_BACKGROUND'),
('useMisaki',   'Use the testing style',    'This will make the site use the new Misaki style instead of Yuuno.',   'checkbox', 'ALTER_PROFILE')
ON DUPLICATE KEY UPDATE `option_id` = VALUES(`option_id`), `option_name` = VALUES(`option_name`), `option_description` = VALUES(`option_description`), `option_type` = VALUES(`option_type`), `option_permission` = VALUES(`option_permission`);

INSERT INTO `sakura_permissions` (`rank_id`, `user_id`, `permissions_site`, `permissions_manage`, `permissions_forums`, `permissions_inherit`) VALUES
(1, 0,  '00000000000000000000000000000001', '00',   '0',    '000'),
(2, 0,  '11110000111111111100111101101100', '00',   '1',    '000'),
(3, 0,  '11110001111111111111111111111100', '11',   '1',    '000'),
(4, 0,  '11111111111111111111111111111100', '11',   '1',    '000'),
(5, 0,  '11110001111111111111111111111100', '11',   '1',    '000'),
(6, 0,  '11110000111111111100111101101100', '00',   '0',    '000'),
(7, 0,  '11110001111111111111111111111100', '01',   '1',    '000'),
(8, 0,  '11110001111111111111111111111100', '00',   '1',    '000'),
(9, 0,  '11110001111111111111111111111100', '00',   '1',    '000'),
(10,    0,  '11110000000011010100101000100010', '00',   '0',    '000'),
(11,    0,  '11110000111111111100111101101100', '00',   '1',    '000')
ON DUPLICATE KEY UPDATE `rank_id` = VALUES(`rank_id`), `user_id` = VALUES(`user_id`), `permissions_site` = VALUES(`permissions_site`), `permissions_manage` = VALUES(`permissions_manage`), `permissions_forums` = VALUES(`permissions_forums`), `permissions_inherit` = VALUES(`permissions_inherit`);

INSERT INTO `sakura_profilefields` (`field_id`, `field_name`, `field_type`, `field_link`, `field_linkformat`, `field_description`, `field_additional`) VALUES
(1, 'Website',  'url',  1,  '{{ VAL }}',    'URL to your website',  ''),
(2, 'Twitter',  'text', 1,  'https://twitter.com/{{ VAL }}',    'Your @twitter Username',   ''),
(3, 'BitBucket',    'text', 1,  'https://bitbucket.org/{{ VAL }}',  'Your BitBucket Username',  ''),
(4, 'Skype',    'text', 1,  'skype:{{ VAL }}?userinfo', 'Your Skype Username',  ''),
(5, 'YouTube',  'text', 0,  '', 'ID or Username excluding http://youtube.com/*/',   '{\"youtubetype\": [\"checkbox\", \"I <b>do not</b> have a Channel Username (url looks like https://www.youtube.com/channel/UCXZcw5hw5C7Neto-T_nRXBQ).\"]}'),
(6, 'SoundCloud',   'text', 1,  'https://soundcloud.com/{{ VAL }}', 'Your SoundCloud username', ''),
(7, 'Steam',    'text', 1,  'https://steamcommunity.com/id/{{ VAL }}',  'Your Steam Community Username (may differ from login username)',   ''),
(8, 'osu!', 'text', 1,  'https://osu.ppy.sh/u/{{ VAL }}',   'Your osu! Username',   ''),
(9, 'Origin',   'text', 0,  '', 'Your Origin User ID',  ''),
(10,    'Xbox Live',    'text', 1,  'https://account.xbox.com/en-GB/Profile?Gamertag={{ VAL }}',    'Your Xbox User ID',    ''),
(11,    'PSN',  'text', 1,  'http://psnprofiles.com/{{ VAL }}', 'Your PSN User ID', ''),
(12,    'Last.fm',  'text', 1,  'http://last.fm/user/{{ VAL }}',    'Your Last.fm username',    '')
ON DUPLICATE KEY UPDATE `field_id` = VALUES(`field_id`), `field_name` = VALUES(`field_name`), `field_type` = VALUES(`field_type`), `field_link` = VALUES(`field_link`), `field_linkformat` = VALUES(`field_linkformat`), `field_description` = VALUES(`field_description`), `field_additional` = VALUES(`field_additional`);

INSERT INTO `sakura_ranks` (`rank_id`, `rank_hierarchy`, `rank_name`, `rank_multiple`, `rank_hidden`, `rank_colour`, `rank_description`, `rank_title`) VALUES
(1, 0,  'Deactivated',  '', 1,  '#555', 'Users that are yet to be activated or that deactivated their own account.',    'Deactivated'),
(2, 1,  'Regular user', 's',    0,  'inherit',  'Regular users with regular permissions.',  'Regular user'),
(3, 3,  'Site moderator',   's',    0,  '#0A0', 'Users with special permissions like being able to ban and modify users if needed.',    'Staff'),
(4, 4,  'Administrator',    's',    0,  '#C00', 'Users that manage the server and everything around that.', 'Administrator'),
(5, 3,  'Developer',    's',    0,  '#824CA0',  'Users that either create or test new features of the site.',   'Staff'),
(6, 1,  'Bot',  's',    1,  '#9E8DA7',  'Reserved user accounts for services.', 'Bot'),
(7, 2,  'Chat moderator',   's',    0,  '#09F', 'Moderators of the chat room.', 'Staff'),
(8, 1,  'Tenshi',   '', 0,  '#EE9400',  'Users that bought premium to help us keep the site and its services alive!',   'Tenshi'),
(9, 1,  'Alumnii',  '', 0,  '#FF69B4',  'People who have contributed to the community but have moved on or resigned.',  'Alumnii'),
(10,    0,  'Restricted',   's',    1,  '#333', 'Users that are restricted.',   'Restricted'),
(11,    1,  'Early Supporter',  's',    0,  '#0049EE',  'User that donated before the premium system.', 'Early Supporter')
ON DUPLICATE KEY UPDATE `rank_id` = VALUES(`rank_id`), `rank_hierarchy` = VALUES(`rank_hierarchy`), `rank_name` = VALUES(`rank_name`), `rank_multiple` = VALUES(`rank_multiple`), `rank_hidden` = VALUES(`rank_hidden`), `rank_colour` = VALUES(`rank_colour`), `rank_description` = VALUES(`rank_description`), `rank_title` = VALUES(`rank_title`);

-- 2015-10-10 21:15:55
