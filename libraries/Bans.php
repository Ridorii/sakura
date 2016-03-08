<?php
/**
 * Holds the ban manager.
 *
 * @package Sakura
 */

namespace Sakura;

/**
 * User banishment management.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Bans
{
    /**
     * Checks if a user is banned.
     *
     * @param int $uid The ID of the user that is being checked.
     *
     * @return array|bool Either false or an array containing information about the ban.
     */
    public static function checkBan($uid)
    {

        // Attempt to get a ban from this user
        $bans = DB::table('bans')
            ->where('user_id', $uid)
            ->get();

        // Reverse the array so new bans are listed first
        $bans = array_reverse($bans);

        // Go over each ban
        foreach ($bans as $ban) {
            // Check if it hasn't expired
            if ($ban->ban_end != 0 && $ban->ban_end < time()) {
                // If it has delete the entry and continue
                DB::table('bans')
                    ->where('ban_id', $ban->ban_id)
                    ->delete();
                continue;
            }

            // Return the ban if all checks were passed
            return [
                'user' => $ban->user_id,
                'issuer' => $ban->ban_moderator,
                'issued' => $ban->ban_begin,
                'expires' => $ban->ban_end,
                'reason' => $ban->ban_reason,
            ];
        }

        // Else just return false
        return false;
    }
}
