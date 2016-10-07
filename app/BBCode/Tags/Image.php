<?php
/**
 * Holds the image tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Image tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Image extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[img\](.*?)\[\/img\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<img class='bbcode__image' src='$1' alt='$1'>";
}
