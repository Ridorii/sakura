<?php
/**
 * Holds the appearance section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\ActiveUser;
use Sakura\Config;
use Sakura\DB;
use Sakura\File;
use Sakura\Perms\Site;
use Sakura\Router;
use Sakura\Template;

/**
 * Appearance settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AppearanceController extends Controller
{
    private function handleUpload($mode, $file)
    {
        // Handle errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return "Your file was too large!";

            case UPLOAD_ERR_PARTIAL:
                return "The upload failed!";

            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
                return "Wasn't able to save the file, contact a staff member!";

            case UPLOAD_ERR_EXTENSION:
            default:
                return "Something prevented the file upload!";
        }

        // Get the temp filename
        $tmpName = $_FILES[$mode]['tmp_name'];

        // Get the image meta data
        $meta = getimagesize($tmpName);

        // Check if image
        if (!$meta
            || (
                $meta[2] !== IMAGETYPE_GIF
                && $meta[2] !== IMAGETYPE_JPEG
                && $meta[2] !== IMAGETYPE_PNG
            )
        ) {
            return "Please upload a valid image!";
        }

        // Check dimensions
        $minWidth = Config::get("{$mode}_min_width");
        $minHeight = Config::get("{$mode}_min_height");
        $maxWidth = Config::get("{$mode}_max_width");
        $maxHeight = Config::get("{$mode}_max_height");

        if ($meta[0] < $minWidth
            || $meta[1] < $minHeight
            || $meta[0] > $maxWidth
            || $meta[1] > $maxHeight) {
            return "Your image has to be at least {$minWidth}x{$minHeight}"
                . " and not bigger than {$maxWidth}x{$maxHeight}, yours was {$meta[0]}x{$meta[1]}!";
        }

        // Check file size
        $maxFileSize = Config::get("{$mode}_max_fsize");

        if (filesize($tmpName) > $maxFileSize) {
            $maxSizeFmt = byte_symbol($maxFileSize);

            return "Your image is not allowed to be larger than {$maxSizeFmt}!";
        }

        $userId = ActiveUser::$user->id;
        $ext = image_type_to_extension($meta[2]);

        $filename = "{$mode}_{$userId}.{$ext}";

        // Create the file
        $file = File::create(file_get_contents($tmpName), $filename, ActiveUser::$user);

        // Delete the old file
        $this->deleteFile($mode);

        $column = "user_{$mode}";

        // Save new avatar
        DB::table('users')
            ->where('user_id', ActiveUser::$user->id)
            ->update([
                $column => $file->id,
            ]);

        return null;
    }

    public function deleteFile($mode)
    {
        (new File(ActiveUser::$user->{$mode}))->delete();
    }

    public function avatar()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_AVATAR)) {
            $message = "You aren't allowed to change your avatar.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;

        if ($session) {
            $avatar = $_FILES['avatar'] ?? null;
            $redirect = Router::route('settings.appearance.avatar');

            if ($avatar && $avatar['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = $this->handleUpload('avatar', $_FILES['avatar']);
                $message = $upload !== null ? $upload : "Changed your avatar!";
            } else {
                $this->deleteFile('avatar');
                $message = "Deleted your avatar!";
            }

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        return Template::render('settings/appearance/avatar');
    }

    public function background()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_BACKGROUND)) {
            $message = "You aren't allowed to change your background.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;

        if ($session) {
            $background = $_FILES['background'] ?? null;
            $redirect = Router::route('settings.appearance.background');

            if ($background && $background['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = $this->handleUpload('background', $_FILES['background']);
                $message = $upload !== null ? $upload : "Changed your background!";
            } else {
                $this->deleteFile('background');
                $message = "Deleted your background!";
            }

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        return Template::render('settings/appearance/background');
    }

    public function header()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_HEADER)) {
            $message = "You aren't allowed to change your profile header.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;

        if ($session) {
            $header = $_FILES['header'] ?? null;
            $redirect = Router::route('settings.appearance.header');

            if ($header && $header['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = $this->handleUpload('header', $_FILES['header']);
                $message = $upload !== null ? $upload : "Changed your header!";
            } else {
                $this->deleteFile('header');
                $message = "Deleted your header!";
            }

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        return Template::render('settings/appearance/header');
    }

    public function userpage()
    {
        // Check permission
        if (!(
            ActiveUser::$user->page
            && ActiveUser::$user->permission(Site::CHANGE_USERPAGE)
        ) || !ActiveUser::$user->permission(Site::CREATE_USERPAGE)) {
            $message = "You aren't allowed to change your userpage.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $userpage = $_POST['userpage'] ?? null;

        $maxLength = 65535;

        if ($session && $userpage) {
            $redirect = Router::route('settings.appearance.userpage');

            if ($session !== session_id()) {
                $message = 'Your session expired!';
                Template::vars(compact('message', 'redirect'));
                return Template::render('global/information');
            }

            if (strlen($userpage) > $maxLength) {
                $message = 'Your userpage is too long, shorten it a little!';
                Template::vars(compact('message', 'redirect'));
                return Template::render('global/information');
            }

            // Update database
            DB::table('users')
                ->where('user_id', ActiveUser::$user->id)
                ->update([
                    'user_page' => $userpage,
                ]);

            $message = 'Updated your userpage!';
            Template::vars(compact('message', 'redirect'));
            return Template::render('global/information');
        }

        Template::vars(compact('maxLength'));

        return Template::render('settings/appearance/userpage');
    }

    public function signature()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_SIGNATURE)) {
            $message = "You aren't allowed to change your signature.";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $session = $_POST['session'] ?? null;
        $signature = $_POST['signature'] ?? null;

        $maxLength = 500;

        if ($session && $signature) {
            $redirect = Router::route('settings.appearance.signature');

            if ($session !== session_id()) {
                $message = 'Your session expired!';
                Template::vars(compact('message', 'redirect'));
                return Template::render('global/information');
            }

            if (strlen($signature) > $maxLength) {
                $message = 'Your signature is too long, shorten it a little!';
                Template::vars(compact('message', 'redirect'));
                return Template::render('global/information');
            }

            // Update database
            DB::table('users')
                ->where('user_id', ActiveUser::$user->id)
                ->update([
                    'user_signature' => $signature,
                ]);

            $message = 'Updated your signature!';
            Template::vars(compact('message', 'redirect'));
            return Template::render('global/information');
        }

        Template::vars(compact('maxLength'));

        return Template::render('settings/appearance/signature');
    }
}
