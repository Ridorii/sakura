<?php
/**
 * Holds the code tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Code tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Code extends TagBase
{
    /**
     * Parses the bbcode.
     * @param string $text
     * @return string
     */
    public static function parse($text)
    {
        return preg_replace_callback(
            '/\[code(?:\=([a-z]+))?\](.*?)\[\/code\]/s',
            function ($matches) {
                $class = strlen($matches[1]) ? " class='lang-{$matches[1]}'" : '';
                // htmlencode bbcode characters here as well

                return "<pre><code{$class}>{$matches[2]}</code></pre>";
            },
            $text
        );
    }
}
