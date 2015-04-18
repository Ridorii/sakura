<?php
/*
 * Sakura Sock Chat authentication script
 * By Flashwave
 */

// Settings
$sockSakuraPath = ''; // Filesystem path to the _sakura folder WITHOUT an ending /

/* * * DON'T EDIT ANYTHING BELOW THIS LINE * * */

// Include Sakura
require_once $sockSakuraPath .'/sakura.php';

use sockchat\Auth;
use Sakura\Session;
use Sakura\Users;

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
    $uid = $_GET['arg1'];
    $sid = $_GET['arg2'];

    // Check if session is active else deny
    if(Session::checkSession($uid, $sid)) {

        // Get user and rank data
        $user = Users::getUser($uid);
        $rank = Users::getRank($user['rank_main']);

        // Deny group and user id 0
        if($user['id'] == 0 || $rank['id'] == 0) {

            Auth::Deny();
            Auth::Serve();
            exit;

        }

        Auth::SetUserData(
            $user['id'],
            $user['username'],
            $rank['colour']
        );

        switch($rank['id']) {

            default: // Fallback
            case 2: // Regular User
                Auth::SetCommonPermissions(
                    0,
                    USER_NORMAL,
                    LOGS_DISABLED,
                    NICK_DISABLED,
                    CHANNEL_CREATE_DISABLED
                );
                break;

            case 6: // Bot
            case 8: // Tenshi
            case 9: // Alumni
                Auth::SetCommonPermissions(
                    1,
                    USER_NORMAL,
                    LOGS_ENABLED,
                    NICK_ENABLED,
                    CHANNEL_CREATE_TEMP
                );
                break;

            case 3: // Site Moderator
            case 5: // Developer
            case 6: // Chat Moderator
                Auth::SetCommonPermissions(
                    ($rank['id'] == 2 ? 3 : 2), // Site moderators are 3, rest is 2
                    USER_MODERATOR,
                    LOGS_ENABLED,
                    NICK_ENABLED,
                    CHANNEL_CREATE_TEMP
                );
                break;

            case 4: // Administrator
                Auth::SetCommonPermissions(
                    4,
                    USER_MODERATOR,
                    LOGS_ENABLED,
                    NICK_ENABLED,
                    CHANNEL_CREATE_PERM
                );
                break;


        }

        Auth::Accept();

    } else 
        Auth::Deny();

}

// Serve the authentication data
Auth::Serve();
