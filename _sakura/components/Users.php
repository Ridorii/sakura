<?php
/*
 * User Management
 */

namespace Sakura;

class Users {

    // Check if a user is logged in
    public static function loggedIn() {

        // Just return false for now since we don't have a user system yet
        return false;

    }

    // Get user
    public static function getUser($id) {

        // Execute query
        $user = Database::fetch('users', false, ['id' => [$id, '=']]);

        // Return false if no user was found
        if(empty($user))
            return false;

        // If user was found return user data
        return $user;

    }

}
