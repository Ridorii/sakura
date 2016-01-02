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
    // Rank data
    private $data = [
        'rank_id' => 0,
        'rank_name' => 'Rank',
        'rank_hierarchy' => 0,
        'rank_multiple' => null,
        'rank_hidden' => 1,
        'rank_colour' => '#444',
        'rank_description' => '',
        'rank_title' => '',
    ];
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
        $getRank = Database::fetch(
            'ranks',
            false,
            [
                'rank_id' => [$rid, '=', true],
            ]
        );

        // Check if the rank actually exists
        if (!empty($getRank)) {
            // If not assign as the fallback rank
            $this->data = $getRank;
        }

        // Init the permissions
        $this->permissions = new Perms(Perms::SITE);
    }

    // Get the rank id
    public function id()
    {
        return $this->data['rank_id'];
    }

    // Get the rank hierarchy
    public function hierarchy()
    {
        return $this->data['rank_hierarchy'];
    }

    // Get the rank name
    public function name($multi = false)
    {
        return $this->data['rank_name'] . ($multi ? $this->data['rank_multiple'] : null);
    }

    // Get the rank title
    public function title()
    {
        return $this->data['rank_title'];
    }

    // Get the rank description
    public function description()
    {
        return $this->data['rank_description'];
    }

    // Get the rank colour
    public function colour()
    {
        return $this->data['rank_colour'];
    }

    // Check if the rank is hidden
    public function hidden()
    {
        return $this->data['rank_hidden'] || $this->permission(Site::DEACTIVATED) || $this->permission(Site::RESTRICTED);
    }

    // Check if the rank has the proper permissions
    public function permission($flag)
    {
        // Set default permission value
        $perm = 0;

        // Bitwise OR it with the permissions for this forum
        $perm = $perm | $this->permissions->rank($this->id());
        
        return $this->permissions->check($flag, $perm);
    }
}
