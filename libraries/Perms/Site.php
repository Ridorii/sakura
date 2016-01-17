<?php
/*
 * Global site permissions
 */

namespace Sakura\Perms;

/**
 * Class Site
 * @package Sakura
 */
class Site
{
    const DEACTIVATED = 1; // Is a user deactivated
    const RESTRICTED = 2; // Is a user restricted
    const ALTER_PROFILE = 4; // Can alter their profile data
    const CHANGE_AVATAR = 8; // Can change their avatar
    const CHANGE_BACKGROUND = 16; // Can change their background
    const CHANGE_HEADER = 32; // User can change their profile header
    const VIEW_MEMBERLIST = 64; // Can view the memberlist
    const CREATE_USERPAGE = 128; // Can create a userpage
    const CHANGE_USERPAGE = 256; // Can change their userpage
    const USE_MESSAGES = 512; // Can use the Private Messaging system
    const SEND_MESSAGES = 1024; // Can send Private Messages to other users
    const CHANGE_EMAIL = 2048; // Can change their account e-mail address
    const CHANGE_USERNAME = 4096; // Can change their username
    const CHANGE_USERTITLE = 8192; // Can change their usertitle
    const CHANGE_PASSWORD = 16384; // Can change their password
    const ALTER_RANKS = 32768; // Can change their ranks
    const MANAGE_SESSIONS = 65536; // Can manage their sessions
    const CHANGE_SIGNATURE = 131072; // User can change their signature
    const DEACTIVATE_ACCOUNT = 262144; // Can deactivate their account
    const VIEW_PROFILE_DATA = 524288; // Can view other's profile data
    const MANAGE_FRIENDS = 1048576; // Can manage friends (add/remove)
    const REPORT_USERS = 2097152; // Can report users to staff
    const OBTAIN_PREMIUM = 4194304; // Can obtain the premium rank
    const JOIN_GROUPS = 8388608; // Can join groups
    const CREATE_GROUP = 16777216; // Can create a group
    const MULTIPLE_GROUPS = 33554432; // Can create multiple groups (requires single group perm)
    const CHANGE_NAMECOLOUR = 67108864; // Can change their username colour
    const STATIC_PREMIUM = 134217728; // User has static premium status
    const CREATE_COMMENTS = 268435456; // User can make comments
    const DELETE_COMMENTS = 536870912; // User can delete own comments
    const VOTE_COMMENTS = 1073741824; // User can vote on comments
}
