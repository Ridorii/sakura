<?php
/**
 * Holds the forum permission flags.
 *
 * @package Sakura
 */

namespace Sakura\Perms;

/**
 * All forum permission flags.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Forum
{
    /**
     * Can this user view/read this forum?
     */
    const VIEW = 1;

    /**
     * Can this user post/reply in this forum?
     */
    const REPLY = 2;

    /**
     * Can this user create topics in this forum?
     */
    const CREATE_THREADS = 4;

    /**
     * Can this user edit their own posts?
     */
    const EDIT_OWN = 8;

    /**
     * Can this user delete their own posts?
     */
    const DELETE_OWN = 16;

    /**
     * Can this user change topics to the sticky type?
     */
    const STICKY = 32;

    /**
     * Can this user change topics to the announcement type?
     */
    const ANNOUNCEMENT = 64;

    /**
     * Can this user edit any post in this forum?
     */
    const EDIT_ANY = 128;

    /**
     * Can this user delete any post in this forum?
     */
    const DELETE_ANY = 256;

    /**
     * Can this user toggle the locked status on topics in this forum?
     */
    const LOCK = 512;

    /**
     * Can this user move topics to other forums from/to this forum?
     */
    const MOVE = 1024;
}
