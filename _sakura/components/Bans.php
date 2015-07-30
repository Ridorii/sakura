<?php
/*
 * Ban management
 */

namespace Sakura;

class Bans {

    // Check if a user is banned
    public static function checkBan($id) {

        if($id == 1) {
            return [
                'user' => 1,
                'issuer' => 2,
                'issued' => 246,
                'expires' => time(),
                'reason' => 'meow'
            ];
        }

    }

}
