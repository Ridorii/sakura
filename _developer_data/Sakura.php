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

if(Auth::getPageType() == AUTH_FETCH) {

    // Check if user is logged into the Sakura backend if not deny
    if(/* Login check */) {

        // If so append the required arguments and accept
        Auth::AppendArguments([/* User ID */, /* Session ID */]);
        Auth::Accept();

    } else 
        Auth::Deny();

} else {

    // Check if session is active else deny
    if(/* Check if session is active */) {

        Auth::SetUserData(
            /* User ID */,
            /* Username */,
            /* User colour */
        );

    } else 
        Auth::Deny();

}

// Serve the authentication data
Auth::Serve();
