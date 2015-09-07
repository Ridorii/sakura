<?php
/*
 * Sakura Sock Chat authentication script
 * By Flashwave
 */

// Filesystem path to the _sakura folder WITHOUT an ending /
// This can also be set before an include of this file in case
//  you're using git to keep in sync and don't want conflicts
// You can also create a PHP file including this SockChat.php
//  file so it's always up-to-date! Don't forget to include the
//  variable below in the file __BEFORE__ the include!
if(!isset($sockSakuraPath)) {

    $sockSakuraPath = '';

}

/* * * DON'T EDIT ANYTHING BELOW THIS LINE * * */

// Include Sakura
require_once $sockSakuraPath .'/sakura.php';

use sockchat\Auth;
use Sakura\Session;
use Sakura\Users;
use Sakura\Permissions;
use Sakura\User;

if(Auth::getPageType() == AUTH_FETCH) {

    // Check if user is logged into the Sakura backend if not deny
    if(Users::checkLogin()) {

        // If so append the required arguments and accept
        Auth::AppendArguments([Session::$userId, Session::$sessionId]);
        Auth::Accept();

    } else {

        Auth::Deny();

    }

} else {

    // Get arguments
    $uid = $_REQUEST['arg1'];
    $sid = $_REQUEST['arg2'];

    // Check if session is active else deny
    if(Session::checkSession($uid, $sid)) {

        // Check if they can access the chat
        if(Permissions::check('SITE', 'DEACTIVATED', $uid, 1) && Permissions::check('SITE', 'RESTRICTED', $uid, 1)) {

            Auth::Deny();
            Auth::Serve();
            exit;

        }

        // Create a user object
        $user = new User($uid);

        // Set the user's data
        Auth::SetUserData(
            $user->data['id'],
            $user->data['username'],
            $user->colour()
        );

        // Set the common permissions
        Auth::SetCommonPermissions(
            bindec(Permissions::getUserPermissions($uid)['SITE']),
            Permissions::check('MANAGE',    'USE_MANAGE',           $uid, 1) ? 1 : 0,
            Permissions::check('SITE',      'CREATE_BACKGROUND',    $uid, 1) ? 1 : 0,
            Permissions::check('SITE',      'CHANGE_USERNAME',      $uid, 1) ? 1 : 0,
            Permissions::check('SITE',      'MULTIPLE_GROUPS',      $uid, 1) ? 2 : (
                Permissions::check('SITE', 'CREATE_GROUP',          $uid, 1) ? 1 : 0
            )
        );

        Auth::Accept();

    } else {

        Auth::Deny();

    }

}

// Serve the authentication data
Auth::Serve();
