<?php
/**
 * Holds the YouTube tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * YouTube tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class YouTube extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[youtube\]([A-Za-z0-9\-\_]+)\[\/youtube\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<iframe width='560' height='315' src='https://www.youtube-nocookie.com/embed/$1'"
        . " frameborder='0' allowfullscreen></iframe>";
}
