<?php
/**
 * Holds the file controller.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Sakura\Config;
use Sakura\CurrentSession;
use Sakura\DB;
use Sakura\Exceptions\FileException;
use Sakura\File;
use Sakura\Perms;
use Sakura\Perms\Manage;
use Sakura\Perms\Site;
use Sakura\Template;
use Sakura\User;

/**
 * File controller, handles user uploads like avatars.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class FileController extends Controller
{
    /**
     * Possible modes.
     */
    const MODES = [
        'avatar',
        'background',
        'header',
    ];

    /**
     * The base for serving a file.
     * @param string $data
     * @param string $mime
     * @param string $name
     * @return string
     */
    private function serve($data, $mime, $name)
    {
        header("Content-Disposition: inline; filename={$name}");
        header("Content-Type: {$mime}");
        return $data;
    }

    /**
     * Handles file uploads.
     * @param string $mode
     * @param array $file
     * @return array
     */
    private function upload($mode, $file, $user)
    {
        // Handle errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new FileException("Your file was too large!");

            case UPLOAD_ERR_PARTIAL:
                throw new FileException("The upload failed!");

            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
                throw new FileException("Wasn't able to save the file, contact a staff member!");

            case UPLOAD_ERR_EXTENSION:
            default:
                throw new FileException("Something prevented the file upload!");
        }

        // Get the temp filename
        $tmpName = $file['tmp_name'];

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
            throw new FileException("Please upload a valid image!");
        }

        // Check dimensions
        $maxWidth = config("file.{$mode}.max_width");
        $maxHeight = config("file.{$mode}.max_height");

        if ($meta[0] > $maxWidth
            || $meta[1] > $maxHeight) {
            throw new FileException("Your image can't be bigger than {$maxWidth}x{$maxHeight}" .
                ", yours was {$meta[0]}x{$meta[1]}!");
        }

        // Check file size
        $maxFileSize = config("file.{$mode}.max_file_size");

        if (filesize($tmpName) > $maxFileSize) {
            $maxSizeFmt = byte_symbol($maxFileSize);

            throw new FileException("Your image is not allowed to be larger than {$maxSizeFmt}!");
        }

        $userId = $user->id;
        $ext = image_type_to_extension($meta[2]);

        $filename = "{$mode}_{$userId}{$ext}";

        // Create the file
        $file = File::create(file_get_contents($tmpName), $filename, $user);

        // Delete the old file
        $this->delete($mode, $user);

        $column = "user_{$mode}";

        // Save new avatar
        DB::table('users')
            ->where('user_id', $user->id)
            ->update([
                $column => $file->id,
            ]);
    }

    /**
     * Deletes a file.
     * @param string $mode
     */
    public function delete($mode, $user)
    {
        $fileId = $user->{$mode};

        if ($fileId) {
            (new File($fileId))->delete();
        }
    }

    /**
     * Catchall serve.
     * @param string $method
     * @param array $params
     * @return string
     */
    public function __call($method, $params)
    {
        if (!in_array($method, self::MODES)) {
            throw new HttpRouteNotFoundException;
        }

        $user = User::construct($params[0] ?? 0);

        if (session_check()) {
            if (!CurrentSession::$user->permission(Manage::CHANGE_IMAGES, Perms::MANAGE)
                && ($user->id !== CurrentSession::$user->id
                    || !$user->permission(constant("Sakura\Perms\Site::CHANGE_" . strtoupper($method)))
                    || $user->permission(Site::DEACTIVATED)
                    || $user->permission(Site::RESTRICTED))
            ) {
                throw new HttpMethodNotAllowedException;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $error = null;

                try {
                    $this->upload($method, $_FILES['file'] ?? null, $user);
                } catch (FileException $e) {
                    $error = $e->getMessage();
                }

                return $this->json(compact('error'));
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                $this->delete($method, $user);
                return;
            }
        }

        $noFile = path('public/' . str_replace(
            '%tplname%',
            Template::$name,
            config("user.{$method}_none")
        ));
        $none = [
            'name' => basename($noFile),
            'data' => file_get_contents($noFile),
            'mime' => getimagesizefromstring($noFile)['mime'],
        ];

        if ($user->permission(Site::DEACTIVATED)
            || $user->permission(Site::RESTRICTED)
            || !$user->{$method}) {
            return $this->serve($none['data'], $none['mime'], $none['name']);
        }

        $serve = new File($user->{$method});

        if (!$serve->id) {
            return $this->serve($none['data'], $none['mime'], $none['name']);
        }

        return $this->serve($serve->data, $serve->mime, $serve->name);
    }
}
