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

    // Get user data by id
    public static function getUser($id) {

        // Execute query
        $user = Database::fetch('users', false, ['id' => [$id, '=']]);
print_r($user);
        // Return false if no user was found
        if(empty($user))
            return false;

        // If user was found return user data
        return $user;

    }

    // Get group data by id
    public static function getGroup($id) {

        // Execute query
        $group = Database::fetch('groups', false, ['id' => [$id, '=']]);
print_r($group);
        // Return false if no group was found
        if(empty($group))
            return false;

        // If group was found return group data
        return $group;

    }

    // Get all users
    public static function getAllUsers() {

        // Execute query
        $getUsers = Database::fetch('users', true);

        // Reorder shit
        foreach($getUsers as $user)
            $users[$user['id']] = $user;

        // and return an array with the users
        return $users;

    }

    // Get all groups
    public static function getAllGroups() {

        // Execute query
        $getGroups = Database::fetch('groups', true);

        // Reorder shit
        foreach($getGroups as $group)
            $groups[$group['id']] = $group;

        // and return an array with the users
        return $groups;

    }

}
