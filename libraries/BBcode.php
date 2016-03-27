<?php
/**
 * Holds the BBcode handler.
 *
 * @package Sakura
 */

namespace Sakura;

use JBBCode\CodeDefinitionBuilder;
use JBBCode\DefaultCodeDefinitionSet;
use JBBCode\Parser;

/**
 * Sakura wrapper for JBBCode.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class BBcode
{
    /**
     * The container for JBBCode.
     *
     * @var Parser
     */
    private static $bbcode = null;

    /**
     * Initialiser.
     */
    public static function init()
    {
        // Create new parser class
        self::$bbcode = new Parser();

        // Add the standard definitions
        self::loadStandardCodes();
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
            $image = "<img src='{$emote->emote_path}' alt='{$emote->emote_string}' class='emoticon' />";
            $icon = preg_quote($emote->emote_string, '#');
            $text = preg_replace("#$icon#", $image, $text);
        }

        // Return the parsed text
        return $text;
    }

    /**
     * Adds the standard BBcode.
     */
    public static function loadStandardCodes()
    {
        // Add the standard definitions
        self::$bbcode->addCodeDefinitionSet(new DefaultCodeDefinitionSet());

        $simpleCodes = [
            ['header', '<h1>{param}</h1>'],
            ['s', '<del>{param}</del>'],
            ['spoiler', '<span class="spoiler">{param}</span>'],
            ['box', '<div class="spoiler-box-container">
            <div class="spoiler-box-title" onclick="Sakura.toggleClass(this.parentNode.children[1], \'hidden\');">'
                . 'Click to open</div><div class="spoiler-box-content hidden">{param}</div></div>'],
            ['box', '<div class="spoiler-box-container"><div class="spoiler-box-title"'
                . ' onclick="Sakura.toggleClass(this.parentNode.children[1], \'hidden\');">{option}</div>'
                . '<div class="spoiler-box-content hidden">{param}</div></div>'],
            ['quote', '<blockquote><div class="quotee">Quote</div><div class="quote">{param}</div></blockquote>'],
        ];

        foreach ($simpleCodes as $code) {
            $builder = new CodeDefinitionBuilder($code[0], $code[1]);

            if (strstr($code[1], '{option}')) {
                $builder->setUseOption(true);
            }

            self::$bbcode->addCodeDefinition($builder->build());
        }

        // Add special definitions (PHP files MUST have the same name as the definition class
        foreach (glob(ROOT . 'libraries/BBcodeDefinitions/*.php') as $ext) {
            // Clean the file path
            $ext = str_replace(ROOT . 'libraries/', '', $ext);
            $ext = str_replace('.php', '', $ext);
            $ext = str_replace('/', '\\', $ext);

            // Build the classname
            $className = __NAMESPACE__ . '\\' . $ext;

            // Add the BBcode definition
            self::$bbcode->addCodeDefinition(new $className);
        }
    }

    /**
     * Set the text to parse.
     *
     * @param string $text The text that should be parsed.
     */
    public static function text($text)
    {
        // Check if $bbcode is still null
        if (!self::$bbcode) {
            self::init();
        }

        self::$bbcode->parse($text);
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
        // Check if text isn't null
        if ($text !== null) {
            self::text($text);
        }

        $parsed = nl2br(self::$bbcode->getAsHtml());

        $parsed = self::fixCodeTags($parsed);
        $parsed = self::parseEmoticons($parsed);

        return $parsed;
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
        // Check if text isn't null
        if ($text !== null) {
            self::text($text);
        }

        return self::$bbcode->getAsBBCode();
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
        // Check if text isn't null
        if ($text !== null) {
            self::text($text);
        }

        return self::$bbcode->getAsText();
    }

    /**
     * Clean up the contents of <code> tags.
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
