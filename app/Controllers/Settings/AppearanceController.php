<?php
/**
 * Holds the appearance section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\ActiveUser;
use Sakura\DB;
use Sakura\File;
use Sakura\Perms\Site;

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

        $confp = $mode === 'header' ? 'cover' : $mode;

        // Check dimensions
        $maxWidth = config("file.{$confp}.max_width");
        $maxHeight = config("file.{$confp}.max_height");

        if ($meta[0] > $maxWidth
            || $meta[1] > $maxHeight) {
            return "Your image has to be at least {$minWidth}x{$minHeight}"
                . " and not bigger than {$maxWidth}x{$maxHeight}, yours was {$meta[0]}x{$meta[1]}!";
        }

        // Check file size
        $maxFileSize = config("file.{$confp}.max_file_size");

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
            $redirect = route('settings.index');
            return view('global/information', compact('message', 'redirect'));
        }

        if (session_check()) {
            $avatar = $_FILES['avatar'] ?? null;
            $redirect = route('settings.appearance.avatar');

            if ($avatar && $avatar['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = $this->handleUpload('avatar', $_FILES['avatar']);
                $message = $upload !== null ? $upload : "Changed your avatar!";
            } else {
                $this->deleteFile('avatar');
                $message = "Deleted your avatar!";
            }

            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/appearance/avatar');
    }

    public function background()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_BACKGROUND)) {
            $message = "You aren't allowed to change your background.";
            $redirect = route('settings.index');
            return view('global/information', compact('message', 'redirect'));
        }

        if (session_check()) {
            $background = $_FILES['background'] ?? null;
            $redirect = route('settings.appearance.background');

            if ($background && $background['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = $this->handleUpload('background', $_FILES['background']);
                $message = $upload !== null ? $upload : "Changed your background!";
            } else {
                $this->deleteFile('background');
                $message = "Deleted your background!";
            }

            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/appearance/background');
    }

    public function header()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_HEADER)) {
            $message = "You aren't allowed to change your profile header.";
            $redirect = route('settings.index');
            return view('global/information', compact('message', 'redirect'));
        }

        if (session_check()) {
            $header = $_FILES['header'] ?? null;
            $redirect = route('settings.appearance.header');

            if ($header && $header['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = $this->handleUpload('header', $_FILES['header']);
                $message = $upload !== null ? $upload : "Changed your header!";
            } else {
                $this->deleteFile('header');
                $message = "Deleted your header!";
            }

            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/appearance/header');
    }

    public function userpage()
    {
        // Check permission
        if (!(
            ActiveUser::$user->page
            && ActiveUser::$user->permission(Site::CHANGE_USERPAGE)
        ) && !ActiveUser::$user->permission(Site::CREATE_USERPAGE)) {
            $message = "You aren't allowed to change your userpage.";
            $redirect = route('settings.index');
            return view('global/information', compact('message', 'redirect'));
        }

        $userpage = $_POST['userpage'] ?? null;
        $maxLength = config('user.page_max');

        if (session_check() && $userpage) {
            $redirect = route('settings.appearance.userpage');

            if (strlen($userpage) > $maxLength) {
                $message = 'Your userpage is too long, shorten it a little!';
            } else {
                DB::table('users')
                    ->where('user_id', ActiveUser::$user->id)
                    ->update([
                        'user_page' => $userpage,
                    ]);

                $message = 'Updated your userpage!';
            }

            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/appearance/userpage', compact('maxLength'));
    }

    public function signature()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::CHANGE_SIGNATURE)) {
            $message = "You aren't allowed to change your signature.";
            $redirect = route('settings.index');
            return view('global/information', compact('message', 'redirect'));
        }

        $signature = $_POST['signature'] ?? null;
        $maxLength = config('user.signature_max');

        if (session_check() && $signature) {
            $redirect = route('settings.appearance.signature');

            if (strlen($signature) > $maxLength) {
                $message = 'Your signature is too long, shorten it a little!';
            } else {
                DB::table('users')
                    ->where('user_id', ActiveUser::$user->id)
                    ->update([
                        'user_signature' => $signature,
                    ]);

                $message = 'Updated your signature!';
            }

            return view('global/information', compact('message', 'redirect'));
        }

        return view('settings/appearance/signature', compact('maxLength'));
    }
}
