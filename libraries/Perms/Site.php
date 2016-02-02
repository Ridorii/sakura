<?php
namespace Sakura\Perms;

/**
 * All global site permissions.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Site
{
    /**
     * Is this user deactivated?
     */
    const DEACTIVATED = 1;

    /**
     * Is this user restricted?
     */
    const RESTRICTED = 2;

    /**
     * Can this user alter their profile?
     */
    const ALTER_PROFILE = 4;

    /**
     * Can this user change their avatar?
     */
    const CHANGE_AVATAR = 8;

    /**
     * Can this user change their profile background?
     */
    const CHANGE_BACKGROUND = 16;

    /**
     * Can this user change their profile header?
     */
    const CHANGE_HEADER = 32;

    /**
     * Can this user view the memberlist?
     */
    const VIEW_MEMBERLIST = 64;

    /**
     * Can this user create a userpage?
     */
    const CREATE_USERPAGE = 128;

    /**
     *  Can this user change their userpage?
     */
    const CHANGE_USERPAGE = 256;

    /**
     * Can this user use the private messaging system?
     */
    const USE_MESSAGES = 512;

    /**
     * Can this user send private messages?
     */
    const SEND_MESSAGES = 1024;

    /**
     * Can this user change the e-mail address associated with their account?
     */
    const CHANGE_EMAIL = 2048;

    /**
     * Can this user change their username (within the configured timeframe)?
     */
    const CHANGE_USERNAME = 4096;

    /**
     * Can this user change the user title?
     */
    const CHANGE_USERTITLE = 8192;

    /**
     * Can this user change the password to their account?
     */
    const CHANGE_PASSWORD = 16384;

    /**
     * Can this user manage the ranks they're part of?
     */
    const ALTER_RANKS = 32768;

    /**
     * Can this user manage the active sessions on their account?
     */
    const MANAGE_SESSIONS = 65536;

    /**
     * Can this user change their forum signature?
     */
    const CHANGE_SIGNATURE = 131072;

    /**
     * Can this user deactivate their account?
     */
    const DEACTIVATE_ACCOUNT = 262144;

    /**
     * Can this user view the external accounts on other's profiles?
     */
    const VIEW_PROFILE_DATA = 524288;

    /**
     * Can this user manage friends?
     */
    const MANAGE_FRIENDS = 1048576;

    /**
     * Can this user report other users?
     */
    const REPORT_USERS = 2097152;

    /**
     * Is this user allowed to buy premium?
     */
    const OBTAIN_PREMIUM = 4194304;

    /**
     * Can this user join groups?
     */
    const JOIN_GROUPS = 8388608;

    /**
     * Can this user create a group?
     */
    const CREATE_GROUP = 16777216;

    /**
     * Can this user create more than one group (requires CREATE_GROUP permission as well)?
     */
    const MULTIPLE_GROUPS = 33554432;

    /**
     * Can this user change the colour of their username?
     */
    const CHANGE_NAMECOLOUR = 67108864;
    
    /**
     * Does this user have infinite premium?
     */
    const STATIC_PREMIUM = 134217728;

    /**
     * Can this user create comments?
     */
    const CREATE_COMMENTS = 268435456;

    /**
     * Can this user delete their own comments?
     */
    const DELETE_COMMENTS = 536870912;

    /**
     * Can this user vote on comments?
     */
    const VOTE_COMMENTS = 1073741824;
}
