<?php
/**
 * Holds the named link tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Named link tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class NamedLink extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[url\=(.*?)\](.*?)\[\/url\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<a href='$1'>$2</a>";
}
