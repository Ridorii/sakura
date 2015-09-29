<?php
/*
 * Sakura Authentication Page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Page actions
if (isset($_REQUEST['mode'])) {
    // Continue
    $continue = true;

    // Make sure we're not in activate mode since adding a timestamp
    //  and accessing the PHP session id is kind of hard when you're in an e-mail client
    if (!isset($_REQUEST['mode']) || $_REQUEST['mode'] != 'activate') {
        // Compare time and session so we know the link isn't forged
        if (!isset($_REQUEST['time']) || $_REQUEST['time'] < time() - 1000) {
            $renderData['page'] = [

                'redirect' => $urls->format('AUTH_ACTION'),
                'message' => 'Timestamps differ too much, refresh the page and try again.',
                'success' => 0,

            ];

            // Prevent
            $continue = false;
        }

        // Match session ids for the same reason
        if (!isset($_REQUEST['session']) || $_REQUEST['session'] != session_id()) {
            $renderData['page'] = [

                'redirect' => $urls->format('AUTH_ACTION'),
                'message' => 'Invalid session, please try again.',
                'success' => 0,

            ];

            // Prevent
            $continue = false;
        }
    }

    // Login check
    if (Users::checkLogin()) {
        if (!in_array($_REQUEST['mode'], ['logout'])) {
            $continue = false;

            // Add page specific things
            $renderData['page'] = [

                'redirect' => $urls->format('SITE_HOME'),
                'message' => 'You are already authenticated. Redirecting...',
                'success' => 1,

            ];
        }
    }

    if ($continue) {
        switch ($_REQUEST['mode']) {
            case 'logout':
                // Attempt logout
                $logout = Users::logout();

                // Add page specific data
                $renderData['page'] = [

                    'redirect' => ($logout ? $_REQUEST['redirect'] : $urls->format('SITE_LOGIN')),
                    'message' => $logout ? 'You are now logged out.' : 'An unknown error occurred.',
                    'success' => $logout ? 1 : 0,

                ];
                break;

            case 'changepassword':
                // Attempt change
                $passforget = Users::resetPassword(
                    $_REQUEST['verk'],
                    $_REQUEST['uid'],
                    $_REQUEST['newpw'],
                    $_REQUEST['verpw']
                );

                // Array containing "human understandable" messages
                $messages = [

                    'INVALID_VERK' => 'The verification key supplied was invalid!',
                    'INVALID_CODE' => 'Invalid verification key, if you think this is an error contact the administrator.',
                    'INVALID_USER' => 'The used verification key is not designated for this user.',
                    'VERK_TOO_SHIT' => 'Your verification code is too weak, try adding some special characters.',
                    'PASS_TOO_SHIT' => 'Your password is too weak, try adding some special characters.',
                    'PASS_NOT_MATCH' => 'Passwords do not match.',
                    'SUCCESS' => 'Successfully changed your password, you may now log in.',

                ];

                // Add page specific things
                $renderData['page'] = [

                    'redirect' => (
                        $passforget[0] ?
                        $urls->format('SITE_LOGIN') :
                        $_SERVER['PHP_SELF'] . '?pw=true&uid=' . $_REQUEST['uid'] . '&verk=' . $_REQUEST['verk']
                    ),
                    'message' => $messages[$passforget[1]],
                    'success' => $passforget[0],

                ];
                break;

            // Activating accounts
            case 'activate':
                // Attempt activation
                $activate = Users::activateUser($_REQUEST['u'], true, $_REQUEST['k']);

                // Array containing "human understandable" messages
                $messages = [

                    'USER_NOT_EXIST' => 'The user you tried to activate does not exist.',
                    'USER_ALREADY_ACTIVE' => 'The user you tried to activate is already active.',
                    'INVALID_CODE' => 'Invalid activation code, if you think this is an error contact the administrator.',
                    'INVALID_USER' => 'The used activation code is not designated for this user.',
                    'SUCCESS' => 'Successfully activated your account, you may now log in.',

                ];

                // Add page specific things
                $renderData['page'] = [

                    'redirect' => $urls->format('SITE_LOGIN'),
                    'message' => $messages[$activate[1]],
                    'success' => $activate[0],

                ];
                break;

            // Resending the activation e-mail
            case 'resendactivemail':
                // Attempt send
                $resend = Users::resendActivationMail($_REQUEST['username'], $_REQUEST['email']);

                // Array containing "human understandable" messages
                $messages = [

                    'AUTH_LOCKED' => 'Authentication is currently not allowed, try again later.',
                    'USER_NOT_EXIST' => 'The user you tried to activate does not exist (confirm the username/email combination).',
                    'USER_ALREADY_ACTIVE' => 'The user you tried to activate is already active.',
                    'SUCCESS' => 'The activation e-mail has been sent to the address associated with your account.',

                ];

                // Add page specific things
                $renderData['page'] = [

                    'redirect' => $urls->format('SITE_HOME'),
                    'message' => $messages[$resend[1]],
                    'success' => $resend[0],

                ];
                break;

            // Login processing
            case 'login':
                // Attempt login
                $login = Users::login($_REQUEST['username'], $_REQUEST['password'], isset($_REQUEST['remember']));

                // Array containing "human understandable" messages
                $messages = [

                    'AUTH_LOCKED' => 'Authentication is currently not allowed, try again later.',
                    'USER_NOT_EXIST' => 'The user you tried to log into does not exist.',
                    'INCORRECT_PASSWORD' => 'The password you entered was invalid.',
                    'NOT_ALLOWED' => 'Your account does not have the required permissions to log in.',
                    'NO_LOGIN' => 'Logging into this account is disabled.',
                    'LOGIN_SUCCESS' => 'Login successful!',

                ];

                // Add page specific things
                $renderData['page'] = [

                    'redirect' => $login[0] ? ((new User($login[2]))->data['lastdate'] ? $_REQUEST['redirect'] : $urls->format('INFO_PAGE', ['welcome'])) : $urls->format('SITE_LOGIN'),
                    'message' => $messages[$login[1]],
                    'success' => $login[0],

                ];
                break;

            // Registration processing
            case 'register':
                // Attempt registration
                $register = Users::register(
                    $_REQUEST['username'],
                    $_REQUEST['password'],
                    $_REQUEST['confirmpassword'],
                    $_REQUEST['email'],
                    isset($_REQUEST['tos']),
                    (
                        Configuration::getConfig('recaptcha') ?
                        $_REQUEST['g-recaptcha-response'] :
                        null
                    ),
                    (
                        Configuration::getConfig('require_registration_code') ?
                        $_REQUEST['registercode'] :
                        null
                    )
                );

                // Array containing "human understandable" messages
                $messages = [

                    'AUTH_LOCKED' => 'Authentication is currently not allowed, try again later.',
                    'DISABLED' => 'Registration is currently disabled.',
                    'INVALID_REG_KEY' => 'The given registration code was invalid.',
                    'TOS' => 'You are required to agree to the Terms of Service.',
                    'CAPTCHA_FAIL' => 'Captcha verification failed, please try again.',
                    'USER_EXISTS' => 'A user with this username already exists, if you lost your password try using the Lost Password form.',
                    'NAME_TOO_SHORT' => 'Your name must be at least 3 characters long.',
                    'NAME_TOO_LONG' => 'Your name can\'t be longer than 16 characters.',
                    'PASS_TOO_SHIT' => 'Your password is too weak, try adding some special characters.',
                    'PASS_NOT_MATCH' => 'Passwords do not match.',
                    'INVALID_EMAIL' => 'Your e-mail address is formatted incorrectly.',
                    'INVALID_MX' => 'No valid MX-Record found on the e-mail address you supplied.',
                    'EMAILSENT' => 'Your registration went through! An activation e-mail has been sent.',
                    'SUCCESS' => 'Your registration went through! Welcome to ' . Configuration::getConfig('sitename') . '!',

                ];

                // Add page specific things
                $renderData['page'] = [

                    'redirect' => ($register[0] ? $urls->format('SITE_LOGIN') : $urls->format('SITE_REGISTER')),
                    'message' => $messages[$register[1]],
                    'success' => $register[0],

                ];
                break;

            // Unforgetting passwords
            case 'forgotpassword':
                // Attempt send
                $passforgot = Users::sendPasswordForgot($_REQUEST['username'], $_REQUEST['email']);

                // Array containing "human understandable" messages
                $messages = [

                    'AUTH_LOCKED' => 'Authentication is currently not allowed, try again later.',
                    'USER_NOT_EXIST' => 'The requested user does not exist (confirm the username/email combination).',
                    'NOT_ALLOWED' => 'Your account does not have the required permissions to change your password.',
                    'SUCCESS' => 'The password reset e-mail has been sent to the address associated with your account.',

                ];

                // Add page specific things
                $renderData['page'] = [

                    'redirect' => $urls->format('SITE_FORGOT_PASSWORD'),
                    'message' => $messages[$passforgot[1]],
                    'success' => $passforgot[0],

                ];
                break;

        }

    }

    // Print page contents or if the AJAX request is set only display the render data
    print isset($_REQUEST['ajax']) ?
    (
        $renderData['page']['message'] . '|' .
        $renderData['page']['success'] . '|' .
        $renderData['page']['redirect']
    ) :
    Templates::render('global/information.tpl', $renderData);
    exit;

}

// Add page specific things
$renderData['auth'] = [

    'redirect' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $urls->format('SITE_HOME'),
    'blockRegister' => [

        'do' => false,

    ],

];

// Check if the user is already logged in
if (Users::checkLogin()) {
    // Add page specific things
    $renderData['page'] = [

        'redirect' => $urls->format('SITE_HOME'),
        'message' => 'You are already logged in, log out to access this page.',

    ];

    print Templates::render('global/information.tpl', $renderData);
    exit;
}

// Check if a user has already registered from the current IP address
if (count($regUserIP = Users::getUsersByIP(Main::getRemoteIP()))) {
    $renderData['auth']['blockRegister'] = [

        'do' => true,
        'username' => $regUserIP[array_rand($regUserIP)]['username'],

    ];
}

// If password forgot things are set display password forget thing
if (isset($_REQUEST['pw']) && $_REQUEST['pw']) {
    $renderData['auth']['changingPass'] = true;
    $renderData['auth']['userId'] = $_REQUEST['uid'];

    if (isset($_REQUEST['key'])) {
        $renderData['auth']['forgotKey'] = $_REQUEST['key'];
    }

    print Templates::render('main/forgotpassword.tpl', $renderData);
    exit;
}

// Print page contents
print Templates::render('main/authenticate.tpl', $renderData);