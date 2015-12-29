<?php
/*
 * Sakura Sock Chat authentication script
 * By Flashwave
 */

// Filesystem path to the sakura root directory WITHOUT an ending /
// This can also be set before an include of this file in case
//  you're using git to keep in sync and don't want conflicts
// You can also create a PHP file including this SockChat.php
//  file so it's always up-to-date! Don't forget to include the
//  variable below in the file __BEFORE__ the include!
if (!isset($sockSakuraPath)) {
    $sockSakuraPath = '';
}

/* * * DON'T EDIT ANYTHING BELOW THIS LINE * * */

// Include Sakura
require_once $sockSakuraPath . '/sakura.php';

use Sakura\Perms;
use Sakura\Perms\Site;
use Sakura\Perms\Manage;
use Sakura\User;
use Sakura\Rank;
use Sakura\Users;
use sockchat\Auth;

if (Auth::getPageType() == AUTH_FETCH) {
    // Check if user is logged into the Sakura backend if not deny
    if ($data = Users::checkLogin()) {
        // If so append the required arguments and accept
        Auth::AppendArguments([$data[0], $data[1]]);
        Auth::Accept();
    } else {
        Auth::Deny();
    }
} else {
    // Get arguments
    $uid = $_REQUEST['arg1'];
    $sid = $_REQUEST['arg2'];

    // Check if session is active else deny
    if ($data = Users::checkLogin($uid, $sid)) {
        // Create a user object
        $user = User::construct($uid);

        // Check if they can access the chat
        if ($user->permission(Site::DEACTIVATED) || $user->permission(Site::RESTRICTED)) {
            Auth::Deny();
            Auth::Serve();
            exit;
        }

        // Set the user's data
        Auth::SetUserData(
            $user->id(),
            $user->username(),
            $user->colour()
        );

        // Set the common permissions
        Auth::SetCommonPermissions(
            Rank::construct($user->mainRank())->hierarchy(),
            $user->permission(Manage::USE_MANAGE, Perms::MANAGE) ? 1 : 0,
            $user->permission(Site::CREATE_BACKGROUND) ? 1 : 0,
            $user->permission(Site::CHANGE_USERNAME) ? 1 : 0,
            $user->permission(Site::MULTIPLE_GROUPS) ? 2 : (
                $user->permission(Site::CREATE_GROUP) ? 1 : 0
            )
        );

        Auth::Accept();
    } else {
        Auth::Deny();
    }
}

// Serve the authentication data
Auth::Serve();
