<?php
/*
 * Sakura News Page
 */

// Declare Namespace
namespace Sakura;

// Use DOMDocument
use DOMDocument;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Create a new News object
$news = new News(isset($_GET['cat']) ? $_GET['cat'] : Configuration::getConfig('site_news_category'));

// News XML feed
if (isset($_GET['xml'])) {
    // Get the news posts
    $posts = $news->getPosts();

    // Meta data attributes
    $metaData = [

        'title' => ($_FEED_TITLE = Configuration::getConfig('sitename')) . ' News',
        'link' => ($_FEED_URL = 'http://' . Configuration::getConfig('url_main')),
        'description' => 'News about ' . $_FEED_TITLE,
        'language' => 'en-gb',
        'webMaster' => (new User(1))->data['email'] . ' (' . $_FEED_TITLE . ' Webmaster)',
        'pubDate' => ($_FEED_DATE = date('r', $posts[array_keys($posts)[0]]['date'])),
        'lastBuildDate' => $_FEED_DATE,

    ];

    // Item attributes
    $itemData = [

        'title' => ['text' => '{EVAL}', 'eval' => '$post["title"]'],
        'link' => ['text' => $_FEED_URL . '/news/{EVAL}', 'eval' => '$post["id"]'],
        'guid' => ['text' => $_FEED_URL . '/news/{EVAL}', 'eval' => '$post["id"]'],
        'pubDate' => ['text' => '{EVAL}', 'eval' => 'date("D, d M Y G:i:s O", $post["date"])'],
        'dc:publisher' => ['text' => '{EVAL}', 'eval' => '$post["poster"]->data["username"]'],
        'description' => ['cdata' => '{EVAL}', 'eval' => '$post["content_parsed"]'],

    ];

    // Create a new DOM document
    $feed = new DOMDocument('1.0', 'utf-8');

    // Create the RSS element
    $fRss = $feed->createElement('rss');

    // Set attributes
    $fRss->setAttribute('version', '2.0');
    $fRss->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
    $fRss->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1');

    // Create the channel element
    $fChannel = $feed->createElement('channel');

    // Build meta elements
    foreach ($metaData as $name => $value) {
        // Create the elements
        $mElem = $feed->createElement($name);
        $mElemText = $feed->createTextNode($value);

        // Append them
        $mElem->appendChild($mElemText);
        $fChannel->appendChild($mElem);

        // Unset the working vars
        unset($mElem);
        unset($mElemText);
    }

    // Add all the posts
    foreach ($posts as $post) {
        // Create item element
        $fPost = $feed->createElement('item');

        // Create post attributes
        foreach ($itemData as $tag => $valueData) {
            // Create the element
            $pElem = $feed->createElement($tag);

            // Create value
            eval('$value = ' . $valueData['eval'] . ';');
            $value = str_replace(
                '{EVAL}',
                $value,
                $valueData[(array_key_exists('cdata', $valueData) ? 'cdata' : 'text')]
            );

            // Create text node or cdata container
            $pElemText = (array_key_exists('cdata', $valueData)) ?
            $feed->createCDATASection($value) :
            $feed->createTextNode($value);

            // Append them
            $pElem->appendChild($pElemText);
            $fPost->appendChild($pElem);

            // Unset the working vars
            unset($pElem);
            unset($pElemText);
            unset($value);
        }

        // Append the item to the channel
        $fChannel->appendChild($fPost);
    }

    // Append the channel element to RSS
    $fRss->appendChild($fChannel);

    // Append the RSS element to the DOM
    $feed->appendChild($fRss);

    // Return the feed
    print $feed->saveXML();
    exit;
}

$renderData = array_merge($renderData, [

    'news' => $news,
    'postsPerPage' => Configuration::getConfig('news_posts_per_page'),
    'viewPost' => isset($_GET['id']),
    'postExists' => $news->postExists(isset($_GET['id']) ? $_GET['id'] : 0),
    'currentPage' => isset($_GET['page']) && ($_GET['page'] - 1) >= 0 ? $_GET['page'] - 1 : 0,

]);

// Print page contents
print Templates::render('main/news.tpl', $renderData);
