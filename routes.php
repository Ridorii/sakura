<?php
/*
 * Router paths
 */

// Define namespace
namespace Sakura;

// Meta pages
Router::get('/', 'Sakura\Controllers\Meta@index', 'main.index');
Router::get('/faq', 'Sakura\Controllers\Meta@faq', 'main.faq');
Router::get('/search', 'Sakura\Controllers\Meta@search', 'main.search');
Router::get('/p/{id}', 'Sakura\Controllers\Meta@infoPage', 'main.infopage');

// News
Router::get('/news', 'Sakura\Controllers\Meta@news', 'news.index');
Router::get('/news/{category}', 'Sakura\Controllers\Meta@news', 'news.category');
Router::get('/news/{category}/{id}', 'Sakura\Controllers\Meta@news', 'news.post');

// Forum
Router::get('/forum', 'Sakura\Controllers\Forums@index', 'forums.index');
Router::get('/forum/{id}', 'Sakura\Controllers\Forums@forum', 'forums.forum');

// Members
Router::get('/members', 'Sakura\Controllers\User@members', 'members.all');
Router::get('/members/{rank}', 'Sakura\Controllers\User@members', 'members.rank');

// User
Router::get('/u/{id}', 'Sakura\Controllers\User@profile', 'user.profile');

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

Router::any('/faq.php', function () {
    header('Location: /faq');
});

Router::any('/search.php', function () {
    header('Location: /search');
});
