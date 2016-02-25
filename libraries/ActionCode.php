<?php
/**
 * Holds the action code handling class.
 * 
 * @package Sakura
 */

namespace Sakura;

/**
 * Used to generate one-time keys for user automatic user actions e.g. account activation.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class ActionCode
{
    /**
     * Generate an Action Code.
     * 
     * @param string $action The identifier of the action.
     * @param int $user The user this action code is intended for (leave 0 for universal).
     * 
     * @return string The action code given to the user.
     */
    public static function generate($action, $user = 0)
    {
        // Generate a code
        $code = uniqid();

        // Insert it
        DB::table('actioncodes')
            ->insert(
                ['action' => $action, 'id' => $user, 'code' => $code]
            );

        // Return the code
        return $code;
    }

    /**
     * Validate an action code.
     * 
     * @param string $action The action identifier.
     * @param string $code The action code.
     * @param int $user The id of the user validating this code.
     * @param bool $invalidate Set if the code should be invalidated once validated.
     * 
     * @return bool Boolean indicating success.
     */
    public static function validate($action, $code, $user = 0, $invalidate = true)
    {
        // Fetch the code from the db
        $get = DB::table('actioncodes')
            ->where('code_action', $action)
            ->where('action_code', $code)
            ->where('user_id', $user)
            ->count();

        // Invalidate the code if requested
        if ($get && $invalidate) {
            self::invalidate($code);
        }

        // Return the result
        return $get > 0;
    }

    /**
     * Make a code invalid.
     * 
     * @param string $code The code that is being invalidated.
     */
    public static function invalidate($code)
    {
        DB::table('actioncodes')
            ->where('code_action', $code)
            ->delete();
    }
}
