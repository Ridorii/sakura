<?php
/*
 * Router paths
 */

// Define namespace
namespace Sakura;

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;

// Check if logged out
Routerv1::filter('logoutCheck', function () {
    if (CurrentSession::$user->isActive()) {
        throw new HttpRouteNotFoundException();
    }
});

// Check if logged in
Routerv1::filter('loginCheck', function () {
    if (!CurrentSession::$user->isActive()) {
        throw new HttpMethodNotAllowedException();
    }
});

// Maintenance check
Routerv1::filter('maintenance', function () {
    if (config('general.maintenance')) {
        CurrentSession::stop();
        http_response_code(503);
        return view('errors/503');
    }
});

Routerv1::group(['before' => 'maintenance'], function () {
    // Meta pages
    Routerv1::get('/', 'MetaController@index', 'main.index');
    Routerv1::get('/faq', 'MetaController@faq', 'main.faq');
    Routerv1::get('/search', 'MetaController@search', 'main.search');

    // Auth
    Routerv1::group(['before' => 'logoutCheck'], function () {
        Routerv1::get('/login', 'AuthController@login', 'auth.login');
        Routerv1::post('/login', 'AuthController@login', 'auth.login');
        Routerv1::get('/register', 'AuthController@register', 'auth.register');
        Routerv1::post('/register', 'AuthController@register', 'auth.register');
        Routerv1::get('/resetpassword', 'AuthController@resetPassword', 'auth.resetpassword');
        Routerv1::post('/resetpassword', 'AuthController@resetPassword', 'auth.resetpassword');
        Routerv1::get('/reactivate', 'AuthController@reactivate', 'auth.reactivate');
        Routerv1::post('/reactivate', 'AuthController@reactivate', 'auth.reactivate');
        Routerv1::get('/activate', 'AuthController@activate', 'auth.activate');
    });
    Routerv1::group(['before' => 'loginCheck'], function () {
        Routerv1::get('/logout', 'AuthController@logout', 'auth.logout');
        Routerv1::post('/logout', 'AuthController@logout', 'auth.logout');
    });

    // Link compatibility layer, prolly remove this in like a year
    Routerv1::get('/r/{id}', function ($id) {
        header("Location: /p/{$id}");
    });
    Routerv1::get('/p/{id}', function ($id) {
        $resolve = [
            'terms' => 'info.terms',
            'contact' => 'info.contact',
            'rules' => 'info.rules',
            'welcome' => 'info.welcome',
            //'profileapi' => 'api.manage.index',
            'chat' => 'chat.redirect',
            'irc' => 'chat.irc',
            'feedback' => 'forums.index',
            'mcp' => 'manage.index',
            'mcptest' => 'manage.index',
            //'report' => 'report.something',
            //'osu' => 'eventual link to flashii team',
            'everlastingness' => 'https://i.flash.moe/18661469927746.txt',
            'fuckingdone' => 'https://i.flash.moe/18671469927761.txt',
        ];

        if (!array_key_exists($id, $resolve)) {
            throw new \Phroute\Phroute\Exception\HttpRouteNotFoundException();
        }

        $link = $resolve[$id];

        header("Location: " . (substr($link, 0, 4) === 'http' ? $link : route($link)));
    });

    // Info
    Routerv1::group(['prefix' => 'info'], function () {
        Routerv1::get('/terms', 'InfoController@terms', 'info.terms');
        Routerv1::get('/privacy', 'InfoController@privacy', 'info.privacy');
        Routerv1::get('/contact', 'InfoController@contact', 'info.contact');
        Routerv1::get('/rules', 'InfoController@rules', 'info.rules');
        Routerv1::get('/welcome', 'InfoController@welcome', 'info.welcome');
    });

    // Status
    Routerv1::group(['prefix' => 'status'], function () {
        Routerv1::get('/', 'StatusController@index', 'status.index');
    });

    // News
    Routerv1::group(['prefix' => 'news'], function () {
        Routerv1::get('/{category:c}?', 'NewsController@category', 'news.category');
        Routerv1::get('/post/{id:i}', 'NewsController@post', 'news.post');
    });

    // Chat
    Routerv1::group(['prefix' => 'chat'], function () {
        Routerv1::get('/redirect', 'ChatController@redirect', 'chat.redirect');
        Routerv1::get('/settings', 'ChatController@settings', 'chat.settings');
        Routerv1::get('/auth', 'ChatController@auth', 'chat.auth');
        Routerv1::get('/resolve', 'ChatController@resolve', 'chat.resolve');
        Routerv1::get('/irc', 'ChatController@irc', 'chat.irc');
    });

    // Authentication for the "old" chat
    Routerv1::get('/web/sock-auth.php', 'ChatController@authLegacy');

    // Forum
    Routerv1::group(['prefix' => 'forum'], function () {
        // Post
        Routerv1::group(['prefix' => 'post'], function () {
            Routerv1::get('/{id:i}', 'Forum.PostController@find', 'forums.post');
            Routerv1::group(['before' => 'loginCheck'], function () {
                Routerv1::get('/{id:i}/raw', 'Forum.PostController@raw', 'forums.post.raw');
                Routerv1::get('/{id:i}/delete', 'Forum.PostController@delete', 'forums.post.delete');
                Routerv1::post('/{id:i}/delete', 'Forum.PostController@delete', 'forums.post.delete');
                Routerv1::post('/{id:i}/edit', 'Forum.PostController@edit', 'forums.post.edit');
            });
        });

        // Topic
        Routerv1::group(['prefix' => 'topic'], function () {
            Routerv1::get('/{id:i}', 'Forum.TopicController@view', 'forums.topic');
            Routerv1::get('/{id:i}/sticky', 'Forum.TopicController@sticky', 'forums.topic.sticky');
            Routerv1::get('/{id:i}/announce', 'Forum.TopicController@announce', 'forums.topic.announce');
            Routerv1::get('/{id:i}/lock', 'Forum.TopicController@lock', 'forums.topic.lock');
            Routerv1::get('/{id:i}/delete', 'Forum.TopicController@delete', 'forums.topic.delete');
            Routerv1::get('/{id:i}/restore', 'Forum.TopicController@restore', 'forums.topic.restore');
            Routerv1::get('/{id:i}/move', 'Forum.TopicController@move', 'forums.topic.move');
            Routerv1::post('/{id:i}/reply', 'Forum.TopicController@reply', 'forums.topic.reply');
        });

        // Forum
        Routerv1::get('/', 'Forum.ForumController@index', 'forums.index');
        Routerv1::get('/{id:i}', 'Forum.ForumController@forum', 'forums.forum');
        Routerv1::group(['before' => 'loginCheck'], function () {
            Routerv1::get('/{id:i}/mark', 'Forum.ForumController@markRead', 'forums.mark');
            Routerv1::get('/{id:i}/new', 'Forum.TopicController@create', 'forums.new');
            Routerv1::post('/{id:i}/new', 'Forum.TopicController@create', 'forums.new');
        });
    });

    // Members
    Routerv1::group(['prefix' => 'members', 'before' => 'loginCheck'], function () {
        Routerv1::get('/', 'UserController@members', 'members.index');
        Routerv1::get('/{rank:i}', 'UserController@members', 'members.rank');
    });

    // User
    Routerv1::group(['prefix' => 'u'], function () {
        Routerv1::get('/{id}', 'UserController@profile', 'user.profile');
        Routerv1::get('/{id}/report', 'UserController@report', 'user.report');

        Routerv1::get('/{id}/nowplaying', 'UserController@nowPlaying', 'user.nowplaying');

        Routerv1::get('/{id}/avatar', 'FileController@avatar', 'user.avatar');
        Routerv1::post('/{id}/avatar', 'FileController@avatar', 'user.avatar');
        Routerv1::delete('/{id}/avatar', 'FileController@avatar', 'user.avatar');

        Routerv1::get('/{id}/background', 'FileController@background', 'user.background');
        Routerv1::post('/{id}/background', 'FileController@background', 'user.background');
        Routerv1::delete('/{id}/background', 'FileController@background', 'user.background');

        Routerv1::get('/{id}/header', 'FileController@header', 'user.header');
        Routerv1::post('/{id}/header', 'FileController@header', 'user.header');
        Routerv1::delete('/{id}/header', 'FileController@header', 'user.header');
    });

    // Notifications
    Routerv1::group(['prefix' => 'notifications'], function () {
        Routerv1::get('/', 'NotificationsController@notifications', 'notifications.get');
        Routerv1::get('/{id}/mark', 'NotificationsController@mark', 'notifications.mark');
    });

    // Comments
    Routerv1::group(['prefix' => 'comments', 'before' => 'loginCheck'], function () {
        Routerv1::post('/{category:c}/post/{reply:i}?', 'CommentsController@post', 'comments.category.post');
        Routerv1::post('/{id:i}/delete', 'CommentsController@delete', 'comments.comment.delete');
        Routerv1::post('/{id:i}/vote', 'CommentsController@vote', 'comments.comment.vote');
    });

    // Comments
    Routerv1::group(['prefix' => 'friends', 'before' => 'loginCheck'], function () {
        Routerv1::post('/{id:i}/add', 'FriendsController@add', 'friends.add');
        Routerv1::post('/{id:i}/remove', 'FriendsController@remove', 'friends.remove');
    });

    // Premium
    Routerv1::group(['prefix' => 'support', 'before' => 'loginCheck'], function () {
        Routerv1::get('/', 'PremiumController@index', 'premium.index');
        Routerv1::get('/error', 'PremiumController@error', 'premium.error');
        Routerv1::get('/handle', 'PremiumController@handle', 'premium.handle');
        Routerv1::get('/complete', 'PremiumController@complete', 'premium.complete');
        Routerv1::post('/purchase', 'PremiumController@purchase', 'premium.purchase');
    });

    // Helpers
    Routerv1::group(['prefix' => 'helper'], function () {
        // BBcode
        Routerv1::group(['prefix' => 'bbcode', 'before' => 'loginCheck'], function () {
            Routerv1::post('/parse', 'HelperController@bbcodeParse', 'helper.bbcode.parse');
        });
    });

    // Settings
    Routerv1::group(['prefix' => 'settings', 'before' => 'loginCheck'], function () {
        Routerv1::get('/', function () {
            $route = Routerv1::route('settings.account.profile');
            return header("Location: {$route}");
        }, 'settings.index');

        // Account section
        Routerv1::group(['prefix' => 'account'], function () {
            Routerv1::get('/', function () {
                $route = Routerv1::route('settings.account.profile');
                return header("Location: {$route}");
            });

            Routerv1::get('/profile', 'Settings.AccountController@profile', 'settings.account.profile');
            Routerv1::post('/profile', 'Settings.AccountController@profile', 'settings.account.profile');
            Routerv1::get('/details', 'Settings.AccountController@details', 'settings.account.details');
            Routerv1::post('/details', 'Settings.AccountController@details', 'settings.account.details');
            Routerv1::get('/ranks', 'Settings.AccountController@ranks', 'settings.account.ranks');
            Routerv1::post('/ranks', 'Settings.AccountController@ranks', 'settings.account.ranks');
            Routerv1::get('/userpage', 'Settings.AccountController@userpage', 'settings.account.userpage');
            Routerv1::post('/userpage', 'Settings.AccountController@userpage', 'settings.account.userpage');
            Routerv1::get('/signature', 'Settings.AccountController@signature', 'settings.account.signature');
            Routerv1::post('/signature', 'Settings.AccountController@signature', 'settings.account.signature');
        });

        // Friends section
        Routerv1::group(['prefix' => 'friends'], function () {
            Routerv1::get('/', function () {
                $route = Routerv1::route('settings.friends.listing');
                return header("Location: {$route}");
            });

            Routerv1::get('/listing', 'Settings.FriendsController@listing', 'settings.friends.listing');
            Routerv1::get('/requests', 'Settings.FriendsController@requests', 'settings.friends.requests');
        });

        // Notifications section
        Routerv1::group(['prefix' => 'notifications'], function () {
            Routerv1::get('/', function () {
                $route = Routerv1::route('settings.notifications.history');
                return header("Location: {$route}");
            });

            Routerv1::get('/history', 'Settings.NotificationsController@history', 'settings.notifications.history');
        });

        // Advanced section
        Routerv1::group(['prefix' => 'advanced'], function () {
            Routerv1::get('/', function () {
                $route = Routerv1::route('settings.advanced.sessions');
                return header("Location: {$route}");
            });

            Routerv1::get('/sessions', 'Settings.AdvancedController@sessions', 'settings.advanced.sessions');
            Routerv1::post('/sessions', 'Settings.AdvancedController@sessions', 'settings.advanced.sessions');
            Routerv1::get('/deactivate', 'Settings.AdvancedController@deactivate', 'settings.advanced.deactivate');
            Routerv1::post('/deactivate', 'Settings.AdvancedController@deactivate', 'settings.advanced.deactivate');
        });
    });

    // Settings
    Routerv1::group(['prefix' => 'manage', 'before' => 'loginCheck'], function () {
        Routerv1::get('/', function () {
            $route = Routerv1::route('manage.overview.index');
            return header("Location: {$route}");
        }, 'manage.index');

        // Overview section
        Routerv1::group(['prefix' => 'overview'], function () {
            Routerv1::get('/', function () {
                $route = Routerv1::route('manage.overview.index');
                return header("Location: {$route}");
            });

            Routerv1::get('/index', 'Manage.OverviewController@index', 'manage.overview.index');
            Routerv1::get('/data', 'Manage.OverviewController@data', 'manage.overview.data');
        });
    });
// Management
    /*
 * Forums
 * - Manage
 * - Settings
 * Comments
 * - Manage
 * Users
 * - Manage users
 * - Manage ranks
 * - Profile fields
 * - Option fields
 * - Bans and restrictions
 * - Warnings
 * Permissions
 * - Site
 * - Management
 * - Forum
 * Logs
 * - Actions
 * - Management
 * - Errors
 */
});
