<?php
/**
 * Holds the italics tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Italics tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Italics extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[i\](.*?)\[\/i\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<i>$1</i>";
}
