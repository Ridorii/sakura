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
     * @param int $rankId ID of the rank that should be constructed.
     */
    private function __construct($rankId)
    {

        // Get the rank database row
        $rankRow = DB::table('ranks')
            ->where('rank_id', $rankId)
            ->get();

        // Check if the rank actually exists
        if ($rankRow) {
            $rankRow = $rankRow[0];
            $this->id = $rankRow->rank_id;
            $this->name = $rankRow->rank_name;
            $this->hierarchy = $rankRow->rank_hierarchy;
            $this->multiple = $rankRow->rank_multiple;
            $this->hidden = (bool) $rankRow->rank_hidden;
            $this->colour = $rankRow->rank_colour;
            $this->description = $rankRow->rank_description;
            $this->title = $rankRow->rank_title;
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

    /**
     * Returns all users that are part of this rank.
     *
     * @param bool $justIds Makes this function only return the user ids when set to a positive value.
     *
     * @return array Either just the user IDs of the users or with objects.
     */
    public function users($justIds = false)
    {
        // Fetch all users part of this rank
        $get = DB::table('user_ranks')
            ->where('rank_id', $this->id)
            ->get(['user_id']);

        // Filter the user ids into one array
        $userIds = array_column($get, 'user_id');

        // Just return that if we were asked for just the ids
        if ($justIds) {
            return $userIds;
        }

        // Create the storage array
        $users = [];

        // Create User objects and store
        foreach ($userIds as $id) {
            $users[$id] = User::construct($id);
        }

        // Return the array
        return $users;
    }
}
