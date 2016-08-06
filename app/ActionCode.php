<?php
/**
 * Holds the action code handling class.
 * @package Sakura
 */

namespace Sakura;

/**
 * Used to generate one-time keys for user automatic user actions e.g. account activation.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class ActionCode
{
    /**
     * Generate an Action Code.
     * @param string $action
     * @param int $user
     * @return string
     */
    public static function generate($action, $user = 0)
    {
        // Generate a code
        $code = uniqid();

        // Insert it
        DB::table('actioncodes')
            ->insert([
                'code_action' => $action,
                'user_id' => $user,
                'action_code' => $code,
            ]);

        // Return the code
        return $code;
    }

    /**
     * Validate an action code.
     * @param string $action
     * @param string $code
     * @param int $user
     * @param bool $invalidate
     * @return bool
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
     * @param string $code
     */
    public static function invalidate($code)
    {
        DB::table('actioncodes')
            ->where('action_code', $code)
            ->delete();
    }
}
