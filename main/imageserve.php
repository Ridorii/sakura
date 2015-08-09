<?php
/*
 * Sakura user image serving
 */

// Declare Namespace
namespace Sakura;

// Define that this page won't require templating
define('SAKURA_NO_TPL', true);

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Set Content type
header('Content-Type: application/octet-stream');

// Path to user uploads
$userDirPath = ROOT . Configuration::getConfig('user_uploads') . '/';

// Check if the m(ode) GET request is set
if(isset($_GET['m'])) {

    switch($_GET['m']) {

        case 'avatar':
            // Set paths
            $noAvatar       = ROOT . Configuration::getConfig('no_avatar_img');
            $deactiveAvatar = ROOT . Configuration::getConfig('deactivated_avatar_img');
            $bannedAvatar   = ROOT . Configuration::getConfig('banned_avatar_img');

            // If ?u= isn't set or if it isn't numeric
            if(!isset($_GET['u']) || !is_numeric($_GET['u']) || $_GET['u'] == 0) {
                $serveImage = $noAvatar;
                break;
            }

            // Get user data
            $user = Users::getUser($_GET['u']);

            // If user is deactivated use deactive avatar
            if(Users::checkIfUserHasRanks([0, 1], $user, true)) {
                $serveImage = $deactiveAvatar;
                break;
            }

            // Check if user is banned
            if(Bans::checkBan($_GET['u'])) {
                $serveImage = $bannedAvatar;
                break;
            }

            // Check if user has an avatar set
            if(empty($user['userData']['userAvatar']) || !file_exists($userDirPath . $user['userData']['userAvatar'])) {
                $serveImage = $noAvatar;
                break;
            }

            // Check if the avatar exist and assign it to a value
            $serveImage = $userDirPath . $user['userData']['userAvatar'];
            break;

        case 'background':
            // Set paths
            $noBackground = ROOT . Configuration::getConfig('no_background_img');

            // If ?u= isn't set or if it isn't numeric
            if(!isset($_GET['u']) || !is_numeric($_GET['u'])) {
                $serveImage = $noBackground;
                break;
            }

            // Get user data
            $user = Users::getUser($_GET['u']);

            // If user is deactivated use deactive avatar
            if(Users::checkIfUserHasRanks([0, 1], $user, true)) {
                $serveImage = $noBackground;
                break;
            }

            // Check if user is banned
            if(Bans::checkBan($_GET['u'])) {
                $serveImage = $noBackground;
                break;
            }

            // Check if user has a background set
            if(empty($user['userData']['profileBackground']) || !file_exists($userDirPath . $user['userData']['profileBackground'])) {
                $serveImage = $noBackground;
                break;
            }

            // Check if the avatar exist and assign it to a value
            $serveImage = $userDirPath . $user['userData']['profileBackground'];
            break;

        case 'header':
            // Set paths
            $noHeader = ROOT . Configuration::getConfig('no_header_img');

            // If ?u= isn't set or if it isn't numeric
            if(!isset($_GET['u']) || !is_numeric($_GET['u'])) {
                $serveImage = $noHeader;
                break;
            }

            // Get user data
            $user = Users::getUser($_GET['u']);

            // If user is deactivated use deactive avatar
            if(Users::checkIfUserHasRanks([0, 1], $user, true)) {
                $serveImage = $noHeader;
                break;
            }

            // Check if user is banned
            if(Bans::checkBan($_GET['u'])) {
                $serveImage = $noHeader;
                break;
            }

            // Check if user has a background set
            if(empty($user['userData']['profileHeader']) || !file_exists($userDirPath . $user['userData']['profileHeader'])) {
                $serveImage = $noHeader;
                break;
            }

            // Check if the avatar exist and assign it to a value
            $serveImage = $userDirPath . $user['userData']['profileHeader'];
            break;

        default:
            $serveImage = ROOT . Configuration::getConfig('pixel_img');

    }

} else
    $serveImage = ROOT . Configuration::getConfig('pixel_img');

$serveImage = file_get_contents($serveImage);

header('Content-Type: ' .getimagesizefromstring($serveImage)['mime']);

print $serveImage;
