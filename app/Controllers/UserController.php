<?php
/**
 * Holds the user page controllers.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
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
        if ($profile->id === 0) {
            // Fetch from username_history
            $check = DB::table('username_history')
                ->where('username_old_clean', clean_string($id, true, true))
                ->orderBy('change_id', 'desc')
                ->first();

            // Redirect if so
            if ($check) {
                redirect(route('user.profile', $check->user_id));
                return;
            }
        }

        return view('user/profile', compact('profile'));
    }

    /**
     * Last listened to.
     * @param int $id
     * @throws HttpRouteNotFoundException
     * @return string
     */
    public function nowPlaying($id)
    {
        $user = User::construct($id);

        if ($user->id === 0) {
            throw new HttpRouteNotFoundException;
        }

        $user->updateLastTrack();

        $artist_url = 'http://last.fm/music/' . urlencode($user->musicArtist);
        $track_url = $artist_url . '/_/' . urlencode($user->musicTrack);

        return $this->json([
            'track' => $user->musicTrack,
            'track_url' => $track_url,
            'artist' => $user->musicArtist,
            'artist_url' => $artist_url,
            'listening' => $user->musicListening,
        ]);
    }

    /**
     * Display the memberlist.
     * @param int $rank
     * @throws HttpMethodNotAllowedException
     * @return string
     */
    public function members($rank = null)
    {
        // Check permission
        if (!CurrentSession::$user->permission(Site::VIEW_MEMBERLIST)) {
            throw new HttpMethodNotAllowedException;
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

        return view('user/members', compact('ranks', 'rank'));
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
