<?php
/**
 * Holds the file controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\User;
use Sakura\File;
use Sakura\Perms\Site;

/**
 * File controller, handles user uploads like avatars.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Files extends Controller
{
    private function serveImage($data, $mime, $name)
    {
        // Add original filename
        header('Content-Disposition: inline; filename="' . $name . '"');

        // Set content type
        header('Content-Type: ' . $mime);

        // Return image data
        return $data;
    }

    public function avatar($id = 0)
    {
        global $templateName;

        $noAvatar = ROOT . str_replace(
            '{{ TPL }}',
            $templateName,
            Config::get('no_avatar_img')
        );
        $none = [
            'name' => basename($noAvatar),
            'data' => file_get_contents($noAvatar),
            'mime' => getimagesizefromstring($noAvatar)['mime'],
        ];

        $deactivePath = ROOT . str_replace(
            '{{ TPL }}',
            $templateName,
            Config::get('deactivated_avatar_img')
        );
        $deactive = [
            'name' => basename($deactivePath),
            'data' => file_get_contents($deactivePath),
            'mime' => getimagesizefromstring($deactivePath)['mime'],
        ];

        $bannedPath = ROOT . str_replace(
            '{{ TPL }}',
            $templateName,
            Config::get('banned_avatar_img')
        );
        $banned = [
            'name' => basename($bannedPath),
            'data' => file_get_contents($bannedPath),
            'mime' => getimagesizefromstring($bannedPath)['mime'],
        ];

        $user = User::construct($id);

        if ($user->permission(Site::DEACTIVATED)) {
            return $this->serveImage($deactive['data'], $deactive['mime'], $deactive['name']);
        }

        if ($user->checkBan() || $user->permission(Site::RESTRICTED)) {
            return $this->serveImage($banned['data'], $banned['mime'], $banned['name']);
        }

        if (!$user->avatar) {
            return $this->serveImage($none['data'], $none['mime'], $none['name']);
        }

        $serve = new File($user->avatar);

        if (!$serve->id) {
            return $this->serveImage($none['data'], $none['mime'], $none['name']);
        }

        return $this->serveImage($serve->data, $serve->mime, $serve->name);
    }

    public function background($id = 0)
    {
        global $templateName;

        $noBg = ROOT . Config::get('no_background_img');
        $none = [
            'name' => basename($noBg),
            'data' => file_get_contents($noBg),
            'mime' => getimagesizefromstring($noBg)['mime'],
        ];

        if (!$id) {
            return $this->serveImage($none['data'], $none['mime'], $none['name']);
        }

        $user = User::construct($id);

        if ($user->permission(Site::DEACTIVATED) || $user->checkBan() || $user->permission(Site::RESTRICTED) || !$user->background) {
            return $this->serveImage($none['data'], $none['mime'], $none['name']);
        }

        $serve = new File($user->background);

        if (!$serve->id) {
            return $this->serveImage($none['data'], $none['mime'], $none['name']);
        }

        return $this->serveImage($serve->data, $serve->mime, $serve->name);
    }

    public function header($id = 0)
    {
        global $templateName;

        $noHeader = ROOT . Config::get('no_header_img');
        $none = [
            'name' => basename($noHeader),
            'data' => file_get_contents($noHeader),
            'mime' => getimagesizefromstring($noHeader)['mime'],
        ];

        if (!$id) {
            return $this->serveImage($none['data'], $none['mime'], $none['name']);
        }

        $user = User::construct($id);

        if ($user->permission(Site::DEACTIVATED) || $user->checkBan() || $user->permission(Site::RESTRICTED) || !$user->header) {
            return $this->serveImage($none['data'], $none['mime'], $none['name']);
        }

        $serve = new File($user->header);

        if (!$serve->id) {
            return $this->serveImage($none['data'], $none['mime'], $none['name']);
        }

        return $this->serveImage($serve->data, $serve->mime, $serve->name);
    }
}
