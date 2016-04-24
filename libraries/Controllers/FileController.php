<?php
/**
 * Holds the file controller.
 *
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
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class FileController extends Controller
{
    /**
     * The base for serving a file.
     *
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
     *
     * @return string
     */
    public function avatar($id = 0)
    {
        $noAvatar = ROOT . str_replace(
            '{{ TPL }}',
            Template::$name,
            Config::get('no_avatar_img')
        );
        $none = [
            'name' => basename($noAvatar),
            'data' => file_get_contents($noAvatar),
            'mime' => getimagesizefromstring($noAvatar)['mime'],
        ];

        $deactivePath = ROOT . str_replace(
            '{{ TPL }}',
            Template::$name,
            Config::get('deactivated_avatar_img')
        );
        $deactive = [
            'name' => basename($deactivePath),
            'data' => file_get_contents($deactivePath),
            'mime' => getimagesizefromstring($deactivePath)['mime'],
        ];

        $bannedPath = ROOT . str_replace(
            '{{ TPL }}',
            Template::$name,
            Config::get('banned_avatar_img')
        );
        $banned = [
            'name' => basename($bannedPath),
            'data' => file_get_contents($bannedPath),
            'mime' => getimagesizefromstring($bannedPath)['mime'],
        ];

        $user = User::construct($id);

        if ($user->permission(Site::DEACTIVATED)) {
            return $this->serve($deactive['data'], $deactive['mime'], $deactive['name']);
        }

        if ($user->permission(Site::RESTRICTED)) {
            return $this->serve($banned['data'], $banned['mime'], $banned['name']);
        }

        if (!$user->avatar) {
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
     *
     * @return string
     */
    public function background($id = 0)
    {
        $noBg = ROOT . Config::get('no_background_img');
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
     *
     * @return string
     */
    public function header($id = 0)
    {
        $noHeader = ROOT . Config::get('no_header_img');
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
