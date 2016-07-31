<?php
/*
 * Router paths
 */

// Define namespace
namespace Sakura;

// Check if logged out
Router::filter('logoutCheck', function () {
    if (ActiveUser::$user->isActive()) {
        $message = "You must be logged out to do that!";

        Template::vars(compact('message'));

        return Template::render('global/information');
    }
});

// Check if logged in
Router::filter('loginCheck', function () {
    if (!ActiveUser::$user->isActive()) {
        $message = "You must be logged in to do that!";

        Template::vars(compact('message'));

        return Template::render('global/information');
    }
});

// Maintenance check
Router::filter('maintenance', function () {
    if (config('general.maintenance')) {
        ActiveUser::$session->destroy();

        http_response_code(503);

        return Template::render('global/maintenance');
    }
});

Router::group(['before' => 'maintenance'], function () {
    // Meta pages
    Router::get('/', 'MetaController@index', 'main.index');
    Router::get('/faq', 'MetaController@faq', 'main.faq');
    Router::get('/search', 'MetaController@search', 'main.search');

    // Auth
    Router::group(['before' => 'logoutCheck'], function () {
        Router::get('/login', 'AuthController@loginGet', 'auth.login');
        Router::post('/login', 'AuthController@loginPost', 'auth.login');
        Router::get('/register', 'AuthController@registerGet', 'auth.register');
        Router::post('/register', 'AuthController@registerPost', 'auth.register');
        Router::get('/resetpassword', 'AuthController@resetPasswordGet', 'auth.resetpassword');
        Router::post('/resetpassword', 'AuthController@resetPasswordPost', 'auth.resetpassword');
        Router::get('/reactivate', 'AuthController@reactivateGet', 'auth.reactivate');
        Router::post('/reactivate', 'AuthController@reactivatePost', 'auth.reactivate');
        Router::get('/activate', 'AuthController@activate', 'auth.activate');
    });
    Router::group(['before' => 'loginCheck'], function () {
        Router::get('/logout', 'AuthController@logout', 'auth.logout');
    });

    // Link compatibility layer, prolly remove this in like a year
    Router::get('/r/{id}', function ($id) {
        header("Location: /p/{$id}");
    });
    Router::get('/p/{id}', function ($id) {
        $resolve = [
            'terms' => 'info.terms',
            'contact' => 'info.contact',
            'rules' => 'info.rules',
            'welcome' => 'info.welcome',
            //'profileapi' => 'api.manage.index',
            'chat' => 'chat.redirect',
            //'irc' => 'chat.irc',
            'feedback' => 'forums.index',
            'mcp' => 'manage.index',
            'mcptest' => 'manage.index',
            //'report' => 'report.something',
            //'osu' => 'eventual link to flashii team',
            //'filehost' => '???',
            //'fhscript' => '???',
            //'fhmanager' => '???',
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
    Router::group(['prefix' => 'info'], function () {
        Router::get('/terms', 'InfoController@terms', 'info.terms');
        Router::get('/privacy', 'InfoController@privacy', 'info.privacy');
        Router::get('/contact', 'InfoController@contact', 'info.contact');
        Router::get('/rules', 'InfoController@rules', 'info.rules');
        Router::get('/welcome', 'InfoController@welcome', 'info.welcome');
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
    });

    // Forum
    Router::group(['prefix' => 'forum'], function () {
        // Post
        Router::group(['prefix' => 'post'], function () {
            Router::get('/{id:i}', 'Forum.PostController@find', 'forums.post');
            Router::group(['before' => 'loginCheck'], function () {
                Router::get('/{id:i}/raw', 'Forum.PostController@raw', 'forums.post.raw');
                Router::get('/{id:i}/delete', 'Forum.PostController@delete', 'forums.post.delete');
                Router::post('/{id:i}/delete', 'Forum.PostController@delete', 'forums.post.delete');
                Router::post('/{id:i}/edit', 'Forum.PostController@edit', 'forums.post.edit');
            });
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
        Router::group(['before' => 'loginCheck'], function () {
            Router::get('/{id:i}/mark', 'Forum.ForumController@markRead', 'forums.mark');
            Router::get('/{id:i}/new', 'Forum.TopicController@create', 'forums.new');
            Router::post('/{id:i}/new', 'Forum.TopicController@create', 'forums.new');
        });
    });

    // Members
    Router::group(['prefix' => 'members', 'before' => 'loginCheck'], function () {
        Router::get('/', 'UserController@members', 'members.index');
        Router::get('/{rank:i}', 'UserController@members', 'members.rank');
    });

    // User
    Router::group(['prefix' => 'u'], function () {
        Router::get('/{id}', 'UserController@profile', 'user.profile');
        Router::get('/{id}/report', 'UserController@report', 'user.report');
        Router::get('/{id}/header', 'FileController@header', 'user.header');
    });

    // Notifications
    Router::group(['prefix' => 'notifications'], function () {
        Router::get('/', 'NotificationsController@notifications', 'notifications.get');
        Router::get('/{id}/mark', 'NotificationsController@mark', 'notifications.mark');
    });

    // Comments
    Router::group(['prefix' => 'comments', 'before' => 'loginCheck'], function () {
        Router::post('/{category:c}/post/{reply:i}?', 'CommentsController@post', 'comments.category.post');
        Router::post('/{id:i}/delete', 'CommentsController@delete', 'comments.comment.delete');
        Router::post('/{id:i}/vote', 'CommentsController@vote', 'comments.comment.vote');
    });

    // Comments
    Router::group(['prefix' => 'friends', 'before' => 'loginCheck'], function () {
        Router::post('/{id:i}/add', 'FriendsController@add', 'friends.add');
        Router::post('/{id:i}/remove', 'FriendsController@remove', 'friends.remove');
    });

    // Files
    Router::get('/a/{id}', 'FileController@avatar', 'file.avatar');
    Router::get('/bg/{id}', 'FileController@background', 'file.background');

    // Premium
    Router::group(['prefix' => 'support', 'before' => 'loginCheck'], function () {
        Router::get('/', 'PremiumController@index', 'premium.index');
        Router::get('/handle', 'PremiumController@handle', 'premium.handle');
        Router::get('/complete', 'PremiumController@complete', 'premium.complete');
        Router::post('/purchase', 'PremiumController@purchase', 'premium.purchase');
    });

    // Helpers
    Router::group(['prefix' => 'helper'], function () {
        // BBcode
        Router::group(['prefix' => 'bbcode', 'before' => 'loginCheck'], function () {
            Router::post('/parse', 'HelperController@bbcodeParse', 'helper.bbcode.parse');
        });
    });

    // Settings
    Router::group(['prefix' => 'settings', 'before' => 'loginCheck'], function () {
        Router::get('/', function () {
            $route = Router::route('settings.general.home');
            return header("Location: {$route}");
        }, 'settings.index');

        // General section
        Router::group(['prefix' => 'general'], function () {
            Router::get('/', function () {
                $route = Router::route('settings.general.home');
                return header("Location: {$route}");
            });

            Router::get('/home', 'Settings.GeneralController@home', 'settings.general.home');
            Router::get('/profile', 'Settings.GeneralController@profile', 'settings.general.profile');
            Router::post('/profile', 'Settings.GeneralController@profile', 'settings.general.profile');
            Router::get('/options', 'Settings.GeneralController@options', 'settings.general.options');
            Router::post('/options', 'Settings.GeneralController@options', 'settings.general.options');
        });

        // Friends section
        Router::group(['prefix' => 'friends'], function () {
            Router::get('/', function () {
                $route = Router::route('settings.friends.listing');
                return header("Location: {$route}");
            });

            Router::get('/listing', 'Settings.FriendsController@listing', 'settings.friends.listing');
            Router::get('/requests', 'Settings.FriendsController@requests', 'settings.friends.requests');
        });

        // Groups section
        Router::group(['prefix' => 'groups'], function () {
            Router::get('/', function () {
                $route = Router::route('settings.groups.listing');
                return header("Location: {$route}");
            });

            Router::get('/listing', 'Settings.GroupsController@listing', 'settings.groups.listing');
            Router::get('/invites', 'Settings.GroupsController@invites', 'settings.groups.invites');
        });

        // Notifications section
        Router::group(['prefix' => 'notifications'], function () {
            Router::get('/', function () {
                $route = Router::route('settings.notifications.history');
                return header("Location: {$route}");
            });

            Router::get('/history', 'Settings.NotificationsController@history', 'settings.notifications.history');
        });

        // Appearance section
        Router::group(['prefix' => 'appearance'], function () {
            Router::get('/', function () {
                $route = Router::route('settings.appearance.avatar');
                return header("Location: {$route}");
            });

            Router::get('/avatar', 'Settings.AppearanceController@avatar', 'settings.appearance.avatar');
            Router::post('/avatar', 'Settings.AppearanceController@avatar', 'settings.appearance.avatar');
            Router::get('/background', 'Settings.AppearanceController@background', 'settings.appearance.background');
            Router::post('/background', 'Settings.AppearanceController@background', 'settings.appearance.background');
            Router::get('/header', 'Settings.AppearanceController@header', 'settings.appearance.header');
            Router::post('/header', 'Settings.AppearanceController@header', 'settings.appearance.header');
            Router::get('/userpage', 'Settings.AppearanceController@userpage', 'settings.appearance.userpage');
            Router::post('/userpage', 'Settings.AppearanceController@userpage', 'settings.appearance.userpage');
            Router::get('/signature', 'Settings.AppearanceController@signature', 'settings.appearance.signature');
            Router::post('/signature', 'Settings.AppearanceController@signature', 'settings.appearance.signature');
        });

        // Account section
        Router::group(['prefix' => 'account'], function () {
            Router::get('/', function () {
                $route = Router::route('settings.account.email');
                return header("Location: {$route}");
            });

            Router::get('/email', 'Settings.AccountController@email', 'settings.account.email');
            Router::post('/email', 'Settings.AccountController@email', 'settings.account.email');
            Router::get('/username', 'Settings.AccountController@username', 'settings.account.username');
            Router::post('/username', 'Settings.AccountController@username', 'settings.account.username');
            Router::get('/title', 'Settings.AccountController@title', 'settings.account.title');
            Router::post('/title', 'Settings.AccountController@title', 'settings.account.title');
            Router::get('/password', 'Settings.AccountController@password', 'settings.account.password');
            Router::post('/password', 'Settings.AccountController@password', 'settings.account.password');
            Router::get('/ranks', 'Settings.AccountController@ranks', 'settings.account.ranks');
            Router::post('/ranks', 'Settings.AccountController@ranks', 'settings.account.ranks');
        });

        // Advanced section
        Router::group(['prefix' => 'advanced'], function () {
            Router::get('/', function () {
                $route = Router::route('settings.advanced.sessions');
                return header("Location: {$route}");
            });

            Router::get('/sessions', 'Settings.AdvancedController@sessions', 'settings.advanced.sessions');
            Router::post('/sessions', 'Settings.AdvancedController@sessions', 'settings.advanced.sessions');
            Router::get('/deactivate', 'Settings.AdvancedController@deactivate', 'settings.advanced.deactivate');
            Router::post('/deactivate', 'Settings.AdvancedController@deactivate', 'settings.advanced.deactivate');
        });
    });

    // Settings
    Router::group(['prefix' => 'manage', 'before' => 'loginCheck'], function () {
        Router::get('/', function () {
            $route = Router::route('manage.overview');
            return header("Location: {$route}");
        }, 'manage.index');

        // Overview section
        Router::group(['prefix' => 'overview'], function () {
            Router::get('/', function () {
                $route = Router::route('manage.overview.index');
                return header("Location: {$route}");
            }, 'manage.overview');

            Router::get('/index', 'Manage.OverviewController@index', 'manage.overview.index');
            Router::get('/data', 'Manage.OverviewController@data', 'manage.overview.data');
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
