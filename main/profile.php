<?php
/*
 * Sakura User Profiles
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Get user data
if(isset($_GET['u'])) {

    $renderData['profile'] = [
        'notset'        => false,
        'user'          => ($_PROFILE_USER_DATA = Users::getUser(($_USER_USERNAME_ID = Users::userExists($_GET['u'], false)) ? $_USER_USERNAME_ID : $_GET['u'])),
        'rank'          => ($_PROFILE_RANK_DATA = Users::getRank($_PROFILE_USER_DATA['rank_main'])),
        'colour'        => ($_PROFILE_USER_DATA['name_colour']  == null ? $_PROFILE_RANK_DATA['colour'] : $_PROFILE_USER_DATA['name_colour']),
        'ranktitle'     => ($_PROFILE_USER_DATA['usertitle']    == null ? $_PROFILE_RANK_DATA['title']  : $_PROFILE_USER_DATA['usertitle']),
        'country'       => Main::getCountryName($_PROFILE_USER_DATA['country']),
        'is_premium'    => Users::checkUserPremium($_PROFILE_USER_DATA['id'])[0],
        'is_online'     => Users::checkUserOnline($_PROFILE_USER_DATA['id']),
        'profilePage'   => Users::getProfilePage($_PROFILE_USER_DATA['userData'], true),
        'fields'        => Users::getUserProfileFields($_PROFILE_USER_DATA['userData'], true),
        'warnings'      => Users::getWarnings($_PROFILE_USER_DATA['id']),
        'friend'        => Users::checkFriend($_PROFILE_USER_DATA['id'])
    ];

    $renderData['page'] = [
        'title'         => ($_PROFILE_USER_DATA['id'] < 1 || $_PROFILE_USER_DATA['password_algo'] == 'nologin' ? 'User not found!' : 'Profile of '. $_PROFILE_USER_DATA['username']),
        'style'         => (!empty($_PROFILE_USER_DATA['userData']['profileBackground']) ? [
            '#userBackground' => [
                'background' => 'url("/bg/'. $_PROFILE_USER_DATA['id'] .'") no-repeat center center / cover transparent !important',
                'position' => 'fixed',
                'top' => '0',
                'bottom' => '0',
                'right' => '0',
                'left' => '0',
                'z-index' => '-1'
            ]
        ] : null)
    ];

} else {

    $renderData['profile']['notset']    = true;
    $renderData['page']['title']        = 'User not found!';

}

// Print page contents
print Templates::render('main/profile.tpl', $renderData);
