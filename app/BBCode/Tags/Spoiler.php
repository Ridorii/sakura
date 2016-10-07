<?php
/**
 * Holds the spoiler tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Spoiler tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Spoiler extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[spoiler\](.*?)\[\/spoiler\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<span class='bbcode__spoiler'>$1</span>";
}
