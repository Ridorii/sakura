<?php
/**
 * Holds the authentication interface.
 * @package Sakura
 */

namespace Sakura\Chat;

/**
 * Interface for authentication methods.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
interface AuthInterface
{
    public function attempt();
}
