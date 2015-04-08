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

        case 'login':
        case 'register':
        case 'forgotpassword':
            // Add page specific things
            $renderData['page'] = [
                'title'     => 'auth test',
                'redirect'  => $_SERVER['PHP_SELF'],
                'message'   => 'meow meow meow meow meow meow meow meow meow meow meow meow'
            ];

            // Print page contents
            print Templates::render('errors/information.tpl', $renderData);
            exit;

    }

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
