<?php
/**
 * Holds the box tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;
use Sakura\User;

/**
 * Box tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Box extends TagBase
{
    /**
     * Parses the bbcode.
     * @param string $text
     * @return string
     */
    public static function parse($text, User $poster)
    {
        return preg_replace_callback(
            '/\[box(?:\=(.*?))?\](.*?)\[\/box\]/s',
            function ($matches) {
                $title = strlen($matches[1]) ? $matches[1] : 'Click to open';

                return "<div class='bbcode__box'>"
                    . "<div class='bbcode__box-title' onclick='alert(\"implement the toggle system\");'>{$title}</div>"
                    . "<div class='bbcode__box-content bbcode__box-content--hidden'>{$matches[2]}</div>"
                    . "</div>";
            },
            $text
        );
    }
}
