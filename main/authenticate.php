<?php
/*
 * Sakura Authentication Page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Page actions
if(
    isset($_REQUEST['mode']) &&
    isset($_REQUEST['time']) &&
    isset($_REQUEST['session'])
) {

    switch($_REQUEST['mode']) {

        // Login processing
        case 'login':

            // Attempt login
            $login = Users::login($_REQUEST['username'], $_REQUEST['password'], isset($_REQUEST['remember']));

            // Array containing "human understandable" messages
            $messages = [
                'USER_NOT_EXIST'        => 'The user you tried to log into does not exist.',
                'INCORRECT_PASSWORD'    => 'The password you entered was invalid.',
                'DEACTIVATED'           => 'Your account is deactivated.',
                'LEGACY_SUCCESS'        => 'Login successful! Taking you to the password changing page...',
                'LOGIN_SUCESS'          => 'Login successful!'
            ];

            // Add page specific things
            $renderData['page'] = [
                'title'     => 'Login',
                'redirect'  => ($login[0] ? $_REQUEST['redirect'] : '/authenticate'),
                'message'   => $messages[$login[1]]
            ];

            break;

        // Registration processing
        case 'register':

            // Add page specific things
            $renderData['page'] = [
                'title'     => 'Register on Flashii',
                'redirect'  => $_SERVER['PHP_SELF'],
                'message'   => 'what'
            ];

            break;

        // Unforgetting passwords
        case 'forgotpassword':

            // Add page specific things
            $renderData['page'] = [
                'title'     => 'Forgot Password',
                'redirect'  => $_SERVER['PHP_SELF'],
                'message'   => 'what'
            ];

            break;

    }

    // Print page contents or if the AJAX request is set only display the render data
    print   isset($_REQUEST['ajax']) ?
            (
                $renderData['page']['title']
                . ':'
                . $renderData['page']['message']
                . ':'
                . $renderData['page']['redirect']
            ) :
            Templates::render('errors/information.tpl', $renderData);
    exit;

}

// Add page specific things
$renderData['page'] = [
    'title' => 'Login to Flashii'
];
$renderData['auth'] = [
    'redirect' => (
        isset($_REQUEST['chat']) ?
        Configuration::getLocalConfig('urls', 'chat') :
        (
            isset($_SERVER['HTTP_REFERER']) ?
            $_SERVER['HTTP_REFERER'] :
            Configuration::getLocalConfig('urls', 'main')
        )
    ),
    'blockRegister' => [
        'do'        => true,
        'username'  => 'test' 
    ]
];

// Print page contents
print Templates::render('main/authenticate.tpl', $renderData);
