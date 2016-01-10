<?php
/*
 * Action code handler
 */

namespace Sakura;

/**
 * Class ActionCode
 * @package Sakura
 */
class ActionCode
{
    // Generating an action code
    public static function generate($action, $user = 0)
    {
        // Generate a code
        $code = uniqid();

        // Insert it
        Database::insert('actioncodes', [
            'code_action' => $action,
            'user_id' => $user,
            'action_code' => $code,
        ]);

        // Return the code
        return $code;
    }

    // Checking if a code is still valid
    public static function validate($action, $code, $user = 0, $invalidate = true)
    {
        // Fetch the code from the db
        $get = Database::count('actioncodes', [
            'code_action' => [$action, '='],
            'action_code' => [$code, '='],
            'user_id' => [$user, '='],
        ]);

        // Invalidate the code if requested
        if ($invalidate) {
            self::invalidate($code);
        }

        // Return the result
        return $get[0] > 0;
    }

    // Make a code invalid
    public static function invalidate($code)
    {
        Database::delete('actioncodes', [
            'code_action' => [$code, '='],
        ]);
    }
}
