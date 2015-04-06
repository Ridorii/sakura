<?php
/*
 * Sakura News Page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Add page specific things
$renderData['newsPosts'] = Main::getNewsPosts((isset($_GET['id']) && !isset($_GET['xml']) && is_numeric($_GET['id'])) ? $_GET['id'] : null, (isset($_GET['id']) && !isset($_GET['xml']) && is_numeric($_GET['id'])));
$renderData['page'] = [
    'articleCount'  => ($postCount = count($renderData['newsPosts'])),
    'title'         => (isset($_GET['id']) ? ($postCount ? $renderData['newsPosts'][0]['title'] : 'Post does not exist!') : 'Flashii News'),
];

// News XML, don't really care so yeah
if(isset($_GET['xml'])) {

    print '<?xml version="1.0" encoding="UTF-8"?>';
    print '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">';

    print '<channel>';

    print '<title>Flashii News</title>';
    print '<link>http://flashii.net/</link>';
    print '<description>News on Flashii.net.</description>';
    print '<language>en-gb</language>';
    print '<language><webMaster>admin@flashii.net (Flashii Administrator)</webMaster></language>';
    print '<pubDate>'. date('D, d M Y G:i:s O') .'</pubDate>';
    print '<lastBuildDate>'. date('D, d M Y G:i:s O') .'</lastBuildDate>';

    foreach($renderData['newsPosts'] as $newsPost) {

        print '<item>';

        print '<title>'. $newsPost['title'] .'</title>';
        print '<link>http://flashii.net/news/'. $newsPost['id'] .'</link>';
        print '<guid>http://flashii.net/news/'. $newsPost['id'] .'</guid>';
        print '<pubDate>'. date('D, d M Y G:i:s O', $newsPost['date']) .'</pubDate>';
        print '<dc:publisher>'. $newsPost['udata']['username'] .'</dc:publisher>';
        print '<description><![CDATA['. $newsPost['parsed'] .']]></description>';

        print '</item>';

    }

    print '</channel>';

    print '</rss>';
    exit;

}

// Print page contents
print Main::tplRender('main/news.tpl', $renderData);
