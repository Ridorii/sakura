<?php
/**
 * Holds the forum permission handler.
 * @package Sakura
 */

namespace Sakura\Forum;

use Sakura\DB;
use Sakura\User;

/**
 * Forum permission handler.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class ForumPerms
{
    private static $table = 'forum_perms';
    private $forums = [];
    private $user = 0;
    private $ranks = [];
    private $permCache = [];
    private $validCache = [];

    public function __construct(Forum $forum, User $user)
    {
        $this->forums = [0, $forum->id, $forum->category]; // make this inherit everything before release
        $this->user = $user->id;
        $this->ranks = array_keys($user->ranks);
    }

    public function __get($name)
    {
        return $this->check($name);
    }

    public function __isset($name)
    {
        return $this->valid($name);
    }

    public function valid($name)
    {
        if (!array_key_exists($name, $this->validCache)) {
            $column = 'perm_' . camel_to_snake($name);
            $this->validCache[$name] =  DB::getSchemaBuilder()->hasColumn(static::$table, $column);
        }

        return $this->validCache[$name];
    }

    public function check($name)
    {
        if (!array_key_exists($name, $this->permCache)) {
            $column = 'perm_' . camel_to_snake($name);

            $result = array_column(DB::table(static::$table)
                ->whereIn('forum_id', $this->forums)
                ->where(function ($query) {
                    $query->whereIn('rank_id', $this->ranks)
                        ->orWhere('user_id', $this->user);
                })
                ->get([$column]), $column);

            $this->permCache[$name] = !in_array('0', $result, true) && in_array('1', $result, true);
        }

        return $this->permCache[$name];
    }
}
