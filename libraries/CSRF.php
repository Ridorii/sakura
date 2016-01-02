<?php
/*
 * CSRF protection
 */

namespace Sakura;

use Sakura\Hashing;

/**
 * Class CSRF
 * @package Sakura
 */
class CSRF
{
    // Constants
    const ID_PREFIX = '_sakura_csrf_';
    const RANDOM_SIZE = 16;

    // Create a new CSRF token
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

    // Generate a CSRF token
    public static function generate()
    {
        return bin2hex(\mcrypt_create_iv(self::RANDOM_SIZE, MCRYPT_DEV_URANDOM));
    }

    // Validate a CSRF token
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
