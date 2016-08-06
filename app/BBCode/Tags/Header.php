<?php
/**
 * Holds the header tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Header tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Header extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[header\](.*?)\[\/header\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<h1>$1</h1>";
}
