<?php
/*
 * Router paths
 */

// Define namespace
namespace Sakura;

// Check if logged out
Router::filter('logoutCheck', function () {
    global $currentUser;

    if ($currentUser->id !== 0) {
        $message = "You must be logged out to do that!";

        Template::vars(['page' => compact('message')]);

        return Template::render('global/information');
    }
});

// Check if logged in
Router::filter('loginCheck', function () {
    global $currentUser;

    if ($currentUser->id === 0) {
        $message = "You must be logged in to do that!";

        Template::vars(['page' => compact('message')]);

        return Template::render('global/information');
    }
});

// Meta pages
Router::get('/', 'MetaController@index', 'main.index');
Router::get('/faq', 'MetaController@faq', 'main.faq');
Router::get('/search', 'MetaController@search', 'main.search');
Router::get('/p/{id}', 'MetaController@infoPage', 'main.infopage');

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

// News
Router::group(['prefix' => 'news'], function () {
    Router::get('/{category:c}?', 'NewsController@category', 'news.category');
    Router::get('/post/{id:i}', 'NewsController@post', 'news.post');
});

// Forum
Router::group(['prefix' => 'forum'], function () {
    // Post
    Router::group(['prefix' => 'post'], function () {
        Router::get('/{id:i}', 'ForumController@post', 'forums.post');
        Router::group(['before' => 'loginCheck'], function () {
            Router::get('/{id:i}/raw', 'ForumController@postRaw', 'forums.post.raw');
            Router::get('/{id:i}/delete', 'ForumController@deletePost', 'forums.post.delete');
            Router::post('/{id:i}/delete', 'ForumController@deletePost', 'forums.post.delete');
            Router::post('/{id:i}/edit', 'ForumController@editPost', 'forums.post.edit');
        });
    });

    // Thread
    Router::group(['prefix' => 'thread'], function () {
        Router::get('/{id:i}', 'ForumController@thread', 'forums.thread');
        Router::post('/{id:i}/mod', 'ForumController@threadModerate', 'forums.thread.mod');
        Router::post('/{id:i}/reply', 'ForumController@threadReply', 'forums.thread.reply');
    });

    // Forum
    Router::get('/', 'ForumController@index', 'forums.index');
    Router::get('/{id:i}', 'ForumController@forum', 'forums.forum');
    Router::group(['before' => 'loginCheck'], function () {
        Router::get('/{id:i}/mark', 'ForumController@markForumRead', 'forums.mark');
        Router::get('/{id:i}/new', 'ForumController@createThread', 'forums.new');
        Router::post('/{id:i}/new', 'ForumController@createThread', 'forums.new');
    });
});

// Members
Router::group(['prefix' => 'members', 'before' => 'loginCheck'], function () {
    Router::get('/', 'UserController@members', 'members.index');
    Router::get('/{rank:i}', 'UserController@members', 'members.rank');
});

// User
Router::get('/u/{id}', 'UserController@profile', 'user.profile');
Router::get('/u/{id}/header', 'FileController@header', 'user.header');

// Notifications
Router::group(['prefix' => 'notifications'], function () {
    Router::get('/', 'NotificationsController@notifications', 'notifications.get');
    Router::get('/{id}/mark', 'NotificationsController@mark', 'notifications.mark');
});

// Comments
Router::group(['prefix' => 'comments', 'before' => 'loginCheck'], function () {
    Router::post('/{category:c}/post/{reply:i}?', 'CommentsController@post', 'comments.post');
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
    });

    // General section
    Router::group(['prefix' => 'general'], function () {
        Router::get('/', function () {
            $route = Router::route('settings.general.home');
            return header("Location: {$route}");
        });

        Router::get('/home', 'Settings.GeneralController@home', 'settings.general.home');
        Router::get('/profile', 'Settings.GeneralController@profile', 'settings.general.profile');
        Router::get('/options', 'Settings.GeneralController@options', 'settings.general.options');
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
        Router::get('/background', 'Settings.AppearanceController@background', 'settings.appearance.background');
        Router::get('/header', 'Settings.AppearanceController@header', 'settings.appearance.header');
        Router::get('/userpage', 'Settings.AppearanceController@userpage', 'settings.appearance.userpage');
        Router::get('/signature', 'Settings.AppearanceController@signature', 'settings.appearance.signature');
    });

    // Account section
    Router::group(['prefix' => 'account'], function () {
        Router::get('/', function () {
            $route = Router::route('settings.account.email');
            return header("Location: {$route}");
        });

        Router::get('/email', 'Settings.AccountController@avatar', 'settings.account.email');
        Router::get('/username', 'Settings.AccountController@username', 'settings.account.username');
        Router::get('/title', 'Settings.AccountController@title', 'settings.account.title');
        Router::get('/password', 'Settings.AccountController@password', 'settings.account.password');
        Router::get('/ranks', 'Settings.AccountController@ranks', 'settings.account.ranks');
    });

    // Advanced section
    Router::group(['prefix' => 'advanced'], function () {
        Router::get('/', function () {
            $route = Router::route('settings.advanced.sessions');
            return header("Location: {$route}");
        });

        Router::get('/sessions', 'Settings.AdvancedController@sessions', 'settings.advanced.sessions');
        Router::get('/deactivate', 'Settings.AdvancedController@deactivate', 'settings.advanced.deactivate');
    });
});

// Management
/*
 * General
 * - Dashboard
 * - Info pages (possibly deprecate with wiki)
 * Configuration
 * - General
 * - Files
 * - User
 * - Mail
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
