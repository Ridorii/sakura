<?php
/**
 * Holds the size tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;
use Sakura\User;

/**
 * Size tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Size extends TagBase
{
    /**
     * The maximum size a user can specify.
     * @var int
     */
    private static $maxSize = 200;

    /**
     * The minimum size a user can specify.
     * @var int
     */
    private static $minSize = 1;

    /**
     * Text aliases for the various sizes.
     * @var array
     */
    private static $aliases = [
        'tiny' => 50,
        'small' => 85,
        'normal' => 100,
        'large' => 150,
        'huge' => 200,
    ];

    /**
     * Parses the bbcode.
     * @param string $text
     * @return string
     */
    public static function parse($text, User $poster)
    {
        return preg_replace_callback(
            '/\[size\=([a-z0-9]+)\](.*?)\[\/size\]/s',
            function ($matches) {
                if (is_numeric($matches[1])) {
                    $size = intval($matches[1]);

                    if ($size < self::$minSize || $size > self::$maxSize) {
                        return $matches[0];
                    }
                } elseif (in_array($matches[1], self::$aliases)) {
                    $size = self::$aliases[$matches[1]];
                } else {
                    return $matches[0];
                }

                // we'll just use per cent for now, don't let this make it to production though
                return "<div style='font-size: {$size}%' class='bbcode__size'>{$matches[2]}</div>";
            },
            $text
        );
    }
}
