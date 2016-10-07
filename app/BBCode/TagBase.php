<?php
/**
 * Holds the tag base.
 * @package Sakura
 */

namespace Sakura\BBCode;

use Sakura\User;

/**
 * Interface for tags.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "";

    /**
     * Parses the bbcode.
     * @param string $text
     * @return string
     */
    public static function parse($text, User $poster)
    {
        return preg_replace(static::$pattern, static::$replace, $text);
    }
}
