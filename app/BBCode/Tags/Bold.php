<?php
/**
 * Holds the bold tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Bold tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Bold extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[b\](.*?)\[\/b\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<b class='bbcode__bold'>$1</b>";
}
