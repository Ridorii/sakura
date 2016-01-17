<?php
/*
 * Rank Class
 */

namespace Sakura;

use Sakura\Perms;
use Sakura\Perms\Site;

/**
 * Class Rank
 * @package Sakura
 */
class Rank
{
    // Variables
    public $id = 0;
    public $name = 'Rank';
    public $hierarchy = 0;
    public $multiple = '';
    public $colour = 'inherit';
    public $description = '';
    public $title = '';
    private $hidden = true;
    private $permissions;
    protected static $_rankCache = [];
    
    // Static initialiser
    public static function construct($rid, $forceRefresh = false)
    {
        // Check if a rank object isn't present in cache
        if ($forceRefresh || !array_key_exists($rid, self::$_rankCache)) {
            // If not create a new object and cache it
            self::$_rankCache[$rid] = new Rank($rid);
        }

        // Return the cached object
        return self::$_rankCache[$rid];
    }

    // Initialise the rank object
    private function __construct($rid)
    {

        // Get the rank database row
        $rankRow = Database::fetch(
            'ranks',
            false,
            [
                'rank_id' => [$rid, '=', true],
            ]
        );

        // Check if the rank actually exists
        if ($rankRow) {
            $this->id = $rankRow['rank_id'];
            $this->name = $rankRow['rank_name'];
            $this->hierarchy = $rankRow['rank_hierarchy'];
            $this->multiple = $rankRow['rank_multiple'];
            $this->hidden = (bool) $rankRow['rank_hidden'];
            $this->colour = $rankRow['rank_colour'];
            $this->description = $rankRow['rank_description'];
            $this->title = $rankRow['rank_title'];
        }

        // Init the permissions
        $this->permissions = new Perms(Perms::SITE);
    }

    // Get the rank name
    public function name($multi = false)
    {
        return $this->name . ($multi ? $this->multiple : null);
    }

    // Check if the rank is hidden
    public function hidden()
    {
        return $this->hidden || $this->permission(Site::DEACTIVATED) || $this->permission(Site::RESTRICTED);
    }

    // Check if the rank has the proper permissions
    public function permission($flag)
    {
        // Set default permission value
        $perm = 0;

        // Bitwise OR it with the permissions for this forum
        $perm = $perm | $this->permissions->rank($this->id);
        
        return $this->permissions->check($flag, $perm);
    }
}
