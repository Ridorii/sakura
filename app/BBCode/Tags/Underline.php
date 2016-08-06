<?php
/**
 * Holds the underline tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Underline tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Underline extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[u\](.*?)\[\/u\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<u>$1</u>";
}
