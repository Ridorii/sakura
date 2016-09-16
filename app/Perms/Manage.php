<?php
/**
 * Holds the site management permission flags.
 * @package Sakura
 */

namespace Sakura\Perms;

/**
 * All site management permission flags.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Manage
{
    /**
     * Can this user use the management panel?
     */
    const USE_MANAGE = 1;

    /**
     * Can this user toggle the restriction status of users?
     */
    const CAN_RESTRICT_USERS = 2;

    /**
     * Can this user alter other user's profile images?
     */
    const CHANGE_IMAGES = 4;
}
