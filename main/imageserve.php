<?php
/*
 * Sakura user image serving
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Set Content type
header('Content-Type: application/octet-stream');

// Check if the m(ode) GET request is set
if(isset($_GET['m'])) {
    switch($_GET['m']) {
        case 'avatar':
            // Set path to no avatar picture
            $noAvatar       = ROOT .'content/images/no-av.png';
            $deactiveAvatar = ROOT .'content/images/deactivated-av.png';
            $bannedAvatar   = ROOT .'content/images/banned-av.png';
            $avatarDirPath  = ROOT .'content/images/avatars/';

            // If ?u= isn't set or if it isn't numeric
            if(!isset($_GET['u']) || !is_numeric($_GET['u'])) {
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
            if(false) { // [Flashwave 2015-04-27] Banning isn't implemented yet
                $serveImage = $bannedAvatar;
                break;
            }

            // Check if user has an avatar set
            if(empty($user['avatar_url']) || !file_exists($avatarDirPath . $user['avatar_url'])) {
                $serveImage = $noAvatar;
                break;
            }

            // Check if the avatar exist and assign it to a value
            $serveImage = $avatarDirPath . $user['avatar_url'];
            break;
        
        case 'background':
            // Set path to no avatar picture
            $noBackground = ROOT .'content/pixel.png';

            // If ?u= isn't set or if it isn't numeric
            if(!isset($_GET['u']) || !is_numeric($_GET['u'])) {
                $serveImage = $noBackground;
                break;
            }

            // Check if the avatar exist and assign it to a value
            $serveImage = empty(Users::getUser($_GET['u'])['profilebg']) ? $noBackground : Users::getUser($_GET['u'])['profilebg'];
            break;
        
        default:
            $serveImage = ROOT .'content/pixel.png';
    }
} else {
    $serveImage = ROOT .'content/pixel.png';
}

$serveImage = file_get_contents($serveImage);

header('Content-Type: ' .getimagesizefromstring($serveImage)['mime']);

print $serveImage;
