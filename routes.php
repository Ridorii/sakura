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
Router::get('/forum', 'Sakura\Controllers\Forum@index', 'forum.index');

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

Router::any('/faq.php', function () {
    header('Location: /faq');
});

Router::any('/search.php', function () {
    header('Location: /search');
});
