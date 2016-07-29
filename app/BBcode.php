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
     * BBcodes, also for backwards compatibility.
     *
     * @var array
     */
    protected static $bbcodes = [];

    /**
     * Initialiser.
     */
    public static function init()
    {
    }

    /**
     * Parse the emoticons.
     *
     * @param string $text String to parse emoticons from.
     *
     * @return string Parsed text.
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
            $text = preg_replace("#$icon#", $image, $text);
        }

        // Return the parsed text
        return $text;
    }

    /**
     * Set the text to parse.
     *
     * @param string $text The text that should be parsed.
     */
    public static function text($text)
    {
        return $text;
    }

    /**
     * Convert the parsed text to HTML.
     *
     * @param string $text The text that should be parsed.
     *
     * @return string The parsed HTML.
     */
    public static function toHTML($text = null)
    {
        // // Check if text isn't null
        // if ($text !== null) {
        //     self::text($text);
        // }

        // $parsed = nl2br(self::$bbcode->getAsHtml());

        // $parsed = self::fixCodeTags($parsed);
        // $parsed = self::parseEmoticons($parsed);

        // return $parsed;

        return $text;
    }

    /**
     * Convert the parsed text to BBCode.
     *
     * @param string $text The text that should be parsed.
     *
     * @return string The converted bbcode.
     */
    public static function toEditor($text = null)
    {
        // // Check if text isn't null
        // if ($text !== null) {
        //     self::text($text);
        // }

        // return self::$bbcode->getAsBBCode();

        return $text;
    }

    /**
     * Convert the parsed text to plain.
     *
     * @param string $text The text that should be parsed.
     *
     * @return string The converted plaintext.
     */
    public static function toPlain($text = null)
    {
        // // Check if text isn't null
        // if ($text !== null) {
        //     self::text($text);
        // }

        // return self::$bbcode->getAsText();
        return $text;
    }

    /**
     * Clean up the contents of <code> tags.
     * See if this can be deprecated with a custom implementation!
     *
     * @param string $text Dirty
     *
     * @return string Clean
     */
    public static function fixCodeTags($text)
    {
        $parts = explode('<code>', $text);
        $newStr = '';

        if (count($parts) > 1) {
            foreach ($parts as $p) {
                $parts2 = explode('</code>', $p);
                if (count($parts2) > 1) {
                    $code = str_replace('<br />', '', $parts2[0]);
                    $code = str_replace('<br/>', '', $code);
                    $code = str_replace('<br>', '', $code);
                    $code = str_replace('<', '&lt;', $code);
                    $newStr .= '<code>' . $code . '</code>';
                    $newStr .= $parts2[1];
                } else {
                    $newStr .= $p;
                }
            }
        } else {
            $newStr = $text;
        }

        return $newStr;
    }
}
