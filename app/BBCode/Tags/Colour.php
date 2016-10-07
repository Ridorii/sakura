<?php
/**
 * Holds the colour tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Colour tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Colour extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[colou?r\=(#[A-f0-9]{6}|#[A-f0-9]{3})\](.*?)\[\/colou?r\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<span style='color: $1' class='bbcode__colour'>$2</span>";
}
