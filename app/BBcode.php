<?php
/**
 * Holds the BBcode handler.
 *
 * @package Sakura
 */

namespace Sakura;

/**
 * BBcode handler.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class BBcode
{
    /**
     * Holds the bbcode parsers
     *
     * @var array
     */
    public static $parsers = [
        'bold' => [
            'pattern' => '/\[b\](.*?)\[\/b\]/s',
            'replace' => '<b>$1</b>',
            'content' => '$1',
        ],
        'italic' => [
            'pattern' => '/\[i\](.*?)\[\/i\]/s',
            'replace' => '<i>$1</i>',
            'content' => '$1',
        ],
        'underline' => [
            'pattern' => '/\[u\](.*?)\[\/u\]/s',
            'replace' => '<u>$1</u>',
            'content' => '$1',
        ],
        'linethrough' => [
            'pattern' => '/\[s\](.*?)\[\/s\]/s',
            'replace' => '<del>$1</del>',
            'content' => '$1',
        ],
        'header' => [
            'pattern' => '/\[header\](.*?)\[\/header\]/s',
            'replace' => '<h1>$1</h1>',
            'content' => '$1',
        ],
        'size' => [
            'pattern' => '/\[size\=([1-7])\](.*?)\[\/size\]/s',
            'replace' => '<font size="$1">$2</font>',
            'content' => '$2',
        ],
        'color' => [
            'pattern' => '/\[color\=(#[A-f0-9]{6}|#[A-f0-9]{3})\](.*?)\[\/color\]/s',
            'replace' => '<span style="color: $1">$2</span>',
            'content' => '$2',
        ],
        'center' => [
            'pattern' => '/\[center\](.*?)\[\/center\]/s',
            'replace' => '<div style="text-align: center;">$1</div>',
            'content' => '$1',
        ],
        'left' => [
            'pattern' => '/\[left\](.*?)\[\/left\]/s',
            'replace' => '<div style="text-align: left;">$1</div>',
            'content' => '$1',
        ],
        'right' => [
            'pattern' => '/\[right\](.*?)\[\/right\]/s',
            'replace' => '<div style="text-align: right;">$1</div>',
            'content' => '$1',
        ],
        'align' => [
            'pattern' => '/\[align\=(left|center|right)\](.*?)\[\/align\]/s',
            'replace' => '<div style="text-align: $1;">$2</div>',
            'content' => '$2',
        ],
        'quote' => [
            'pattern' => '/\[quote\](.*?)\[\/quote\]/s',
            'replace' => '<blockquote>$1</blockquote>',
            'content' => '$1',
        ],
        'namedquote' => [
            'pattern' => '/\[quote\=(.*?)\](.*)\[\/quote\]/s',
            'replace' => '<blockquote><small>$1</small>$2</blockquote>',
            'content' => '$2',
        ],
        'link' => [
            'pattern' => '/\[url\](.*?)\[\/url\]/s',
            'replace' => '<a href="$1">$1</a>',
            'content' => '$1',
        ],
        'namedlink' => [
            'pattern' => '/\[url\=(.*?)\](.*?)\[\/url\]/s',
            'replace' => '<a href="$1">$2</a>',
            'content' => '$2',
        ],
        'image' => [
            'pattern' => '/\[img\](.*?)\[\/img\]/s',
            'replace' => '<img src="$1" alt="$1">',
            'content' => '$1',
        ],
        'orderedlistnumerical' => [
            'pattern' => '/\[list=1\](.*?)\[\/list\]/s',
            'replace' => '<ol>$1</ol>',
            'content' => '$1',
        ],
        'orderedlistalpha' => [
            'pattern' => '/\[list=a\](.*?)\[\/list\]/s',
            'replace' => '<ol type="a">$1</ol>',
            'content' => '$1',
        ],
        'unorderedlist' => [
            'pattern' => '/\[list\](.*?)\[\/list\]/s',
            'replace' => '<ul>$1</ul>',
            'content' => '$1',
        ],
        'listitem' => [
            'pattern' => '/\[\*\](.*)/',
            'replace' => '<li>$1</li>',
            'content' => '$1',
        ],
        'code' => [
            'pattern' => '/\[code\](.*?)\[\/code\]/s',
            'replace' => '<pre><code>$1</code></pre>',
            'content' => '$1',
        ],
        'youtube' => [
            'pattern' => '/\[youtube\](.*?)\[\/youtube\]/s',
            'replace' => '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
            'content' => '$1',
        ],
        'linebreak' => [
            'pattern' => '/\r\n|\r|\n/',
            'replace' => '<br>',
            'content' => '',
        ],
    ];

    /**
     * Parse the emoticons.
     *
     * @param string $text
     * @return string
     */
    public static function parseEmoticons($text)
    {
        // Get emoticons from the database
        $emotes = DB::table('emoticons')
            ->get();

        // Parse all emoticons
        foreach ($emotes as $emote) {
            $image = "<img src='{$emote->emote_path}' alt='{$emote->emote_string}' class='emoticon'>";
            $icon = preg_quote($emote->emote_string, '#');
            $text = preg_replace("#{$icon}#", $image, $text);
        }

        // Return the parsed text
        return $text;
    }

    /**
     * Convert the parsed text to HTML.
     *
     * @param string $text
     * @return string
     */
    public static function toHTML($text)
    {
        $text = self::parseEmoticons($text);

        foreach (self::$parsers as $parser) {
            $text = preg_replace($parser['pattern'], $parser['replace'], $text);
        }

        return $text;
    }
}
