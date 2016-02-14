<?php
/*
 * Router paths
 */

// Define namespace
namespace Sakura;

// Meta pages
Router::get('/', 'Meta@index', 'main.index');
Router::get('/faq', 'Meta@faq', 'main.faq');
Router::get('/search', 'Meta@search', 'main.search');
Router::get('/p/{id}', 'Meta@infoPage', 'main.infopage');

// Auth
Router::get('/login', 'Auth@login', 'auth.login');

// News
Router::get('/news', 'Meta@news', 'news.index');
Router::get('/news/{category}', 'Meta@news', 'news.category');
Router::get('/news/{category}/{id}', 'Meta@news', 'news.post');

// Forum
Router::get('/forum', 'Forums@index', 'forums.index');
Router::get('/forum/{id}', 'Forums@forum', 'forums.forum');

// Members
Router::get('/members', 'User@members', 'members.all');
Router::get('/members/{rank}', 'User@members', 'members.rank');

// User
Router::get('/u/{id}', 'User@profile', 'user.profile');

// Premium
Router::get('/support', 'Premium@index', 'premium.index');
Router::get('/support/tracker', 'Premium@tracker', 'premium.tracker');

// Redirections
Router::any('/index.php', function () {
    // Info pages
    if (isset($_REQUEST['p'])) {
        header('Location: /p/' . $_REQUEST['p']);
        return;
    }

    // Forum index
    if (isset($_REQUEST['forum']) && $_REQUEST['forum']) {
        header('Location: /forum');
        return;
    }

    // Site index
    header('Location: /');
});

Router::any('/news.php', function () {
    // Category + post
    if (isset($_REQUEST['cat']) && isset($_REQUEST['id'])) {
        header('Location: /news/' . $_REQUEST['cat'] . '/'. $_REQUEST['id']);
        return;
    }

    // Category
    if (isset($_REQUEST['cat'])) {
        header('Location: /news/' . $_REQUEST['cat']);
        return;
    }

    // Post in the main category
    if (isset($_REQUEST['id'])) {
        header('Location: /news/' . $_REQUEST['id']);
        return;
    }

    // All posts in main category
    header('Location: /news');
});

Router::any('/profile.php', function () {
    // Redirect to the profile
    if (isset($_REQUEST['u'])) {
        header('Location: /u/' . $_REQUEST['u']);
        return;
    }

    // Redirect to index
    header('Location: /');
});

Router::any('/members.php', function () {
    // Append sort
    $append = isset($_REQUEST['sort']) ? '?sort=' . $_REQUEST['sort'] : '';

    // Redirect to the profile
    if (isset($_REQUEST['rank'])) {
        header('Location: /members/' . $_REQUEST['rank'] . $append);
        return;
    }

    // Redirect to index
    header('Location: /members/' . $append);
});

Router::any('/viewforum.php', function () {
    // Redirect to the profile
    if (isset($_REQUEST['f'])) {
        $req = [];
        foreach ($_REQUEST as $k => $v) {
            if ($k == 'f') {
                continue;
            }

            $req[] = $k . '=' . $v;
        }

        header('Location: /forum/' . $_REQUEST['f'] . ($req ? '?' . implode('&', $req) : ''));
        return;
    }

    // Redirect to index
    header('Location: /forum/');
});

Router::any('/support.php', function () {
    if (isset($_GET['tracker'])) {
        header('Location: /support/tracker');
        return;
    }

    header('Location: /support');
});

Router::any('/faq.php', function () {
    header('Location: /faq');
});

Router::any('/search.php', function () {
    header('Location: /search');
});
