<?php
/*
 * Router paths
 */

// Define namespace
namespace Sakura;

use Phroute\Phroute\Exception\HttpRouteNotFoundException;

// Maintenance check
Router::filter('maintenance', function () {
    if (config('general.maintenance')) {
        CurrentSession::stop();
        http_response_code(503);
        return view('errors/503');
    }
});

Router::group(['before' => 'maintenance'], function () {
    // Meta pages
    Router::get('/', 'MetaController@index', 'main.index');
    Router::get('/faq', 'MetaController@faq', 'main.faq');
    Router::get('/search', 'MetaController@search', 'main.search');

    // Auth
    Router::get('/login', 'AuthController@login', 'auth.login');
    Router::post('/login', 'AuthController@login', 'auth.login');
    Router::get('/register', 'AuthController@register', 'auth.register');
    Router::post('/register', 'AuthController@register', 'auth.register');
    Router::get('/resetpassword', 'AuthController@resetPassword', 'auth.resetpassword');
    Router::post('/resetpassword', 'AuthController@resetPassword', 'auth.resetpassword');
    Router::get('/reactivate', 'AuthController@reactivate', 'auth.reactivate');
    Router::post('/reactivate', 'AuthController@reactivate', 'auth.reactivate');
    Router::get('/activate', 'AuthController@activate', 'auth.activate');
    Router::get('/logout', 'AuthController@logout', 'auth.logout');
    Router::post('/logout', 'AuthController@logout', 'auth.logout');

    // Link compatibility layer, prolly remove this in like a year
    Router::get('/r/{id}', function ($id) {
        redirect("/p/{$id}");
    });
    Router::get('/p/{id}', function ($id) {
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

        redirect(substr($link, 0, 4) === 'http' ? $link : route($link));
    });

    // Info
    Router::group(['prefix' => 'info'], function () {
        Router::get('/terms', 'InfoController@terms', 'info.terms');
        Router::get('/privacy', 'InfoController@privacy', 'info.privacy');
        Router::get('/contact', 'InfoController@contact', 'info.contact');
        Router::get('/rules', 'InfoController@rules', 'info.rules');
        Router::get('/welcome', 'InfoController@welcome', 'info.welcome');
    });

    // Status
    Router::group(['prefix' => 'status'], function () {
        Router::get('/', 'StatusController@index', 'status.index');
    });

    // News
    Router::group(['prefix' => 'news'], function () {
        Router::get('/{category:c}?', 'NewsController@category', 'news.category');
        Router::get('/post/{id:i}', 'NewsController@post', 'news.post');
    });

    // Chat
    Router::group(['prefix' => 'chat'], function () {
        Router::get('/redirect', 'ChatController@redirect', 'chat.redirect');
        Router::get('/settings', 'ChatController@settings', 'chat.settings');
        Router::get('/auth', 'ChatController@auth', 'chat.auth');
        Router::get('/resolve', 'ChatController@resolve', 'chat.resolve');
        Router::get('/irc', 'ChatController@irc', 'chat.irc');
    });

    // Authentication for the "old" chat
    Router::get('/web/sock-auth.php', 'ChatController@authLegacy');

    // Forum
    Router::group(['prefix' => 'forum'], function () {
        // Post
        Router::group(['prefix' => 'post'], function () {
            Router::get('/{id:i}', 'Forum.PostController@find', 'forums.post');
            Router::delete('/{id:i}', 'Forum.PostController@delete', 'forums.post.delete');
            Router::get('/{id:i}/raw', 'Forum.PostController@raw', 'forums.post.raw');
            Router::post('/{id:i}/edit', 'Forum.PostController@edit', 'forums.post.edit');
        });

        // Topic
        Router::group(['prefix' => 'topic'], function () {
            Router::get('/{id:i}', 'Forum.TopicController@view', 'forums.topic');
            Router::get('/{id:i}/sticky', 'Forum.TopicController@sticky', 'forums.topic.sticky');
            Router::get('/{id:i}/announce', 'Forum.TopicController@announce', 'forums.topic.announce');
            Router::get('/{id:i}/lock', 'Forum.TopicController@lock', 'forums.topic.lock');
            Router::get('/{id:i}/delete', 'Forum.TopicController@delete', 'forums.topic.delete');
            Router::get('/{id:i}/restore', 'Forum.TopicController@restore', 'forums.topic.restore');
            Router::get('/{id:i}/move', 'Forum.TopicController@move', 'forums.topic.move');
            Router::post('/{id:i}/reply', 'Forum.TopicController@reply', 'forums.topic.reply');
        });

        // Forum
        Router::get('/', 'Forum.ForumController@index', 'forums.index');
        Router::get('/{id:i}', 'Forum.ForumController@forum', 'forums.forum');
        Router::get('/{id:i}/mark', 'Forum.ForumController@markRead', 'forums.mark');
        Router::get('/{id:i}/new', 'Forum.TopicController@create', 'forums.new');
        Router::post('/{id:i}/new', 'Forum.TopicController@create', 'forums.new');
    });

    // Members
    Router::group(['prefix' => 'members'], function () {
        Router::get('/', 'UserController@members', 'members.index');
        Router::get('/{rank:i}', 'UserController@members', 'members.rank');
    });

    // User
    Router::group(['prefix' => 'u'], function () {
        Router::get('/{id}', 'UserController@profile', 'user.profile');
        Router::get('/{id}/report', 'UserController@report', 'user.report');

        Router::get('/{id}/nowplaying', 'UserController@nowPlaying', 'user.nowplaying');

        Router::get('/{id}/avatar', 'FileController@avatar', 'user.avatar');
        Router::post('/{id}/avatar', 'FileController@avatar', 'user.avatar');
        Router::delete('/{id}/avatar', 'FileController@avatar', 'user.avatar');

        Router::get('/{id}/background', 'FileController@background', 'user.background');
        Router::post('/{id}/background', 'FileController@background', 'user.background');
        Router::delete('/{id}/background', 'FileController@background', 'user.background');

        Router::get('/{id}/header', 'FileController@header', 'user.header');
        Router::post('/{id}/header', 'FileController@header', 'user.header');
        Router::delete('/{id}/header', 'FileController@header', 'user.header');
    });

    // Notifications
    Router::group(['prefix' => 'notifications'], function () {
        Router::get('/', 'NotificationsController@notifications', 'notifications.get');
        Router::get('/{id}/mark', 'NotificationsController@mark', 'notifications.mark');
    });

    // Comments
    Router::group(['prefix' => 'comments'], function () {
        Router::post('/{category:c}/post/{reply:i}?', 'CommentsController@post', 'comments.category.post');
        Router::post('/{id:i}/delete', 'CommentsController@delete', 'comments.comment.delete');
        Router::post('/{id:i}/vote', 'CommentsController@vote', 'comments.comment.vote');
    });

    // Comments
    Router::group(['prefix' => 'friends'], function () {
        Router::post('/{id:i}/add', 'FriendsController@add', 'friends.add');
        Router::post('/{id:i}/remove', 'FriendsController@remove', 'friends.remove');
    });

    // Premium
    Router::group(['prefix' => 'support'], function () {
        Router::get('/', 'PremiumController@index', 'premium.index');
        Router::get('/error', 'PremiumController@error', 'premium.error');
        Router::get('/handle', 'PremiumController@handle', 'premium.handle');
        Router::get('/complete', 'PremiumController@complete', 'premium.complete');
        Router::post('/purchase', 'PremiumController@purchase', 'premium.purchase');
    });

    // Helpers
    Router::group(['prefix' => 'helper'], function () {
        // BBcode
        Router::group(['prefix' => 'bbcode'], function () {
            Router::post('/parse', 'HelperController@bbcodeParse', 'helper.bbcode.parse');
        });
    });

    // Settings
    Router::group(['prefix' => 'settings'], function () {
        Router::get('/', function () {
            redirect(route('settings.account.profile'));
        }, 'settings.index');

        // Account section
        Router::group(['prefix' => 'account'], function () {
            Router::get('/', function () {
                redirect(route('settings.account.profile'));
            });

            Router::get('/profile', 'Settings.AccountController@profile', 'settings.account.profile');
            Router::post('/profile', 'Settings.AccountController@profile', 'settings.account.profile');
            Router::get('/details', 'Settings.AccountController@details', 'settings.account.details');
            Router::post('/details', 'Settings.AccountController@details', 'settings.account.details');
            Router::get('/ranks', 'Settings.AccountController@ranks', 'settings.account.ranks');
            Router::post('/ranks', 'Settings.AccountController@ranks', 'settings.account.ranks');
            Router::get('/userpage', 'Settings.AccountController@userpage', 'settings.account.userpage');
            Router::post('/userpage', 'Settings.AccountController@userpage', 'settings.account.userpage');
            Router::get('/signature', 'Settings.AccountController@signature', 'settings.account.signature');
            Router::post('/signature', 'Settings.AccountController@signature', 'settings.account.signature');
        });

        // Friends section
        Router::group(['prefix' => 'friends'], function () {
            Router::get('/', function () {
                redirect(route('settings.account.listing'));
            });

            Router::get('/listing', 'Settings.FriendsController@listing', 'settings.friends.listing');
            Router::get('/requests', 'Settings.FriendsController@requests', 'settings.friends.requests');
        });

        // Advanced section
        Router::group(['prefix' => 'advanced'], function () {
            Router::get('/', function () {
                redirect(route('settings.account.sessions'));
            });

            Router::get('/sessions', 'Settings.AdvancedController@sessions', 'settings.advanced.sessions');
            Router::post('/sessions', 'Settings.AdvancedController@sessions', 'settings.advanced.sessions');
            Router::get('/deactivate', 'Settings.AdvancedController@deactivate', 'settings.advanced.deactivate');
            Router::post('/deactivate', 'Settings.AdvancedController@deactivate', 'settings.advanced.deactivate');
        });
    });

    // Settings
    Router::group(['prefix' => 'manage'], function () {
        Router::get('/', function () {
            redirect(route('manage.overview.index'));
        }, 'manage.index');

        // Overview section
        Router::group(['prefix' => 'overview'], function () {
            Router::get('/', function () {
                redirect(route('manage.overview.index'));
            });

            Router::get('/index', 'Manage.OverviewController@index', 'manage.overview.index');
            Router::get('/data', 'Manage.OverviewController@data', 'manage.overview.data');
        });
    });
});
