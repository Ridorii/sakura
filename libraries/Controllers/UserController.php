<?php
/**
 * Holds the user page controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\DB;
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
                $message = "This user changed their username! Redirecting you to their new profile.";
                $redirect = Router::route('user.profile', $check[0]->user_id);

                Template::vars(compact('message', 'redirect'));

                // Print page contents
                return Template::render('global/information');
            }
        }

        // Set parse variables
        Template::vars(compact('profile'));

        // Print page contents
        return Template::render('user/profile');
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

        // Get members per page
        $membersPerPage = Config::get('members_per_page');

        // Set parse variables
        Template::vars(compact('ranks', 'rank', 'membersPerPage'));

        // Render the template
        return Template::render('user/members');
    }
}
