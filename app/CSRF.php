<?php
/**
 * Holds the CSRF token handler.
 * @package Sakura
 */

namespace Sakura;

/**
 * Used to generate and validate CSRF tokens.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class CSRF
{
    /**
     * The prefix to prevent collisions in the $_SESSION variable.
     */
    const ID_PREFIX = '_sakura_csrf_';

    /**
     * The size of the randomly generated string.
     */
    const RANDOM_SIZE = 16;

    /**
     * Create a new CSRF token.
     * @param mixed $id
     * @return string
     */
    public static function create($id)
    {
        // Generate a token
        $token = self::generate();

        // Make identifier
        $id = strtoupper(self::ID_PREFIX . $id);

        // Assign to session
        $_SESSION[$id] = $token;

        // Return the token
        return $token;
    }

    /**
     * Generate a CSRF token.
     * @return string
     */
    public static function generate()
    {
        return bin2hex(random_bytes(self::RANDOM_SIZE));
    }

    /**
     * Validate a CSRF token.
     * @param string $token
     * @param string $id
     * @return bool
     */
    public static function validate($token, $id)
    {
        // Set id
        $id = strtoupper(self::ID_PREFIX . $id);

        // Check if the token exists
        if (!array_key_exists($id, $_SESSION)) {
            return false;
        }

        return hash_equals($token, $_SESSION[$id]);
    }
}
