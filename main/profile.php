<?php
/*
 * Sakura User Profiles
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Catch old profile API and return error
if(isset($_REQUEST['data'])) {

    header('Content-Type: text/plain; charset=utf-8');
	header('Access-Control-Allow-Origin: *');

    print json_encode(['error' => true]);

    exit;

}

// Get user data
if(isset($_GET['u'])) {

    $renderData['profile'] = [
        'notset'    => false,
        'user'      => ($_PROFILE_USER_DATA = Users::getUser(($_USER_USERNAME_ID = Users::userExists($_GET['u'], false)) ? $_USER_USERNAME_ID : $_GET['u'])),
        'rank'      => ($_PROFILE_RANK_DATA = Users::getRank($_PROFILE_USER_DATA['rank_main'])),
        'colour'    => ($_PROFILE_USER_DATA['name_colour']  == null ? $_PROFILE_RANK_DATA['colour'] : $_PROFILE_USER_DATA['name_colour']),
        'ranktitle' => ($_PROFILE_USER_DATA['usertitle']    == null ? $_PROFILE_RANK_DATA['title']  : $_PROFILE_USER_DATA['usertitle']),
        'country'   => Main::getCountryName($_PROFILE_USER_DATA['country']),
        'istenshi'  => Users::checkUserTenshi($_PROFILE_USER_DATA['id']),
        'online'    => Users::checkUserOnline($_PROFILE_USER_DATA['id']),
        'profpage'  => Main::mdParse(base64_decode($_PROFILE_USER_DATA['profile_md'])),
        'data'      => Users::getUserProfileData($_PROFILE_USER_DATA['id'])
    ];

    $renderData['page']['title'] = ($_PROFILE_USER_DATA['id'] < 1 || $_PROFILE_USER_DATA['password_algo'] == 'nologin' ? 'User not found!' : 'Profile of '. $renderData['profile']['user']['username']);

} else {

    $renderData['profile']['notset']    = true;
    $renderData['page']['title']        = 'User not found!';

}

// Print page contents
print Templates::render('main/profile.tpl', $renderData);
