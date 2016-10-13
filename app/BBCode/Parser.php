<?php
/**
 * Holds the BBcode handler.
 * @package Sakura
 */

namespace Sakura\BBCode;

use Sakura\DB;
use Sakura\User;

/**
 * BBcode handler.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Parser
{
    /**
     * Holds the bbcode parsers.
     * @var array
     */
    public static $parsers = [
        // Basic markup
        Tags\Bold::class,
        Tags\Italics::class,
        Tags\Underline::class,
        Tags\Strike::class,
        Tags\Header::class,
        Tags\Image::class,
        Tags\Spoiler::class,

        // More advanced
        Tags\Colour::class,
        Tags\Align::class,
        Tags\Size::class,
        Tags\YouTube::class,

        // Links
        Tags\NamedLink::class,
        Tags\Link::class,

        // Quotes
        Tags\NamedQuote::class,
        Tags\Quote::class,

        // Advanced parsing
        Tags\Box::class,
        Tags\Code::class,
        Tags\ListTag::class,
        Tags\UserTag::class,
        Tags\Markdown::class,

        // Newline must always be last
        Tags\Newline::class,
    ];

    /**
     * Parse the emoticons.
     * @param string $text
     * @return string
     */
    public static function parseEmoticons($text, User $poster = null)
    {
        // Get emoticons from the database
        $emotes = DB::table('emoticons')
            ->get();

        // Parse all emoticons
        foreach ($emotes as $emote) {
            if ($poster === null) {
                // eventually check for hierarchies here
                continue;
            }

            $image = "<img src='{$emote->emote_path}' alt='{$emote->emote_string}' class='emoticon'>";
            $icon = preg_quote($emote->emote_string, '#');
            $text = preg_replace("#{$icon}#", $image, $text);
        }

        // Return the parsed text
        return $text;
    }

    /**
     * Convert the parsed text to HTML.
     * @param string $text
     * @return string
     */
    public static function toHTML($text, User $poster)
    {
        $text = self::parseEmoticons($text);

        foreach (self::$parsers as $parser) {
            $text = call_user_func_array([$parser, 'parse'], [$text, $poster]);
        }

        return $text;
    }
}
