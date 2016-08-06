<?php
/**
 * Holds the box tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

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
    public static function parse($text)
    {
        return preg_replace_callback(
            '/\[box(?:\=(.*?))?\](.*?)\[\/box\]/s',
            function ($matches) {
                $title = strlen($matches[1]) ? $matches[1] : 'Click to open';

                return "<div class='spoiler-box-container'>"
                    . "<div class='spoiler-box-title' onclick='alert(\"reimplement the toggle system\");'>{$title}</div>"
                    . "<div class='spoiler-box-content hidden'>{$matches[2]}</div>"
                    . "</div>";
            },
            $text
        );
    }
}
