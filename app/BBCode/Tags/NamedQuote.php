<?php
/**
 * Holds the named quote tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Named quote tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class NamedQuote extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[quote\=(.*?)\](.*)\[\/quote\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<blockquote><small>$1</small>$2</blockquote>";
}
