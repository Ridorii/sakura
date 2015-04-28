<?php
/*
 * Sakura Sock Chat authentication script
 * By Flashwave
 */

// Filesystem path to the _sakura folder WITHOUT an ending /
// This can also be set before an include of this file in case
//  you're using git to keep in sync and don't want conflicts
if(!isset($sockSakuraPath))
    $sockSakuraPath = ''; 

/* * * DON'T EDIT ANYTHING BELOW THIS LINE * * */

// Include Sakura
require_once $sockSakuraPath .'/sakura.php';

use sockchat\Auth;
use Sakura\Session;
use Sakura\Users;
use Sakura\SockChat;

if(Auth::getPageType() == AUTH_FETCH) {

    // Check if user is logged into the Sakura backend if not deny
    if(Users::checkLogin()) {

        // If so append the required arguments and accept
        Auth::AppendArguments([Session::$userId, Session::$sessionId]);
        Auth::Accept();

    } else 
        Auth::Deny();

} else {

    // Get arguments
    $uid = $_REQUEST['arg1'];
    $sid = $_REQUEST['arg2'];

    // Check if session is active else deny
    if(Session::checkSession($uid, $sid)) {

        // Get user and rank data
        $user = Users::getUser($uid);
        $rank = Users::getRank($user['rank_main']);

        // Deny group and user id 0
        if($user['id'] == 0 || $rank['id'] == 0 || $user['password_algo'] == 'nologin') {

            Auth::Deny();
            Auth::Serve();
            exit;

        }

        // Set the user's data
        Auth::SetUserData(
            $user['id'],
            $user['username'],
            $user['name_colour'] == null ? $rank['colour'] : $user['name_colour']
        );

        // Get the user's permissions
        $perms = SockChat::getUserPermissions($user['id']);

        // Check if they can access the chat
        if(!$perms['access']) {

            Auth::Deny();
            Auth::Serve();
            exit;

        }

        // Set the common permissions
        Auth::SetCommonPermissions(
            $perms['rank'],
            $perms['type'],
            $perms['logs'],
            $perms['nick'],
            $perms['channel']
        );

        Auth::Accept();

    } else 
        Auth::Deny();

}

// Serve the authentication data
Auth::Serve();
