<?php
/*
 * URL management
 */

namespace Sakura;

/**
 * Class Urls
 * @package Sakura
 */
class Urls
{
    // Unformatted links [0] = no mod_rewrite, [1] = mod_rewrite
    protected $urls = [

        // General site sections
        'SITE_HOME' => [
            '/',
            '/',
        ],
        'SITE_NEWS' => [
            '/news.php',
            '/news',
        ],
        'SITE_NEWS_PAGE' => [
            '/news.php?page=%u',
            '/news/p%u',
        ],
        'SITE_NEWS_POST' => [
            '/news.php?id=%u',
            '/news/%u',
        ],
        'SITE_NEWS_RSS' => [
            '/news.php?xml=true',
            '/news.xml',
        ],
        'SITE_SEARCH' => [
            '/search.php',
            '/search',
        ],
        'SITE_PREMIUM' => [
            '/support.php',
            '/support',
        ],
        'SITE_DONATE_TRACK' => [
            '/support.php?tracker=true',
            '/support/tracker',
        ],
        'SITE_DONATE_TRACK_PAGE' => [
            '/support.php?tracker=true&page=%u',
            '/support/tracker/%u',
        ],
        'SITE_FAQ' => [
            '/faq.php',
            '/faq',
        ],
        'SITE_LOGIN' => [
            '/authenticate.php',
            '/login',
        ],
        'SITE_LOGOUT' => [
            '/authenticate.php',
            '/logout',
        ],
        'SITE_REGISTER' => [
            '/authenticate.php',
            '/register',
        ],
        'SITE_FORGOT_PASSWORD' => [
            '/authenticate.php',
            '/forgotpassword',
        ],
        'SITE_ACTIVATE' => [
            '/authenticate.php',
            '/activate',
        ],
        'INFO_PAGE' => [
            '/index.php?p=%s',
            '/p/%s',
        ],
        'AUTH_ACTION' => [
            '/authenticate.php',
            '/authenticate',
        ],

        // Memberlist
        'MEMBERLIST_INDEX' => [
            '/members.php',
            '/members',
        ],
        'MEMBERLIST_SORT' => [
            '/members.php?sort=%s',
            '/members/%s',
        ],
        'MEMBERLIST_RANK' => [
            '/members.php?rank=%u',
            '/members/%u',
        ],
        'MEMBERLIST_PAGE' => [
            '/members.php?page=%u',
            '/members/p%u',
        ],
        'MEMBERLIST_SORT_RANK' => [
            '/members.php?sort=%s&rank=%u',
            '/members/%s/%u',
        ],
        'MEMBERLIST_RANK_PAGE' => [
            '/members.php?rank=%u&page=%u',
            '/members/%u/p%u',
        ],
        'MEMBERLIST_SORT_PAGE' => [
            '/members.php?sort=%s&page=%u',
            '/members/%s/p%u',
        ],
        'MEMBERLIST_ALL' => [
            '/members.php?sort=%s&rank=%u&page=%u',
            '/members/%s/%u/p%u',
        ],

        // Forums
        'FORUM_INDEX' => [
            '/index.php?forum=true',
            '/forum',
        ],
        'FORUM_SUB' => [
            '/viewforum.php?f=%u',
            '/forum/%u',
        ],
        'FORUM_THREAD' => [
            '/viewtopic.php?t=%u',
            '/forum/thread/%u',
        ],
        'FORUM_POST' => [
            '/viewtopic.php?p=%u',
            '/forum/post/%u',
        ],
        'FORUM_REPLY' => [
            '/posting.php?t=%u',
            '/forum/thread/%u/reply',
        ],
        'FORUM_NEW_THREAD' => [
            '/posting.php?f=%u',
            '/forum/%u/new',
        ],
        'FORUM_EDIT_POST' => [
            '/posting.php?p=%1$u&edit=%1$u',
            '/forum/post/%u/edit',
        ],
        'FORUM_DELETE_POST' => [
            '/posting.php?p=%1$u&delete=%1$u',
            '/forum/post/%u/delete',
        ],
        'FORUM_QUOTE_POST' => [
            '/posting.php?p=%1$u&quote=%1$u',
            '/forum/post/%u/quote',
        ],

        // Image serve references
        'IMAGE_AVATAR' => [
            '/imageserve.php?m=avatar&u=%u',
            '/a/%u',
        ],
        'IMAGE_BACKGROUND' => [
            '/imageserve.php?m=background&u=%u',
            '/bg/%u',
        ],
        'IMAGE_HEADER' => [
            '/imageserve.php?m=header&u=%u',
            '/u/%u/header',
        ],

        // User actions
        'USER_LOGOUT' => [
            '/authenticate.php?mode=logout&time=%u&session=%s&redirect=%s',
            '/logout?mode=logout&time=%u&session=%s&redirect=%s',
        ],
        'USER_REPORT' => [
            '/report.php?mode=user&u=%u',
            '/u/%u/report',
        ],
        'USER_PROFILE' => [
            '/profile.php?u=%s',
            '/u/%s',
        ],
        'USER_COMMENTS' => [
            '/profile.php?u=%u&view=comments',
            '/u/%u/comments',
        ],
        'USER_FRIENDS' => [
            '/profile.php?u=%u&view=friends',
            '/u/%u/friends',
        ],
        'USER_GROUPS' => [
            '/profile.php?u=%u&view=groups',
            '/u/%u/groups',
        ],
        'USER_THREADS' => [
            '/profile.php?u=%u&view=threads',
            '/u/%u/threads',
        ],
        'USER_POSTS' => [
            '/profile.php?u=%u&view=posts',
            '/u/%u/posts',
        ],
        'USER_GROUP' => [
            '/group.php?g=%u',
            '/g/%u',
        ],

        // Settings urls
        'SETTINGS_INDEX' => [
            '/settings.php',
            '/settings',
        ],
        'SETTING_CAT' => [
            '/settings.php?cat=%s',
            '/settings/%s',
        ],
        'SETTING_MODE' => [
            '/settings.php?cat=%s&mode=%s',
            '/settings/%s/%s',
        ],
        'SETTING_PAGE' => [
            '/settings.php?cat=%s&mode=%s&page=%u',
            '/settings/%s/%s/p%u',
        ],
        'REQUEST_NOTIFICATIONS' => [
            '/settings.php?request-notifications=true',
            '/notifications',
        ],

        // Friend Actions
        'FRIEND_ACTION' => [
            '/settings.php?friend-action=true',
            '/friends',
        ],
        'FRIEND_ADD' => [
            '/settings.php?friend-action=true&add=%u&session=%s&time=%u&redirect=%s',
            '/friends?add=%u&session=%s&time=%u&redirect=%s',
        ],
        'FRIEND_REMOVE' => [
            '/settings.php?friend-action=true&remove=%u&session=%s&time=%u&redirect=%s',
            '/friends?remove=%u&session=%s&time=%u&redirect=%s',
        ],

        // Manage urls
        'MANAGE_INDEX' => [
            '/manage.php',
            '/manage',
        ],
        'MANAGE_CAT' => [
            '/manage.php?cat=%s',
            '/manage/%s',
        ],
        'MANAGE_MODE' => [
            '/manage.php?cat=%s&mode=%s',
            '/manage/%s/%s',
        ],

        // Comments urls
        'COMMENT_POST' => [
            '/settings.php?comment-action=true',
            '/comments',
        ],
        'COMMENT_VOTE' => [
            '/settings.php?comment-action=true&id=%u&mode=vote&state=%u&category=%s&session=%s',
            '/comments?id=%u&mode=vote&state=%u&category=%s&session=%s',
        ],
        'COMMENT_DELETE' => [
            '/settings.php?comment-action=true&id=%u&category=%s&mode=delete&session=%s',
            '/comments?id=%u&mode=delete&category=%s&session=%s',
        ],

    ];

    // Get a formatted url
    public function format($lid, $args = [], $rewrite = null)
    {

        // Check if the requested url exists
        if (!array_key_exists($lid, $this->urls)) {
            return null;
        }

        // Check if mod_rewrite is enabled
        $rewrite = ($rewrite === null ? Configuration::getConfig('url_rewrite') : $rewrite) ? 1 : 0;

        // Format urls
        $formatted = vsprintf($this->urls[$lid][$rewrite], $args);

        // Return the formatted url
        return $formatted;

    }
}
