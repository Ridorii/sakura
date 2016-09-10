<?php
/**
 * Holds the user page controllers.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\CurrentSession;
use Sakura\DB;
use Sakura\Perms\Site;
use Sakura\Rank;
use Sakura\User;

/**
 * Everything that is just for serving user data.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class UserController extends Controller
{
    /**
     * Display the profile of a user.
     * @param int $id
     * @return string
     */
    public function profile($id = 0)
    {
        // Get the user's context
        $profile = User::construct($id);

        // If the user id is zero check if there was a namechange
        if ($profile->id == 0) {
            // Fetch from username_history
            $check = DB::table('username_history')
                ->where('username_old_clean', clean_string($id, true, true))
                ->orderBy('change_id', 'desc')
                ->first();

            // Redirect if so
            if ($check) {
                $message = "This user changed their username! Redirecting you to their new profile.";
                $redirect = route('user.profile', $check->user_id);
                return view('global/information', compact('message', 'redirect'));
            }
        }

        return view((isset($_GET['new']) ? '@aitemu/' : '') . 'user/profile', compact('profile'));
    }

    /**
     * Display the memberlist.
     * @param int $rank
     * @return string
     */
    public function members($rank = null)
    {
        // Check permission
        if (!CurrentSession::$user->permission(Site::VIEW_MEMBERLIST)) {
            return view('global/restricted');
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
        $rank = array_key_exists($rank, $ranks) ? $rank : ($rank ? 0 : intval(config("rank.regular")));

        // Get members per page
        $membersPerPage = 30;

        return view('user/members', compact('ranks', 'rank', 'membersPerPage'));
    }

    /**
     * Report a user.
     * @param int $id
     */
    public function report($id = 0)
    {
        return view('user/report');
    }
}
