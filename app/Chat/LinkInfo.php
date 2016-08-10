<?php
/**
 * Holds the link info object.
 * @package Sakura
 */

namespace Sakura\Chat;

/**
 * Object to serve back to the chat.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class LinkInfo
{
    /**
     * Types for $Type.
     */
    const TYPES = [
        'PLAIN' => 0,
        'META' => 1,
        'VIDEO' => 2,
        'AUDIO' => 3,
        'IMAGE' => 4,
        'EMBED' => 5,
    ];

    /**
     * Modifiable url.
     * @var string
     */
    public $URL;

    /**
     * Original url.
     * @var string
     */
    public $OriginalURL;

    /**
     * Type (from const TYPES).
     * @var int
     */
    public $Type;

    /**
     * Full image or thumbnail, depends on Type.
     * @var string
     */
    public $Image;

    /**
     * Title/header text.
     * @var string
     */
    public $Title;

    /**
     * Description text.
     * @var string
     */
    public $Description;

    /**
     * The content type to assign if applicable.
     * @var string
     */
    public $ContentType;

    /**
     * The width of an image if applicable.
     * @var int
     */
    public $Width;

    /**
     * The height of an image if applicable.
     * @var int
     */
    public $Height;
}
