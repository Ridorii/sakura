<?php
/*
 * Everything you'd ever need from a specific user
 */

namespace Sakura;

class User
{
    // User data
    public $data = [];
    public $ranks = [];
    public $mainRank = [];

    // Initialise the user object
    public function __construct($uid)
    {

        // Get the user database row
        $this->data = Database::fetch(
            'users',
            false,
            [
                'id' => [$uid, '=', true],
                'username_clean' => [Main::cleanString($uid, true), '=', true],
            ]
        );

        // Check if anything like the username exists
        if (empty($this->data)) {
            $this->data = Database::fetch(
                'users',
                false,
                [
                    'username_clean' => ['%' . Main::cleanString($uid, true) . '%', 'LIKE'],
                ]
            );
        }

        // Check if the user actually exists
        if (empty($this->data)) {
            // If not assign as the fallback user
            $this->data = Users::$emptyUser;
        }

        // Decode the json in the userData column
        $this->data['userData'] = json_decode(!empty($this->data['userData']) ? $this->data['userData'] : '[]', true);

        // Decode the ranks json array
        $ranks = json_decode($this->data['ranks'], true);

        // Get the rows for all the ranks
        foreach ($ranks as $rank) {
            // Store the database row in the array
            $this->ranks[$rank] = Database::fetch('ranks', false, ['id' => [$rank, '=']]);
        }

        // Check if ranks were set
        if (empty($this->ranks)) {
            // If not assign the fallback rank
            $this->ranks[0] = Users::$emptyRank;
        }

        // Assign the user's main rank to a special variable since we'll use it a lot
        $this->mainRank = $this->ranks[
            array_key_exists($this->data['rank_main'], $this->ranks) ?
            $this->data['rank_main'] :
            array_keys($this->ranks)[0]
        ];

    }

    // Check if the user has the specified ranks
    public function checkIfUserHasRanks($ranks)
    {

        // Check if the main rank is the specified rank
        if (in_array($this->mainRank['id'], $ranks)) {
            return true;
        }

        // If not go over all ranks and check if the user has them
        foreach ($ranks as $rank) {
            // We check if $rank is in $this->ranks and if yes return true
            if (array_key_exists($rank, $this->ranks)) {
                return true;
            }
        }

        // If all fails return false
        return false;

    }

    // Get the user's colour
    public function colour()
    {

        return empty($this->data['name_colour']) ? $this->mainRank['colour'] : $this->data['name_colour'];

    }

    // Get the user's title
    public function userTitle()
    {

        return empty($this->data['usertitle']) ? $this->mainRank['title'] : $this->data['usertitle'];

    }

    // Get the user's long and short country names
    public function country()
    {

        return [

            'long' => Main::getCountryName($this->data['country']),
            'short' => $this->data['country'],

        ];

    }

    // Check if a user is online
    public function checkOnline()
    {

        return $this->data['lastdate'] > (time() - Configuration::getConfig('max_online_time'));

    }

    // Get user's forum statistics
    public function forumStats()
    {

        return Forum::getUserStats($this->data['id']);

    }

    // Check if the user is friends with the currently authenticated
    public function checkFriends($with)
    {

        return Users::checkFriend($this->data['id'], $with);

    }

    // Get all the friend of this user
    public function getFriends($timestamps = false, $getData = false, $checkOnline = false)
    {

        return Users::getFriends($this->data['id'], $timestamps, $getData, $checkOnline);

    }

    // Check if the user is banned
    public function checkBan()
    {

        return Bans::checkBan($this->data['id']);

    }

    // Check if the user has the proper permissions
    public function checkPermission($layer, $action)
    {

        return Permissions::check($layer, $action, $this->data['id'], 1);

    }

