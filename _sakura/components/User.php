<?php
/*
 * Everything you'd ever need from a specific user
 */

namespace Sakura;

class User {

    // User data
    public $user   = [];
    private $ranks  = [];

    // Initialise the user
    function __contruct($id) {

        // Get the user database row
        $this->user = Database::fetch('users', false, ['id' => [$id, '=']]);

        // Decode the ranks json array
        $ranks = json_decode($this->user['ranks'], true);

        // Get the rows for all the ranks
        $this->ranks[] = Database::fetch('ranks', false, ['id' => [$id, '=']]);

    }

}
