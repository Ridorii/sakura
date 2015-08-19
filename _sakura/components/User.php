<?php
/*
 * Everything you'd ever need from a specific user
 */

namespace Sakura;

class User {

    // User data
    public $data        = [];
    public $ranks       = [];
    public $mainRank    = [];

    // Initialise the user object
    function __construct($id) {

        // Get the user database row
        $this->data = Database::fetch('users', false, ['id' => [$id, '=']]);

        // Check if the user actually exists
        if(empty($this->data)) {

            // If not assign as the fallback user
            $this->data = Users::$emptyUser;

        }

        // Decode the json in the userData column
        $this->data['userData'] = json_decode(!empty($this->data['userData']) ? $this->data['userData'] : '[]', true);

        // Decode the ranks json array
        $ranks = json_decode($this->data['ranks'], true);

        // Get the rows for all the ranks
        foreach($ranks as $rank) {

            // Store the database row in the array
            $this->ranks[$rank] = Database::fetch('ranks', false, ['id' => [$rank, '=']]);

        }

        // Check if ranks were set
        if(empty($this->ranks)) {

            // If not assign the fallback rank
            $this->ranks[0] = Users::$emptyRank;

        }

        // Assign the user's main rank to a special variable since we'll use it a lot
        $this->mainRank = $this->ranks[array_key_exists($this->data['rank_main'], $this->ranks) ? $this->data['rank_main'] : array_keys($this->ranks)[0]];

    }

    // Get the user's colour
    public function colour() {

        return empty($this->data['name_colour']) ? $this->mainRank['colour'] : $this->data['name_colour'];

    }

    // Get the user's title
    public function userTitle() {

        return empty($this->data['usertitle']) ? $this->mainRank['title'] : $this->data['usertitle'];

    }

    // Get the user's long and short country names
    public function country() {

        return [

            'long'  => Main::getCountryName($this->data['country']),
            'short' => $this->data['country']

        ];

    }

    // Get the user's profile fields
    public function profileFields() {

        // Get profile fields
        $profileFields = Database::fetch('profilefields');

        // If there's nothing just return null
        if(!count($profileFields)) {

            return;

        }

        // Once again if nothing was returned just return null
        if(empty($this->data['userData']['profileFields'])) {

            return;

        }

        // Create output array
        $profile = [];

        // Check if profile fields aren't fake
        foreach($profileFields as $field) {

            // Completely strip all special characters from the field name
            $fieldName = Main::cleanString($field['name'], true, true);

            // Check if the user has the current field set otherwise continue
            if(!array_key_exists($fieldName, $this->data['userData']['profileFields'])) {

                continue;

            }

            // Assign field to output with value
            $profile[$fieldName]            = array();
            $profile[$fieldName]['name']    = $field['name'];
            $profile[$fieldName]['value']   = $this->data['userData']['profileFields'][$fieldName];
            $profile[$fieldName]['islink']  = $field['islink'];

            // If the field is set to be a link add a value for that as well
            if($field['islink']) {

                $profile[$fieldName]['link'] = str_replace('{{ VAL }}', $this->data['userData']['profileFields'][$fieldName], $field['linkformat']);

            }

            // Check if we have additional options as well
            if($field['additional'] != null) {

                // Decode the json of the additional stuff
                $additional = json_decode($field['additional'], true);

                // Go over all additional forms
                foreach($additional as $subName => $subField) {

                    // Check if the user has the current field set otherwise continue
                    if(!array_key_exists($subName, $this->data['userData']['profileFields'])) {

                        continue;

                    }

                    // Assign field to output with value
                    $profile[$fieldName][$subName] = $this->data['userData']['profileFields'][$subName];

                }

            }

        }

        // Return appropiate profile data
        return $profile;

    }

}
