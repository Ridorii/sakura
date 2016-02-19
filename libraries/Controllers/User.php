<?php
/**
 * Holds the user page controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\DB;
use Sakura\Rank;
use Sakura\Template;
use Sakura\User as UserContext;
use Sakura\Utils;

/**
 * Everything that is just for serving user data.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class User extends Controller
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
        $profile = UserContext::construct($id);

        // If the user id is zero check if there was a namechange
        if ($profile->id == 0) {
            // Fetch from username_history
            $check = DB::prepare('SELECT `user_id` FROM `{prefix}username_history` WHERE `username_old_clean` = :uname ORDER BY `change_id` DESC');
            $check->execute([
                'uname' => Utils::cleanString($id, true, true),
            ]);
            $check = $check->fetch();

            // Redirect if so
            if ($check) {
                Template::vars([
                    'page' => [
                        'message' => 'The user this profile belongs to changed their username, you are being redirected.',
                        'redirect' => (new \Sakura\Urls)->format('USER_PROFILE', [$check->user_id]),
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
                    'redirect' => (new \Sakura\Urls)->format('USER_PROFILE', [$profile->id]),
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
    public function members($rank = 0)
    {
        global $currentUser;

        // Check permission
        if (!$currentUser->permission(\Sakura\Perms\Site::VIEW_MEMBERLIST)) {
            return Template::render('global/restricted');
        }

        // Set parse variables
        Template::vars([
            'memberlist' => [
                'ranks' => ($_MEMBERLIST_RANKS = \Sakura\Users::getAllRanks()),
                'active' => ($_MEMBERLIST_ACTIVE = (array_key_exists($rank, $_MEMBERLIST_RANKS) ? $rank : 2)),
                'users' => Rank::construct($_MEMBERLIST_ACTIVE)->users(),
                'membersPerPage' => Config::get('members_per_page'),
            ]
        ]);

        // Render the template
        return Template::render('main/memberlist');
    }
}
