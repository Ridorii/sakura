<?php
/*
 * Rank Class
 */

namespace Sakura;

class Rank
{
    // Rank data
    public $data = [];

    // Initialise the rank object
    public function __construct($rid)
    {

        // Get the rank database row
        $this->data = Database::fetch(
            'ranks',
            false,
            [
                'rank_id' => [$rid, '=', true],
            ]
        );

        // Check if the rank actually exists
        if (empty($this->data)) {
            // If not assign as the fallback rank
            $this->data = Users::$emptyRank;
        }

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

        return $this->data['rank_hidden'] || $this->checkPermission('SITE', 'DEACTIVATED') || $this->checkPermission('SITE', 'RESTRICTED');

    }

    // Check if the rank has the proper permissions
    public function checkPermission($layer, $action)
    {

        return Permissions::check($layer, $action, [$this->id()], 2);

    }
}
