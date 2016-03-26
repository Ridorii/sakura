<?php
/**
 * Holds the user page controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\DB;
use Sakura\Notification;
use Sakura\Perms\Site;
use Sakura\Rank;
use Sakura\Router;
use Sakura\Template;
use Sakura\User;
use Sakura\Utils;

/**
 * Everything that is just for serving user data.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class UserController extends Controller
{
    /**
     * Display the profile of a user.
     *
     * @param mixed $id The user ID.
     *
     * @return bool|string The profile page.
     */
    public function profile($id = 0)
    {
        global $currentUser;

        // Get the user's context
        $profile = User::construct($id);

        // If the user id is zero check if there was a namechange
        if ($profile->id == 0) {
            // Fetch from username_history
            $check = DB::table('username_history')
                ->where('username_old_clean', Utils::cleanString($id, true, true))
                ->orderBy('change_id', 'desc')
                ->get();

            // Redirect if so
            if ($check) {
                Template::vars([
                    'page' => [
                        'message' => 'The user this profile belongs to changed their username, you are being redirected.',
                        'redirect' => Router::route('user.profile', $check[0]->user_id),
                    ],
                ]);

                // Print page contents
                return Template::render('global/information');
            }
        }

        // Check if we're trying to restrict
        if (isset($_GET['restrict']) && $_GET['restrict'] == session_id() && $currentUser->permission(\Sakura\Perms\Manage::CAN_RESTRICT_USERS, \Sakura\Perms::MANAGE)) {
            // Check restricted status
            $restricted = $profile->permission(\Sakura\Perms\Site::RESTRICTED);

            if ($restricted) {
                $profile->removeRanks([Config::get('restricted_rank_id')]);
                $profile->addRanks([2]);
            } else {
                $profile->addRanks([Config::get('restricted_rank_id')]);
                $profile->removeRanks(array_keys($profile->ranks));
            }

            Template::vars([
                'page' => [
                    'message' => 'Toggled the restricted status of the user.',
                    'redirect' => Router::route('user.profile', $profile->id),
                ],
            ]);

            // Print page contents
            return Template::render('global/information');
        }

        // Set parse variables
        Template::vars([
            'profile' => $profile,
        ]);

        // Print page contents
        return Template::render('main/profile');
    }

    /**
     * Display the memberlist.
     *
     * @param int $rank Optional rank ID.
     *
     * @return bool|string The memberlist.
     */
    public function members($rank = null)
    {
        global $currentUser;

        // Check permission
        if (!$currentUser->permission(Site::VIEW_MEMBERLIST)) {
            return Template::render('global/restricted');
        }

        // Get all ranks
        $getRanks = DB::table('ranks')
            ->get(['rank_id']);

        // Define variable
        $ranks = [];

        // Add the empty rank
        $ranks[0] = Rank::construct(0);

        // Reorder shit
        foreach ($getRanks as $sortRank) {
            $ranks[$sortRank->rank_id] = Rank::construct($sortRank->rank_id);
        }

        // Get the active rank
        $rank = array_key_exists($rank, $ranks) ? $rank : ($rank ? 0 : 2);

        // Set parse variables
        Template::vars([
            'ranks' => $ranks,
            'rank' => $rank,
            'membersPerPage' => Config::get('members_per_page'),
        ]);

        // Render the template
        return Template::render('main/memberlist');
    }

    public function notifications()
    {
        // TODO: add friend on/offline messages
        global $currentUser;

        // Set json content type
        header('Content-Type: application/json; charset=utf-8');

        return json_encode(
            $currentUser->notifications(),
            JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING
        );
    }

    public function markNotification($id = 0)
    {
        global $currentUser;

        // Check permission
        if ($currentUser->permission(Site::DEACTIVATED)) {
            return '0';
        }

        // Create the notification object
        $alert = new Notification($id);

        // Verify that the currently authed user is the one this alert is for
        if ($alert->user !== $currentUser->id) {
            return '0';
        }

        // Toggle the read status and save
        $alert->toggleRead();
        $alert->save();

        return '1';
    }
}
