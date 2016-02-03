<?php
/**
 * Holds the rank object class.
 * 
 * @package Sakura
 */

namespace Sakura;

use Sakura\Perms;
use Sakura\Perms\Site;

/**
 * Serves Rank data.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Rank
{
    /**
     * ID of the rank.
     * 
     * @var int
     */
    public $id = 0;

    /**
     * Name of the rank.
     * 
     * @var string
     */
    public $name = 'Rank';

    /**
     * Global hierarchy of the rank.
     * 
     * @var int
     */
    public $hierarchy = 0;

    /**
     * Text that should be append to the name to make it address multiple.
     * 
     * @var string
     */
    public $multiple = '';

    /**
     * The rank's username colour.
     * 
     * @var string
     */
    public $colour = 'inherit';

    /**
     * Description of the rank.
     * 
     * @var string
     */
    public $description = '';

    /**
     * User title of the rank.
     * 
     * @var string
     */
    public $title = '';

    /**
     * Indicates if this rank should be hidden.
     * 
     * @var bool
     */
    private $hidden = true;

    /**
     * Permission container.
     * 
     * @var Perms
     */
    private $permissions;

    /**
     * Instance cache container.
     * 
     * @var array
     */
    protected static $_rankCache = [];
    
    /**
     * Cached constructor.
     * 
     * @param int $rid ID of the rank.
     * @param bool $forceRefresh Force a cache refresh.
     * 
     * @return Rank The requested rank object.
     */
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

    /**
     * Constructor.
     * 
     * @param int $rid ID of the rank that should be constructed.
     */
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

    /**
     * Get the name of the rank.
     * 
     * @param bool $multi Should the multiple sense be appended?
     * 
     * @return string The rank's name.
     */
    public function name($multi = false)
    {
        return $this->name . ($multi ? $this->multiple : null);
    }

    /**
     * Indicates if the rank is hidden.
     * 
     * @return bool Hidden status.
     */
    public function hidden()
    {
        return $this->hidden || $this->permission(Site::DEACTIVATED) || $this->permission(Site::RESTRICTED);
    }

    /**
     * Check permissions.
     * 
     * @param int $flag Permission flag that should be checked.
     * 
     * @return bool Success indicator.
     */
    public function permission($flag)
    {
        // Set default permission value
        $perm = 0;

        // Bitwise OR it with the permissions for this forum
        $perm = $perm | $this->permissions->rank($this->id);
        
        return $this->permissions->check($flag, $perm);
    }
}
