<?php
/**
 * Hold the controller for chat related pages.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Chat\LinkInfo;
use Sakura\Chat\Settings;
use Sakura\Chat\URLResolver;
use Sakura\DB;
use Sakura\Perms;
use Sakura\Perms\Manage;
use Sakura\Perms\Site;
use Sakura\Session;
use Sakura\User;

/**
 * Chat related controller.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class ChatController extends Controller
{
    /**
     * Middlewares!
     * @var array
     */
    protected $middleware = [
        'EnableCORS',
    ];

    /**
     * Redirects the user to the chat client.
     */
    public function redirect()
    {
        header('Location: ' . config('chat.webclient'));
    }

    /**
     * Serves the settings for a Sakurako chat.
     * @return string
     */
    public function settings()
    {
        $settings = new Settings;
        $settings->loadStandard();

        $emotes = DB::table('emoticons')
            ->get();

        foreach ($emotes as $emote) {
            $settings->addEmoticon([$emote->emote_string], $emote->emote_path, 1, true);
        }

        return $this->json($settings);
    }

    /**
     * Resolves urls.
     * @return string
     */
    public function resolve()
    {
        $data = json_decode(file_get_contents('php://input'));
        $info = new LinkInfo;

        if (json_last_error() === JSON_ERROR_NONE) {
            $info = URLResolver::resolve(
                $data->Protocol ?? null,
                $data->Slashes ?? null,
                $data->Authority ?? null,
                $data->Host ?? null,
                $data->Port ?? null,
                $data->Path ?? null,
                $data->Query ?? null,
                $data->Hash ?? null
            );
        }

        return $info;
    }

    /**
     * Handles the authentication for a chat server.
     * @return string
     */
    public function auth()
    {
        return;
    }

    /**
     * Legacy auth, for SockLegacy. Remove when the old chat server finally dies.
     * @return string
     */
    public function authLegacy()
    {
        $user = User::construct($_GET['arg1'] ?? null);
        $session = new Session($_GET['arg2'] ?? null);

        if ($session->validate($user->id)
            && !$user->permission(Site::DEACTIVATED)
            && !$user->permission(Site::RESTRICTED)) {
            $hierarchy = $user->hierarchy();
            $moderator = $user->permission(Manage::USE_MANAGE, Perms::MANAGE) ? 1 : 0;
            $changeName = $user->permission(Site::CHANGE_USERNAME) ? 1 : 0;
            $createChans = $user->permission(Site::MULTIPLE_GROUPS) ? 2 : (
                $user->permission(Site::CREATE_GROUP) ? 1 : 0
            );

            // The single 0 in here is used to determine log access, which isn't supported by sakurako anymore since it
            // required direct database access and the chat is databaseless now.
            return "yes{$user->id}\n{$user->username}\n{$user->colour}\n{$hierarchy}\f{$moderator}\f0\f{$changeName}\f{$createChans}\f";
        }

        return "no";
    }
}