    // Get amount of time since user events
    public function elapsed($append = ' ago', $none = 'Just now')
    {

        return [

            'joined' => Main::timeElapsed($this->data['regdate'], $append, $none),
            'lastOnline' => Main::timeElapsed($this->data['lastdate'], $append, $none),
            'birth' => Main::timeElapsed(strtotime($this->data['birthday']), $append, $none),

        ];

    }

    // Get the user's profile fields
    public function profileFields()
    {

        // Get profile fields
        $profileFields = Database::fetch('profilefields');

        // If there's nothing just return null
        if (!count($profileFields)) {
            return;
        }

        // Once again if nothing was returned just return null
        if (empty($this->data['userData']['profileFields'])) {
            return;
        }

        // Create output array
        $profile = [];

        // Check if profile fields aren't fake
        foreach ($profileFields as $field) {
            // Completely strip all special characters from the field name
            $fieldName = Main::cleanString($field['name'], true, true);

            // Check if the user has the current field set otherwise continue
            if (!array_key_exists($fieldName, $this->data['userData']['profileFields'])) {
                continue;
            }

            // Assign field to output with value
            $profile[$fieldName] = array();
            $profile[$fieldName]['name'] = $field['name'];
            $profile[$fieldName]['value'] = $this->data['userData']['profileFields'][$fieldName];
            $profile[$fieldName]['islink'] = $field['islink'];

            // If the field is set to be a link add a value for that as well
            if ($field['islink']) {
                $profile[$fieldName]['link'] = str_replace(
                    '{{ VAL }}',
                    $this->data['userData']['profileFields'][$fieldName],
                    $field['linkformat']
                );
            }

            // Check if we have additional options as well
            if ($field['additional'] != null) {
                // Decode the json of the additional stuff
                $additional = json_decode($field['additional'], true);

                // Go over all additional forms
                foreach ($additional as $subName => $subField) {
                    // Check if the user has the current field set otherwise continue
                    if (!array_key_exists($subName, $this->data['userData']['profileFields'])) {
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

    // Get the user's option fields
    public function optionFields()
    {

        // Get option fields
        $optionFields = Database::fetch('optionfields');

        // If there's nothing just return null
        if (!count($optionFields)) {
            return;
        }

        // Once again if nothing was returned just return null
        if (empty($this->data['userData']['userOptions'])) {
            return;
        }

        // Create output array
        $options = [];

        // Check if profile fields aren't fake
        foreach ($optionFields as $field) {
            // Check if the user has the current field set otherwise continue
            if (!array_key_exists($field['id'], $this->data['userData']['userOptions'])) {
                continue;
            }

            // Make sure the user has the proper permissions to use this option
            if (!$this->checkPermission('SITE', $field['require_perm'])) {
                continue;
            }

            // Assign field to output with value
            $options[$field['id']] = $this->data['userData']['userOptions'][$field['id']];
        }

        // Return appropiate profile data
        return $options;

    }

    // Check if user has Premium
    public function checkPremium()
    {

        // Check if the user has static premium
        if (Permissions::check('SITE', 'STATIC_PREMIUM', $this->data['id'], 1)) {
            return [2, 0, time() + 1];
        }

        // Attempt to retrieve the premium record from the database
        $getRecord = Database::fetch('premium', false, [
            'uid' => [$this->data['id'], '='],
        ]);

        // If nothing was returned just return false
        if (empty($getRecord)) {
            return [0];
        }

        // Check if the Tenshi hasn't expired
        if ($getRecord['expiredate'] < time()) {
            Users::removeUserPremium($this->data['id']);
            Users::updatePremiumMeta($this->data['id']);
            return [0, $getRecord['startdate'], $getRecord['expiredate']];
        }

        // Else return the start and expiration date
        return [1, $getRecord['startdate'], $getRecord['expiredate']];

    }

    // Get all warnings issued to the user
    public function getWarnings()
    {

        // Do the database query
        $warnings = Database::fetch('warnings', true, [
            'uid' => [$this->data['id'], '='],
        ]);

        // Return all the warnings
        return $warnings;

    }
}
