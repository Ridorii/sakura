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
            $noAvatar = ROOT .'content/images/no-av.png';

            // If ?u= isn't set or if it isn't numeric
            if(!isset($_GET['u']) || !is_numeric($_GET['u'])) {
                $serveImage = $noAvatar;
                break;
            }

            // Check if the avatar exist and assign it to a value
            $serveImage = empty(Users::getUser($_GET['u'])['avatar_url']) ? $noAvatar : Users::getUser($_GET['u'])['avatar_url'];
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
