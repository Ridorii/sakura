<?php
/**
 * Holds the global permissions handler.
 *
 * @package Sakura
 */

namespace Sakura;

/**
 * Global permissions handler.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Perms
{
    /**
     * SITE permission mode, used for general permissions.
     */
    const SITE = 'permissions\permissions_site';

    /**
     * MANAGE permission mode, used for site management actions.
     */
    const MANAGE = 'permissions\permissions_manage';

    /**
     * FORUM permission mode, used per forum.
     */
    const FORUM = 'forum_permissions\forum_perms';

    /**
     * The table containing the permissions.
     *
     * @var string
     */
    protected $table = '';

    /**
     * The column containing the permissions.
     *
     * @var string
     */
    protected $column = '';

    /**
     * Constructor.
     *
     * @param string $mode One of the modes above.
     */
    public function __construct($mode)
    {
        $this->mode($mode);
    }

    /**
     * Set a permission mode.
     *
     * @param string $mode One of the modes above.
     */
    public function mode($mode)
    {
        // Split the mode variable
        $mode = explode('\\', $mode);

        // Assign $table, $column and $selectors
        $this->table = $mode[0];
        $this->column = $mode[1];
    }

    /**
     * Compare a permission flag.
     *
     * @param int $flag The permission flag.
     * @param int $perm The permissions of the user.
     *
     * @return bool Success indicator.
     */
    public function check($flag, $perm)
    {
        return ($flag & $perm) > 0;
    }

    /**
     * Get the permissions from a rank.
     *
     * @param int $rid The ID of the rank in question.
     * @param array $conditions Additional SQL conditions.
     * @param int $perm A permission flag to append to.
     *
     * @return int A permission flag.
     */
    public function rank($rid, $conditions = [], $perm = 0)
    {
        // Build statement
        $get = DB::table($this->table)
            ->where('rank_id', $rid)
            ->where('user_id', 0);

        // Append additional conditionals (DBWrapper v1 format, except OR is ignored)
        foreach ($conditions as $column => $value) {
            $get->where($column, $value[1], $value[0]);
        }

        // Fetch from the db
        $get = $get->get();

        // Check if anything was returned
        if ($get) {
            $get = get_object_vars($get[0]);

            if (array_key_exists($this->column, $get) && $get['rank_id']) {
                // Perform a bitwise OR
                $perm = $perm | bindec((string) $get[$this->column]);
            }
        }

        // Return the value
        return $perm;
    }

    /**
     * Get the permissions from a user.
     *
     * @param int $uid The ID of the user in question.
     * @param array $conditions Additional SQL conditions.
     * @param int $perm A permission flag to append to.
     *
     * @return int A permission flag.
     */
    public function user($uid, $conditions = [], $perm = 0)
    {
        // Create a user object
        $user = User::construct($uid);

        // Get data from ranks
        foreach (array_keys($user->ranks) as $rank) {
            $perm = $perm | $this->rank($rank, $conditions, $perm);
        }

        // Build statement
        $get = DB::table($this->table)
            ->where('rank_id', 0)
            ->where('user_id', $uid);

        // Append additional conditionals (DBWrapper v1 format, except OR is ignored)
        foreach ($conditions as $column => $value) {
            $get->where($column, $value[1], $value[0]);
        }

        // Fetch from the db
        $get = $get->get();

        // Check if anything was returned
        if ($get) {
            $get = get_object_vars($get[0]);

            if (array_key_exists($this->column, $get) && $get['user_id']) {
                // Perform a bitwise OR
                $perm = $perm | bindec((string) $get[$this->column]);
            }
        }

        // Return the value
        return $perm;
    }
}