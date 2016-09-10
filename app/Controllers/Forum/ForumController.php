<?php
/**
 * Holds the forum pages controllers.
 * @package Sakura
 */

namespace Sakura\Controllers\Forum;

use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Sakura\Config;
use Sakura\CurrentSession;
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
            if (!$forum->permission(ForumPerms::VIEW, CurrentSession::$user->id)) {
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
            if (!$forum->permission(ForumPerms::VIEW, CurrentSession::$user->id)) {
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

        // Redirect forum id 0 to the main page
        if ($forum->id === 0) {
            header("Location: " . route('forums.index'));
            return;
        }

        // Check if the forum exists
        if ($forum->id < 0
            || !$forum->permission(ForumPerms::VIEW, CurrentSession::$user->id)) {
            throw new HttpRouteNotFoundException();
        }

        // Check if the forum isn't a link
        if ($forum->type === 2) {
            header("Location: {$forum->link}");
            return;
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
        if (!session_check('s')) {
            throw new HttpMethodNotAllowedException();
        }

        $forum = new Forum($id);

        // Check if the forum exists
        if ($forum->id < 1
            || !$forum->permission(ForumPerms::VIEW, CurrentSession::$user->id)) {
            throw new HttpRouteNotFoundException();
        }

        $forum->trackUpdateAll(CurrentSession::$user->id);

        header("Location: " . route('forums.forum', $forum->id));
    }
}
