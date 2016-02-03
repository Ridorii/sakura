<?php
/**
 * Holds the CSRF token handler.
 * 
 * @package Sakura
 */

namespace Sakura;

use Sakura\Hashing;

/**
 * Used to generate and validate CSRF tokens.
 * 
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
     * 
     * @param mixed $id The ID for this token.
     * 
     * @return string The token.
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
     * 
     * @return string Cryptographically secure random string.
     */
    public static function generate()
    {
        return bin2hex(\mcrypt_create_iv(self::RANDOM_SIZE, MCRYPT_DEV_URANDOM));
    }

    /**
     * Validate a CSRF token.
     * 
     * @param mixed $token The token.
     * @param mixed $id The ID.
     * 
     * @return bool Indicator if it was right or not.
     */
    public static function validate($token, $id)
    {
        // Set id
        $id = strtoupper(self::ID_PREFIX . $id);

        // Check if the token exists
        if (!array_key_exists($id, $_SESSION)) {
            return false;
        }

        // Use the slowEquals function from the hashing lib to validate
        return Hashing::slowEquals($token, $_SESSION[$id]);
    }
}
