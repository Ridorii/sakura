<?php
/**
 * Holds the link tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Zelda tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Link extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[url\](.*?)\[\/url\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<a href='$1'>$1</a>";
}
