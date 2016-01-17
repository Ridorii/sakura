<?php
/*
 * Sakura user image serving
 */

// Declare Namespace
namespace Sakura;

// Define that this page won't require templating
define('SAKURA_NO_TPL', true);

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Check if the m(ode) GET request is set
if (isset($_GET['m'])) {
    switch ($_GET['m']) {
        case 'avatar':
            // Set paths
            $noAvatar = ROOT . str_replace(
                '{{ TPL }}',
                $templateName,
                Config::get('no_avatar_img')
            );
            $deactiveAvatar = ROOT . str_replace(
                '{{ TPL }}',
                $templateName,
                Config::get('deactivated_avatar_img')
            );
            $bannedAvatar = ROOT . str_replace(
                '{{ TPL }}',
                $templateName,
                Config::get('banned_avatar_img')
            );

            // If ?u= isn't set or if it isn't numeric
            if (!isset($_GET['u']) || !is_numeric($_GET['u'])) {
                $serveImage = $noAvatar;
                break;
            }

            // Get user data
            $user = User::construct($_GET['u']);

            // If user is deactivated use deactive avatar
            if ($user->permission(Perms\Site::DEACTIVATED)) {
                $serveImage = $deactiveAvatar;
                break;
            }

            // Check if user is banned
            if ($user->checkBan() || $user->permission(Perms\Site::RESTRICTED)) {
                $serveImage = $bannedAvatar;
                break;
            }

            // Check if user has an avatar set
            if (!$user->avatar) {
                $serveImage = $noAvatar;
                break;
            }

            // Attempt to get the file
            $serve = new File($user->avatar);

            // Check if the file exists
            if (!$serve->id) {
                $serveImage = $noAvatar;
                break;
            }

            // Check if the avatar exist and assign it to a value
            $serveImage = $serve->data;
            $serveMime = $serve->mime;
            $serveName = $serve->name;
            break;

        case 'background':
            // Set paths
            $noBackground = ROOT . Config::get('no_background_img');

            // If ?u= isn't set or if it isn't numeric
            if (!isset($_GET['u']) || !is_numeric($_GET['u'])) {
                $serveImage = $noBackground;
                break;
            }

            // Get user data
            $user = User::construct($_GET['u']);

            // If user is deactivated use deactive avatar
            if ($user->permission(Perms\Site::DEACTIVATED)) {
                $serveImage = $noBackground;
                break;
            }

            // Check if user is banned
            if (Bans::checkBan($_GET['u']) || $user->permission(Perms\Site::RESTRICTED)) {
                $serveImage = $noBackground;
                break;
            }

            // Check if user has a background set
            if (!$user->background) {
                $serveImage = $noBackground;
                break;
            }

            // Attempt to get the file
            $serve = new File($user->background);

            // Check if the file exists
            if (!$serve->id) {
                $serveImage = $noBackground;
                break;
            }

            // Check if the avatar exist and assign it to a value
            $serveImage = $serve->data;
            $serveMime = $serve->mime;
            $serveName = $serve->name;
            break;

        case 'header':
            // Set paths
            $noHeader = ROOT . Config::get('no_header_img');

            // If ?u= isn't set or if it isn't numeric
            if (!isset($_GET['u']) || !is_numeric($_GET['u'])) {
                $serveImage = $noHeader;
                break;
            }

            // Get user data
            $user = User::construct($_GET['u']);

            // If user is deactivated use deactive avatar
            if ($user->permission(Perms\Site::DEACTIVATED)) {
                $serveImage = $noHeader;
                break;
            }

            // Check if user is banned
            if (Bans::checkBan($_GET['u']) || $user->permission(Perms\Site::RESTRICTED)) {
                $serveImage = $noHeader;
                break;
            }

            // Check if user has a header set
            if (!$user->header) {
                $serveImage = $noHeader;
                break;
            }

            // Attempt to get the file
            $serve = new File($user->header);

            // Check if the file exists
            if (!$serve->id) {
                $serveImage = $noHeader;
                break;
            }

            // Check if the avatar exist and assign it to a value
            $serveImage = $serve->data;
            $serveMime = $serve->mime;
            $serveName = $serve->name;
            break;

        default:
            $serveImage = ROOT . Config::get('pixel_img');

    }
} else {
    $serveImage = ROOT . Config::get('pixel_img');
}

// Do some more checks
if (!isset($serveName) || !isset($serveMime)) {
    $serveName = basename($serveImage);
    $serveImage = file_get_contents($serveImage);
    $serveMime = getimagesizefromstring($serveImage)['mime'];
}

// Add original filename
header('Content-Disposition: inline; filename="' . $serveName . '"');

// Set content type
header('Content-Type: ' . $serveMime);

echo $serveImage;
