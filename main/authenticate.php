<?php
/*
 * Sakura Authentication Page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Add page specific things
$renderData['page'] = [
    'title'         => 'Login to Flashii'
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
print Main::tplRender('main/authenticate.tpl', $renderData);
