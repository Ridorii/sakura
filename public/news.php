<?php
/*
 * Sakura News Page
 */

// Declare Namespace
namespace Sakura;

// Use DOMDocument
use DOMDocument;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Create a new News object
$news = new News(isset($_GET['cat']) ? $_GET['cat'] : Config::get('site_news_category'));

// News XML feed
if (isset($_GET['xml'])) {
    // Get the news posts
    $posts = $news->posts;

    // Meta data attributes
    $metaData = [
        'title' => ($_FEED_TITLE = Config::get('sitename')) . ' News',
        'link' => ($_FEED_URL = 'http://' . Config::get('url_main')),
        'description' => 'News about ' . $_FEED_TITLE,
        'language' => 'en-gb',
        'webMaster' => Config::get('admin_email') . ' (' . $_FEED_TITLE . ' Webmaster)',
        'pubDate' => ($_FEED_DATE = date('r', $posts[array_keys($posts)[0]]['news_timestamp'])),
        'lastBuildDate' => $_FEED_DATE,
    ];

    // Item attributes
    $itemData = [
        'title' => ['text' => '0', 'eval' => '$post["news_title"]'],
        'link' => ['text' => $_FEED_URL . (new Urls())->format('SITE_NEWS_POST', ['0']), 'eval' => '$post["news_id"]'],
        'guid' => ['text' => $_FEED_URL . (new Urls())->format('SITE_NEWS_POST', ['0']), 'eval' => '$post["news_id"]'],
        'pubDate' => ['text' => '{EVAL}', 'eval' => 'date("D, d M Y G:i:s O", $post["news_timestamp"])'],
        'dc:publisher' => ['text' => '0', 'eval' => '$post["news_poster"]->username()'],
        'description' => ['cdata' => '0', 'eval' => '$post["news_content_parsed"]'],
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
                '0',
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
    'postsPerPage' => Config::get('news_posts_per_page'),
    'viewPost' => isset($_GET['id']),
    'postExists' => $news->postExists(isset($_GET['id']) ? $_GET['id'] : 0),
]);

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('main/news');
