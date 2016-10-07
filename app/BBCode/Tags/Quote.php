<?php
/**
 * Holds the quote tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Quote tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Quote extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[quote\](.*?)\[\/quote\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<blockquote class='bbcode__quote'>$1</blockquote>";
}
