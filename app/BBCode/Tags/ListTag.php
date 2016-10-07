<?php
/**
 * Holds the list tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;
use Sakura\User;

/**
 * List tag. Name is suffixed with Tag since "list" is a language construct.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class ListTag extends TagBase
{
    /**
     * Parses the bbcode.
     * @param string $text
     * @return string
     */
    public static function parse($text, User $poster)
    {
        return preg_replace_callback(
            '/\[list(?:\=(1|A|a|I|i))?\](.*?)\[\/list\]/s',
            function ($matches) {
                $content = preg_replace('/\[\*\](.*)/', '<li class="bbcode__list-entry">$1</li>', $matches[2]);

                if ($matches[1] !== '') {
                    return "<ol type='{$matches[1]}' class='bbcode__list bbcode__list--ordered'>{$content}</ol>";
                }

                return "<ul class='bbcode__list bbcode__list--unordered'>{$content}</ul>";
            },
            $text
        );
    }
}
