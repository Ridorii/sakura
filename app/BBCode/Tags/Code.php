<?php
/**
 * Holds the code tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;
use Sakura\User;

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
    public static function parse($text, User $poster)
    {
        return preg_replace_callback(
            '/\[code(?:\=([a-z]+))?\](.*?)\[\/code\]/s',
            function ($matches) {
                $class = strlen($matches[1]) ? "lang-{$matches[1]}" : '';
                // htmlencode bbcode characters here as well

                return "<pre class='bbcode__code'><code class='bbcode__code-container {$class}'>{$matches[2]}</code></pre>";
            },
            $text
        );
    }
}
