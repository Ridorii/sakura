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
    const SITE = 'permissions\permissions_site\user_id,rank_id';
    const MANAGE = 'permissions\permissions_manage\user_id,rank_id';
    const FORUM = 'forum_permissions\forum_perms\forum_id,user_id,rank_id';

    // Variables
    protected $table = '';
    protected $column = '';
    protected $selectors = [];

    // Constructor
    public function __construct($mode) {
        // Split the mode variable
        $mode = explode('\\', $mode);

        // Assign $table, $column and $selectors
        $this->table = $mode[0];
        $this->column = $mode[1];
        $this->selectors = explode(',', $mode[2]);
    }

    // Checking permissions
    public function check($flag, $perm) {
        return ($flag & bindec($perm)) > 0;
    }

    // Getting rank permissions
    public function get($select) {
        // Combine $select into $selectors
        $select = array_slice($select, 0, count($this->selectors));
        $select = array_combine($this->selectors, $select);
    }
}
