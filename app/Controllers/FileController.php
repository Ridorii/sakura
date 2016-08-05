<?php
/**
 * Holds the file controller.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\File;
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
     * The base for serving a file.
     * @param string $data
     * @param string $mime
     * @param string $name
     * @return string
     */
    private function serve($data, $mime, $name)
    {
        // Add original filename
        header("Content-Disposition: inline; filename={$name}");

        // Set content type
        header("Content-Type: {$mime}");

        // Return image data
        return $data;
    }

    /**
     * Attempt to get an avatar.
     * @param int $id
     * @return string
     */
    public function avatar($id = 0)
    {
        $noAvatar = ROOT . 'public/' . str_replace(
            '%tplname%',
            Template::$name,
            config('user.avatar_none')
        );
        $none = [
            'name' => basename($noAvatar),
            'data' => file_get_contents($noAvatar),
            'mime' => getimagesizefromstring($noAvatar)['mime'],
        ];

        $bannedPath = ROOT . 'public/' . str_replace(
            '%tplname%',
            Template::$name,
            config('user.avatar_ban')
        );
        $banned = [
            'name' => basename($bannedPath),
            'data' => file_get_contents($bannedPath),
            'mime' => getimagesizefromstring($bannedPath)['mime'],
        ];

        $user = User::construct($id);

        if ($user->permission(Site::RESTRICTED)) {
            return $this->serve($banned['data'], $banned['mime'], $banned['name']);
        }

        if ($user->id < 1 || !$user->avatar || $user->permission(Site::DEACTIVATED)) {
            return $this->serve($none['data'], $none['mime'], $none['name']);
        }

        $serve = new File($user->avatar);

        if (!$serve->id) {
            return $this->serve($none['data'], $none['mime'], $none['name']);
        }

        return $this->serve($serve->data, $serve->mime, $serve->name);
    }

    /**
     * Attempt to get a background.
     * @param int $id
     * @return string
     */
    public function background($id = 0)
    {
        $noBg = ROOT . "public/images/pixel.png";
        $none = [
            'name' => basename($noBg),
            'data' => file_get_contents($noBg),
            'mime' => getimagesizefromstring($noBg)['mime'],
        ];

        if (!$id) {
            return $this->serve($none['data'], $none['mime'], $none['name']);
        }

        $user = User::construct($id);

        if ($user->permission(Site::DEACTIVATED)
            || $user->permission(Site::RESTRICTED)
            || !$user->background) {
            return $this->serve($none['data'], $none['mime'], $none['name']);
        }

        $serve = new File($user->background);

        if (!$serve->id) {
            return $this->serve($none['data'], $none['mime'], $none['name']);
        }

        return $this->serve($serve->data, $serve->mime, $serve->name);
    }

    /**
     * Attempt to get a profile header.
     * @param int $id
     * @return string
     */
    public function header($id = 0)
    {
        $noHeader = ROOT . "public/images/pixel.png";
        $none = [
            'name' => basename($noHeader),
            'data' => file_get_contents($noHeader),
            'mime' => getimagesizefromstring($noHeader)['mime'],
        ];

        if (!$id) {
            return $this->serve($none['data'], $none['mime'], $none['name']);
        }

        $user = User::construct($id);

        if ($user->permission(Site::DEACTIVATED)
            || $user->permission(Site::RESTRICTED)
            || !$user->header) {
            return $this->serve($none['data'], $none['mime'], $none['name']);
        }

        $serve = new File($user->header);

        if (!$serve->id) {
            return $this->serve($none['data'], $none['mime'], $none['name']);
        }

        return $this->serve($serve->data, $serve->mime, $serve->name);
    }
}
