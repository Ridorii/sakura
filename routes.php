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
Router::get('/news', 'MetaController@news', 'news.index');
Router::get('/news/{category}', 'MetaController@news', 'news.category');
Router::get('/news/{category}/{id}', 'MetaController@news', 'news.post');

// Forum
Router::get('/forum', 'ForumController@index', 'forums.index');
Router::get('/forum/{id}', 'ForumController@forum', 'forums.forum');

// Members
Router::get('/members', 'UserController@members', 'members.index');
Router::get('/members/{rank}', 'UserController@members', 'members.rank');

// User
Router::get('/u/{id}', 'UserController@profile', 'user.profile');
Router::get('/u/{id}/header', 'FileController@header', 'user.header');

// Files
Router::get('/a/{id}', 'FileController@avatar', 'file.avatar');
Router::get('/bg/{id}', 'FileController@background', 'file.background');

// Premium
Router::get('/support', 'PremiumController@index', 'premium.index');
Router::get('/support/tracker', 'PremiumController@tracker', 'premium.tracker');

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
