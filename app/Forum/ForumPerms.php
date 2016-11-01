<?php
/**
 * Holds the forum permission handler.
 * @package Sakura
 */

namespace Sakura\Forum;

use Sakura\User;

/**
 * Forum permission handler.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class ForumPerms
{
    private $forum = [];
    private $user = 0;
    private $ranks = [];
    private $cache = [];

    public function __construct(Forum $forum, User $user)
    {
        //
    }

    public function __get($name)
    {
        if (!array_key_exists($name, $this->cache)) {
            $column = 'perm_' . camel_to_snake($name);

            $result = array_column(DB::table('forum_perms')
                ->whereIn('forum_id', $this->forum)
                ->where(function ($query) {
                    $query->whereIn('rank_id', $this->ranks)
                        ->orWhere('user_id', $this->user);
                })
                ->get([$column]), $column);

            $this->cache[$name] = !in_array('0', $result, true) && in_array('1', $result, true);
        }

        return $this->cache[$name];
    }
}
