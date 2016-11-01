<?php
/**
 * Holds the user permission handler.
 * @package Sakura
 */

namespace Sakura;

/**
 * User permission handler.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class UserPerms
{
    private $user = 0;
    private $ranks = [];
    private $cache = [];

    public function __construct(User $user)
    {
        $this->user = $user->id;
        $this->ranks = array_keys($user->ranks);
    }

    public function __get($name)
    {
        if (!array_key_exists($name, $this->cache)) {
            $column = 'perm_' . camel_to_snake($name);

            $result = array_column(DB::table('perms')
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
