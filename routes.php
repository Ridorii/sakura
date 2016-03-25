<?php
/*
 * Router paths
 */

// Define namespace
namespace Sakura;

// Meta pages
Router::get('/', 'MetaController@index', 'main.index');
Router::get('/faq', 'MetaController@faq', 'main.faq');
Router::get('/search', 'MetaController@search', 'main.search');
Router::get('/p/{id}', 'MetaController@infoPage', 'main.infopage');

// Auth
Router::get('/login', 'AuthController@loginGet', 'auth.login');
Router::post('/login', 'AuthController@loginPost', 'auth.login');
Router::get('/logout', 'AuthController@logout', 'auth.logout');
Router::get('/register', 'AuthController@registerGet', 'auth.register');
Router::post('/register', 'AuthController@registerPost', 'auth.register');
Router::get('/resetpassword', 'AuthController@resetPasswordGet', 'auth.resetpassword');
Router::post('/resetpassword', 'AuthController@resetPasswordPost', 'auth.resetpassword');
Router::get('/reactivate', 'AuthController@reactivateGet', 'auth.reactivate');
Router::post('/reactivate', 'AuthController@reactivatePost', 'auth.reactivate');
Router::get('/activate', 'AuthController@activate', 'auth.activate');

// News
Router::group(['prefix' => 'news'], function () {
    Router::get('/', 'MetaController@news', 'news.index');
    Router::get('/{category}', 'MetaController@news', 'news.category');
    Router::get('/{category}/{id}', 'MetaController@news', 'news.post');
});

// Forum
Router::group(['prefix' => 'forum'], function () {
    // Post
    Router::group(['prefix' => 'post'], function () {
        Router::get('/{id:i}', 'ForumController@post', 'forums.post');
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
    Router::get('/{id:i}/mark', 'ForumController@markForumRead', 'forums.mark');
    Router::get('/{id:i}/new', 'ForumController@createThread', 'forums.new');
    Router::post('/{id:i}/new', 'ForumController@createThread', 'forums.new');
});

// Members
Router::group(['prefix' => 'members'], function () {
    Router::get('/', 'UserController@members', 'members.index');
    Router::get('/{rank:i}', 'UserController@members', 'members.rank');
});

// User
Router::get('/u/{id}', 'UserController@profile', 'user.profile');
Router::get('/u/{id}/header', 'FileController@header', 'user.header');

// Files
Router::get('/a/{id}', 'FileController@avatar', 'file.avatar');
Router::get('/bg/{id}', 'FileController@background', 'file.background');

// Premium
Router::group(['prefix' => 'support'], function () {
    Router::get('/', 'PremiumController@index', 'premium.index');
    Router::get('/tracker', 'PremiumController@tracker', 'premium.tracker');
});

// Helpers
Router::group(['prefix' => 'helper'], function () {
    // BBcode
    Router::group(['prefix' => 'bbcode'], function () {
        Router::post('/parse', 'HelperController@bbcodeParse', 'helper.bbcode.parse');
    });
});

// Settings
/*
 * General
 * - Home (make this not worthless while you're at it)
 * - Edit Profile
 * - Site Options
 * Friends
 * - Listing
 * - Requests
 * Groups
 * - Listing
 * - Invites
 * Notifications (will probably deprecate this entire section at some point but not relevant yet)
 * - History
 * Appearance (possibly combine ava, bg and header down into one menu as well as userpage and signature maybe)
 * - Avatar
 * - Background
 * - Header
 * - Userpage
 * - Signature
 * Account (also down to one section maybe)
 * - E-mail
 * - Username
 * - Usertitle
 * - Password
 * - Ranks (except this one i guess)
 * Advanced
 * - Session manager
 * - Deactivate account
 */

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
