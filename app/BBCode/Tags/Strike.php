<?php
/**
 * Holds the strike tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Strike tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Strike extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\[s\](.*?)\[\/s\]/s";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<del>$1</del>";
}
