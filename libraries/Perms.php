<?php
/*
 * Permission Handler
 */

namespace Sakura;

/**
 * Class Perms
 * @package Sakura
 */
class Perms
{
    // Modes
    const SITE = 'permissions\permissions_site';
    const MANAGE = 'permissions\permissions_manage';
    const FORUM = 'forum_permissions\forum_perms';

    // Variables
    protected $table = '';
    protected $column = '';
    
    // Constructor
    public function __construct($mode)
    {
        $this->mode($mode);
    }

    // Change the mode
    public function mode($mode)
    {
        // Split the mode variable
        $mode = explode('\\', $mode);

        // Assign $table, $column and $selectors
        $this->table = $mode[0];
        $this->column = $mode[1];
    }

    // Checking permissions
    public function check($flag, $perm)
    {
        return ($flag & $perm) > 0;
    }

    // Getting a rank's permissions
    public function rank($rid, $conditions = [], $perm = 0)
    {
        // Merge rank id and additional conditions
        $conditions = array_merge(['rank_id' => [$rid, '='], 'user_id' => [0, '=']], $conditions);

        // Fetch from the db
        $get = Database::fetch($this->table, false, $conditions);

        // Check if anything was returned
        if ($get && array_key_exists($this->column, $get) && $get['rank_id']) {
            // Perform a bitwise OR
            $perm = $perm | bindec((string) $get[$this->column]);
        }

        // Return the value
        return $perm;
    }

    // Getting a user's permissions
    public function user($uid, $conditions = [], $perm = 0)
    {
        // Create a user object
        $user = User::construct($uid);

        // Get data from ranks
        foreach (array_keys($user->ranks) as $rank) {
            $perm = $perm | $this->rank($rank, $conditions, $perm);
        }

        // Merge user id and additional conditions
        $conditions = array_merge(['user_id' => [$uid, '='], 'rank_id' => [0, '=']], $conditions);

        // Fetch from the db
        $get = Database::fetch($this->table, false, $conditions);
        
        // Check if anything was returned
        if ($get && array_key_exists($this->column, $get) && $get['user_id']) {
            // Perform a bitwise OR
            $perm = $perm | bindec((string) $get[$this->column]);
        }

        // Return the value
        return $perm;
    }
}
