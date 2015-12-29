<?php
/*
 * Permission Handler
 */

namespace Sakura;

use Sakura\Perms\Site;

/**
 * Class Permissions
 * @package Sakura
 */
class Permissions
{
    // Fallback permission data
    private static $fallback = [
        'rank_id' => 0,
        'user_id' => 0,
        'permissions_site' => 1,
        'permissions_manage' => 0,
        'permissions_inherit' => 11,
    ];

    // Global permissions table
    protected static $permissions = [
        // Site permissions
        'SITE' => [
            'DEACTIVATED' => Site::DEACTIVATED, // Is a user deactivated
            'RESTRICTED' => Site::RESTRICTED, // Is a user restricted
            'ALTER_PROFILE' => Site::ALTER_PROFILE, // Can alter their profile data
            'CHANGE_AVATAR' => Site::CHANGE_AVATAR, // Can change their avatar
            'CREATE_BACKGROUND' => Site::CREATE_BACKGROUND, // Can create a background (different from changing)
            'CHANGE_BACKGROUND' => Site::CHANGE_BACKGROUND, // Can change their background
            'VIEW_MEMBERLIST' => Site::VIEW_MEMBERLIST, // Can view the memberlist
            'CREATE_USERPAGE' => Site::CREATE_USERPAGE, // Can create a userpage (different from changing)
            'CHANGE_USERPAGE' => Site::CHANGE_USERPAGE, // Can change their userpage
            'USE_MESSAGES' => Site::USE_MESSAGES, // Can use the Private Messaging system
            'SEND_MESSAGES' => Site::SEND_MESSAGES, // Can send Private Messages to other users
            'CHANGE_EMAIL' => Site::CHANGE_EMAIL, // Can change their account e-mail address
            'CHANGE_USERNAME' => Site::CHANGE_USERNAME, // Can change their username
            'CHANGE_USERTITLE' => Site::CHANGE_USERTITLE, // Can change their usertitle
            'CHANGE_PASSWORD' => Site::CHANGE_PASSWORD, // Can change their password
            'ALTER_RANKS' => Site::ALTER_RANKS, // Can change their ranks
            'MANAGE_SESSIONS' => Site::MANAGE_SESSIONS, // Can manage their sessions
            'CHANGE_SIGNATURE' => Site::CHANGE_SIGNATURE, // User can change their signature
            'DEACTIVATE_ACCOUNT' => Site::DEACTIVATE_ACCOUNT, // Can deactivate their account
            'VIEW_PROFILE_DATA' => Site::VIEW_PROFILE_DATA, // Can view other's profile data
            'MANAGE_FRIENDS' => Site::MANAGE_FRIENDS, // Can manage friends (add/remove)
            'REPORT_USERS' => Site::REPORT_USERS, // Can report users to staff
            'OBTAIN_PREMIUM' => Site::OBTAIN_PREMIUM, // Can obtain the premium rank
            'JOIN_GROUPS' => Site::JOIN_GROUPS, // Can join groups
            'CREATE_GROUP' => Site::CREATE_GROUP, // Can create a group
            'MULTIPLE_GROUPS' => Site::MULTIPLE_GROUPS, // Can create multiple groups (requires single group perm)
            'CHANGE_NAMECOLOUR' => Site::CHANGE_NAMECOLOUR, // Can change their username colour
            'STATIC_PREMIUM' => Site::STATIC_PREMIUM, // User has static premium status
            'CREATE_COMMENTS' => Site::CREATE_COMMENTS, // User can make comments
            'DELETE_COMMENTS' => Site::DELETE_COMMENTS, // User can delete own comments
            'VOTE_COMMENTS' => Site::VOTE_COMMENTS, // User can vote on comments
        ],

        // Site management permissions
        'MANAGE' => [
            'USE_MANAGE' => 1,
        ],
    ];

    // Checking if a user has the permissions to do a thing
    public static function check($layer, $action, $operator, $mode = 0)
    {
        // Check if the permission layer and the permission itself exists
        if (!array_key_exists($layer, self::$permissions) || !array_key_exists($action, self::$permissions[$layer])) {
            return false;
        }

        // Convert to the appropiate mode
        if ($mode === 2) {
            $operator = self::getRankPermissions($operator)[$layer];
        } elseif ($mode === 1) {
            $operator = self::getUserPermissions($operator)[$layer];
        }

        // Perform the bitwise AND
        if (bindec($operator) & self::$permissions[$layer][$action]) {
            return true;
        }

        // Else just return false
        return false;
    }

    // Get permission data of a rank from the database
    public static function getRankPermissions($ranks)
    {
        // Container array
        $getRanks = [];
        $perms = [];

        // Get permission row for all ranks
        foreach ($ranks as $rank) {
            $getRanks[] = Database::fetch('permissions', false, ['rank_id' => [$rank, '='], 'user_id' => [0, '=']]);
        }

        // Check if getRanks is empty or if the rank id is 0 return the fallback
        if (empty($getRanks) || in_array(0, $ranks)) {
            $getRanks = [self::$fallback];
        }

        // Go over the permission data
        foreach ($getRanks as $rank) {
            // Check if perms is empty
            if (empty($perms)) {
                // Store the data of the current rank in $perms
                $perms = [
                    'SITE' => $rank['permissions_site'],
                    'MANAGE' => $rank['permissions_manage'],
                ];
            } else {
                // Perform a bitwise OR on the ranks
                $perms = [
                    'SITE' => $perms['SITE'] | $rank['permissions_site'],
                    'MANAGE' => $perms['MANAGE'] | $rank['permissions_manage'],
                ];
            }
        }

        // Return the compiled permission strings
        return $perms;
    }

    // Get permission data for a user
    public static function getUserPermissions($uid)
    {
        // Get user data
        $user = User::construct($uid);

        // Attempt to get the permission row of a user
        $userPerms = Database::fetch('permissions', false, ['rank_id' => [0, '='], 'user_id' => [$user->id(), '=']]);

        // Get their rank permissions
        $rankPerms = self::getRankPermissions($user->ranks());

        // Just return the rank permissions if no special ones are set
        if (empty($userPerms)) {
            return $rankPerms;
        }

        // Split the inherit option things up
        $inheritance = str_split($userPerms['permissions_inherit']);

        // Override site permissions
        if (!$inheritance[0]) {
            $rankPerms['SITE'] = $userPerms['permissions_site'];
        }

        // Override management permissions
        if (!$inheritance[1]) {
            $rankPerms['MANAGE'] = $userPerms['permissions_manage'];
        }

        // Return permissions
        return $rankPerms;
    }
}
