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
        $stmt = "SELECT * FROM `{prefix}{$this->table}` WHERE `rank_id` = :rank AND `user_id` = 0";

        // Append additional conditionals (DBWrapper v1 format, except OR is ignored)
        foreach ($conditions as $column => $value) {
            $stmt .= " AND `{$column}` {$value[1]} :_retarded_{$column}";
        }

        // Prepare the statement
        $get = DBv2::prepare($stmt);

        // Bind rank
        $get->bindParam('rank', $rid);

        // Bind additionals
        foreach ($conditions as $column => $value) {
            $get->bindParam("_retarded_{$column}", $value[0]);
        }

        // Execute!
        $get->execute();

        // Fetch from the db
        $get = $get->fetch(\PDO::FETCH_ASSOC);

        // Check if anything was returned
        if ($get && array_key_exists($this->column, $get) && $get['rank_id']) {
            // Perform a bitwise OR
            $perm = $perm | bindec((string) $get[$this->column]);
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
        $stmt = "SELECT * FROM `{prefix}{$this->table}` WHERE `rank_id` = 0 AND `user_id` = :user";

        // Append additional conditionals (DBWrapper v1 format, except OR is ignored)
        foreach ($conditions as $column => $value) {
            $stmt .= " AND `{$column}` {$value[1]} :_retarded_{$column}";
        }

        // Prepare the statement
        $get = DBv2::prepare($stmt);

        // Bind rank
        $get->bindParam('user', $uid);

        // Bind additionals
        foreach ($conditions as $column => $value) {
            $get->bindParam("_retarded_{$column}", $value[0]);
        }

        // Execute!
        $get->execute();

        // Fetch from the db
        $get = $get->fetch(\PDO::FETCH_ASSOC);
        
        // Check if anything was returned
        if ($get && array_key_exists($this->column, $get) && $get['user_id']) {
            // Perform a bitwise OR
            $perm = $perm | bindec((string) $get[$this->column]);
        }

        // Return the value
        return $perm;
    }
}
