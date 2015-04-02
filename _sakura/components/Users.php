<?php
/*
 * User Management
 */

namespace Sakura;

class Users {

    // Empty user template
    public static $emptyUser = [
        'id'                => 0,
        'username'          => 'Deleted User',
        'username_clean'    => 'deleted user',
        'password_hash'     => '',
        'password_salt'     => '',
        'password_algo'     => 'sha256',
        'password_iter'     => 1000,
        'password_chan'     => 0,
        'password_new'      => '',
        'email'             => 'deleted@flashii.net',
        'group_main'        => 0,
        'groups'            => '[0]',
        'name_colour'       => '',
        'register_ip'       => '127.0.0.1',
        'last_ip'           => '127.0.0.1',
        'usertitle'         => 'Non-existent user account',
        'profile_md'        => '',
        'avatar_url'        => '',
        'background_url'    => '',
        'regdate'           => 0,
        'lastdate'          => 0,
        'lastunamechange'   => 0,
        'birthday'          => '',
        'profile_data'      => '[]'
    ];

    // Empty group template
    public static $emptyGroup = [
        'id'            => 0,
        'groupname'     => 'Non-existent group',
        'multi'         => 0,
        'colour'        => '',
        'description'   => 'A hardcoded dummy group for fallback.'
    ];

    // Check if a user is logged in
    public static function loggedIn() {

        // Just return false for now since we don't have a user system yet
        return false;

    }

    // Get user data by id
    public static function getUser($id) {

        // Execute query
        $user = Database::fetch('users', false, ['id' => [$id, '=']]);

        // Return false if no user was found
        if(empty($user))
            return self::$emptyUser;

        // If user was found return user data
        return $user;

    }

    // Get group data by id
    public static function getGroup($id) {

        // Execute query
        $group = Database::fetch('groups', false, ['id' => [$id, '=']]);

        // Return false if no group was found
        if(empty($group))
            return self::$emptyGroup;

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
