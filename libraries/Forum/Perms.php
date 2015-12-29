<?php
/*
 * Forum specific permissions class
 */

namespace Sakura\Forum;

use Sakura\Database;

/**
 * Class Perms
 * @package Sakura
 */
class Perms
{
    // Permissions
    const VIEW = 1;
    const REPLY = 2;
    const CREATE_THREADS = 4;
    const EDIT_OWN = 8;
    const DELETE_OWN = 16;
    const STICKY = 32;
    const ANNOUNCEMENT = 64;
    const EDIT_ANY = 128;
    const DELETE_ANY = 256;

    // Permission row
    private $perms = 0;

    // Constructor
    public function __construct($forumId, $rankId = 0, $userId = 0) {
        // Get permissions
        $this->perms = $this->getPerms($forumId, $rankId, $userId);
    }

    // Get permissions
    private function getPerms($forumId, $rankId = 0, $userId = 0, $perms = 0) {
        // Attempt to get the forum's row from the db
        $forumRows = Database::fetch('forums', true, ['forum_id' => [$forumId, '=']]);

        // Check if anything was returned, otherwise just stop
        if (!$forumRows) {
            return $perms;
        }

        // Get the data from the permissions table
        $forumPerms = Database::fetch('forum_permissions', false, [
            'forum_id' => [$forumId, '='],
            'rank_id' => [$rankId, '='],
            'user_id' => [$userId, '='],
        ]);

        // Perform a bitwise OR if perms is already set to something
        if ($perms) {
            $perms = $perms | $forumPerms['forum_perms'];
        } else {
            $perms = $forumPerms['forum_perms'];
        }

        // Perform this again if this forum has a parent
        if ($forumRows['forum_category']) {
            $perms = $this->getPerms($forumId, $rankId, $userId, $perms);
        }

        // Return new value
        return $perms;
    }

    // Check permission
    public function check($perm) {
        return bindec($this->perms) & $perm === true;
    }
}
