<?php
/**
 * Holds the align tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Align tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Align extends TagBase
{
    /**
     * Parses the bbcode.
     * @param string $text
     * @return string
     */
    public static function parse($text)
    {
        return preg_replace_callback(
            '/\[align\=(left|center|centre|right)\](.*?)\[\/align\]/s',
            function ($matches) {
                if ($matches[1] === 'centre') {
                    $matches[1] = 'center';
                }

                return "<div style='text-align: {$matches[1]}'>{$matches[2]}</div>";
            },
            $text
        );
    }
}
