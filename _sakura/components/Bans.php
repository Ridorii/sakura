<?php
/*
 * Ban management
 */

namespace Sakura;

class Bans {

    // Check if a user is banned
    public static function checkBan($id) {

        // Attempt to get a ban from this user
        $bans = Database::fetch('bans', true, ['uid' => [$id, '=']]);

        // Reverse the array so new bans are listed first
        $bans = array_reverse($bans);

        // Go over each ban
        foreach($bans as $ban) {

            // Check if it hasn't expired
            if($ban['ban_end'] != 0 && $ban['ban_end'] < time()) {

                // If it has delete the entry and continue
                Database::delete('bans', ['id' => [$ban['id'], '=']]);
                continue;

            }

            // Return the ban if all checks were passed
            return [

                'user'      => $ban['uid'],
                'issuer'    => $ban['mod_id'],
                'issued'    => $ban['ban_begin'],
                'expires'   => $ban['ban_end'],
                'reason'    => $ban['ban_reason']

            ];

        }

        // Else just return false
        return false;

    }

}