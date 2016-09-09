<?php
/**
 * Markdown.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Parsedown;
use Sakura\BBCode\TagBase;

/**
 * Markdown!
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Markdown extends TagBase
{
    /**
     * Contains the parser.
     * @var Parsedown
     */
    private static $parser = null;

    /**
     * Parses the bbcode.
     * @param string $text
     * @return string
     */
    public static function parse($text)
    {
        return preg_replace_callback(
            '/\[md\](.*?)\[\/md\]/s',
            function ($matches) {
                if (self::$parser === null) {
                    self::$parser = new Parsedown;
                }

                $parsed = self::$parser
                    ->setBreaksEnabled(false)
                    ->setMarkupEscaped(true)
                    ->text($matches[1]);

                return "<div class='markdown'>{$parsed}</div>";
            },
            $text
        );
    }
}
