<?php
/**
 * Holds the global permissions handler.
 * @package Sakura
 */

namespace Sakura;

/**
 * Global permissions handler.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Perms
{
    /**
     * FORUM permission mode, used per forum.
     */
    const FORUM = 'forum_permissions\forum_perms';

    /**
     * The table containing the permissions.
     * @var string
     */
    protected $table = '';

    /**
     * The column containing the permissions.
     * @var string
     */
    protected $column = '';

    /**
     * Constructor.
     * @param string $mode
     */
    public function __construct($mode)
    {
        $this->mode($mode);
    }

    /**
     * Set a permission mode.
     * @param string $mode
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
     * @param int $flag
     * @param int $perm
     * @return bool
     */
    public function check($flag, $perm)
    {
        return ($flag & $perm) > 0;
    }

    /**
     * Get the permissions from a rank.
     * @param int $rid
     * @param array $conditions
     * @param int $perm
     * @return int
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
        $get = $get->first();

        // Check if anything was returned
        if ($get) {
            if (property_exists($get, $this->column) && $get->rank_id) {
                // Perform a bitwise OR
                $perm = $perm | bindec((string) $get->{$this->column});
            }
        }

        // Return the value
        return $perm;
    }

    /**
     * Get the permissions from a user.
     * @param int $uid
     * @param array $conditions
     * @param int $perm
     * @return int
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
        $get = $get->first();

        // Check if anything was returned
        if ($get) {
            if (property_exists($get, $this->column) && $get->user_id) {
                // Perform a bitwise OR
                $perm = $perm | bindec((string) $get->{$this->column});
            }
        }

        // Return the value
        return $perm;
    }
}
