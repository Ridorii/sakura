<?php
/**
 * Holds the newline tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;

/**
 * Newline.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Newline extends TagBase
{
    /**
     * The pattern to match.
     * @var string
     */
    public static $pattern = "/\r\n|\r|\n/";

    /**
     * The string to replace it with.
     * @var string
     */
    public static $replace = "<br>";
}
