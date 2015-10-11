<?php
/*
 * Permission Handler
 */

namespace Sakura;

class Permissions
{
    // Fallback permission data
    private static $fallback = [

        'rank_id' => 0,
        'user_id' => 0,
        'permissions_site' => 1,
        'permissions_manage' => 0,
        'permissions_forums' => 0,
        'permissions_inherit' => 111,

    ];

    // Global permissions table
    protected static $permissions = [

        // Site permissions
        'SITE' => [

            'DEACTIVATED' => 1, // Is a user deactivated
            'RESTRICTED' => 2, // Is a user restricted
            'ALTER_PROFILE' => 4, // Can alter their profile data
            'CHANGE_AVATAR' => 8, // Can change their avatar
            'CREATE_BACKGROUND' => 16, // Can create a background (different from changing)
            'CHANGE_BACKGROUND' => 32, // Can change their background
            'VIEW_MEMBERLIST' => 64, // Can view the memberlist
            'CREATE_USERPAGE' => 128, // Can create a userpage (different from changing)
            'CHANGE_USERPAGE' => 256, // Can change their userpage
            'USE_MESSAGES' => 512, // Can use the Private Messaging system
            'SEND_MESSAGES' => 1024, // Can send Private Messages to other users
            'CHANGE_EMAIL' => 2048, // Can change their account e-mail address
            'CHANGE_USERNAME' => 4096, // Can change their username
            'CHANGE_USERTITLE' => 8192, // Can change their usertitle
            'CHANGE_PASSWORD' => 16384, // Can change their password
            'ALTER_RANKS' => 32768, // Can change their ranks
            'MANAGE_SESSIONS' => 65536, // Can manage their sessions
            'CREATE_REGKEYS' => 131072, // Can create registration keys
            'DEACTIVATE_ACCOUNT' => 262144, // Can deactivate their account
            'VIEW_PROFILE_DATA' => 524288, // Can view other's profile data
            'MANAGE_FRIENDS' => 1048576, // Can manage friends (add/remove)
            'REPORT_USERS' => 2097152, // Can report users to staff
            'OBTAIN_PREMIUM' => 4194304, // Can obtain the premium rank
            'JOIN_GROUPS' => 8388608, // Can join groups
            'CREATE_GROUP' => 16777216, // Can create a group
            'MULTIPLE_GROUPS' => 33554432, // Can create multiple groups (requires single group perm)
            'CHANGE_NAMECOLOUR' => 67108864, // Can change their username colour
            'STATIC_PREMIUM' => 134217728, // User has static premium status
            'CREATE_COMMENTS' => 268435456, // User can make comments
            'DELETE_COMMENTS' => 536870912, // User can delete own comments
            'VOTE_COMMENTS' => 1073741824, // User can vote on comments
            'CHANGE_SIGNATURE' => 2147483648, // User can vote on comments

        ],

        // Forum permissions
        'FORUM' => [

            'USE_FORUM' => 1,

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
                    'FORUM' => $rank['permissions_forums'],

                ];
            } else {
                // Perform a bitwise OR on the ranks
                $perms = [

                    'SITE' => $perms['SITE'] | $rank['permissions_site'],
                    'MANAGE' => $perms['MANAGE'] | $rank['permissions_manage'],
                    'FORUM' => $perms['FORUM'] | $rank['permissions_forums'],

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
        $user = new User($uid);

        // Attempt to get the permission row of a user
        $userPerms = Database::fetch('permissions', false, ['rank_id' => [0, '='], 'user_id' => [$user->data['user_id'], '=']]);

        // Get their rank permissions
        $rankPerms = self::getRankPermissions(json_decode($user->data['user_ranks'], true));

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

        // Override forum permissions
        if (!$inheritance[2]) {
            $rankPerms['FORUM'] = $userPerms['permissions_forums'];
        }

        // Return permissions
        return $rankPerms;

    }
}
