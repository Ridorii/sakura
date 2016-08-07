<?php
/**
 * Holds the forum pages controllers.
 * @package Sakura
 */

namespace Sakura\Controllers\Forum;

use Sakura\ActiveUser;
use Sakura\Config;
use Sakura\DB;
use Sakura\Forum\Forum;
use Sakura\Forum\Post;
use Sakura\Forum\Topic;
use Sakura\Perms\Forum as ForumPerms;
use Sakura\User;

/**
 * Forum page controllers.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class ForumController extends Controller
{
    /**
     * Renders the forum index
     * @return string
     */
    public function index()
    {
        // Get the most active topics
        $activeTopicsIds = DB::table('posts')
            ->where('forum_id', '!=', config('forum.trash'))
            ->groupBy('topic_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->get(['topic_id']);
        $activeTopics = [];

        // make this not disgusting
        while (list($_n, $_t) = each($activeTopicsIds)) {
            $topic = new Topic($_t->topic_id);
            $forum = new Forum($topic->forum);

            // Check if we have permission to view it
            if (!$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
                $fetch = DB::table('posts')
                    ->groupBy('topic_id')
                    ->orderByRaw('COUNT(*) DESC')
                    ->skip(11 + $_n)
                    ->take(1)
                    ->get(['topic_id']);

                if ($fetch) {
                    $activeTopicsIds[] = $fetch[0];
                }
                continue;
            }

            $activeTopics[$topic->id] = $topic;
        }

        // Get the latest posts
        $latestPostsIds = DB::table('posts')
            ->where('forum_id', '!=', config('forum.trash'))
            ->orderBy('post_id', 'desc')
            ->limit(10)
            ->get(['post_id']);
        $latestPosts = [];

        while (list($_n, $_p) = each($latestPostsIds)) {
            $post = new Post($_p->post_id);
            $forum = new Forum($post->forum);

            // Check if we have permission to view it
            if (!$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
                $fetch = DB::table('posts')
                    ->orderBy('post_id', 'desc')
                    ->skip(11 + $_n)
                    ->take(1)
                    ->get(['post_id']);

                if ($fetch) {
                    $latestPostsIds[] = $fetch[0];
                }
                continue;
            }

            $latestPosts[$post->id] = $post;
        }

        // Get the most active poster
        $activePosterId = DB::table('posts')
            ->where('forum_id', '!=', config('forum.trash'))
            ->where('post_time', '>', time() - (24 * 60 * 60))
            ->groupBy('poster_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(1)
            ->get(['poster_id']);
        $activePoster = User::construct(
            $activePosterId ? $activePosterId[0]->poster_id : 0
        );

        $forum = new Forum;

        return view('forum/index', compact('forum', 'activeTopics', 'latestPosts', 'activePoster'));
    }

    /**
     * Renders a forum.
     * @return string
     */
    public function forum($id = 0)
    {
        $forum = new Forum($id);

        $redirect = route('forums.index');
        $message = "The forum you tried to access does not exist!";

        // Redirect forum id 0 to the main page
        if ($forum->id === 0) {
            return header("Location: {$redirect}");
        }

        // Check if the forum exists
        if ($forum->id < 0
            || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
            return view('global/information', compact('message', 'redirect'));
        }

        // Check if the forum isn't a link
        if ($forum->type === 2) {
            $message = "The forum you tried to access is a link. You're being redirected.";
            $redirect = $forum->link;

            return view('global/information', compact('message', 'redirect'));
        }

        return view('forum/forum', compact('forum'));
    }

    /**
     * Marks an entire forum as read.
     * @param int $id
     * @return string
     */
    public function markRead($id = 0)
    {
        $redirect = route('forums.index');

        if (!session_check('s')) {
            $message = "Your session expired! Go back and try again.";
            return view('global/information', compact('message', 'redirect'));
        }

        $forum = new Forum($id);

        // Check if the forum exists
        if ($forum->id < 1
            || !$forum->permission(ForumPerms::VIEW, ActiveUser::$user->id)) {
            $message = "The forum you tried to access does not exist.";
            return view('global/information', compact('message', 'redirect'));
        }

        $forum->trackUpdateAll(ActiveUser::$user->id);

        $message = 'All topics have been marked as read!';
        $redirect = route('forums.forum', $forum->id);

        return view('global/information', compact('message', 'redirect'));
    }
}