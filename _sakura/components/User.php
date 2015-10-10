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
                'user_id' => [$uid, '=', true],
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

        // Decode the json in the user_data column
        $this->data['user_data'] = json_decode(!empty($this->data['user_data']) ? $this->data['user_data'] : '[]', true);

        // Decode the ranks json array
        $ranks = json_decode($this->data['user_ranks'], true);

        // Get the rows for all the ranks
        foreach ($ranks as $rank) {
            // Store the database row in the array
            $this->ranks[$rank] = Database::fetch('ranks', false, ['rank_id' => [$rank, '=']]);
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
        if (in_array($this->mainRank['rank_id'], $ranks)) {
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

        return empty($this->data['user_colour']) ? $this->mainRank['rank_colour'] : $this->data['user_colour'];

    }

    // Get the user's title
    public function userTitle()
    {

        return empty($this->data['user_title']) ? $this->mainRank['rank_title'] : $this->data['user_title'];

    }

    // Get the user's long and short country names
    public function country()
    {

        return [

            'long' => Main::getCountryName($this->data['user_country']),
            'short' => $this->data['user_country'],

        ];

    }

    // Check if a user is online
    public function checkOnline()
    {

        return $this->data['user_last_online'] > (time() - Configuration::getConfig('max_online_time'));

    }

    // Get user's forum statistics
    public function forumStats()
    {

        return Forum::getUserStats($this->data['user_id']);

    }

    // Check if the user is friends with the currently authenticated
    public function checkFriends($with)
    {

        return Users::checkFriend($this->data['user_id'], $with);

    }

    // Get all the friend of this user
    public function getFriends($timestamps = false, $getData = false, $checkOnline = false)
    {

        return Users::getFriends($this->data['user_id'], $timestamps, $getData, $checkOnline);

    }

    // Check if the user is banned
    public function checkBan()
    {

        return Bans::checkBan($this->data['user_id']);

    }

    // Check if the user has the proper permissions
    public function checkPermission($layer, $action)
    {

        return Permissions::check($layer, $action, $this->data['user_id'], 1);

    }

    // Get a user's profile comments
    public function profileComments()
    {

        return new Comments('profile-' . $this->data['user_id']);

    }

    // Get amount of time since user events
    public function elapsed($append = ' ago', $none = 'Just now')
    {

        return [

            'joined' => Main::timeElapsed($this->data['user_registered'], $append, $none),
            'lastOnline' => Main::timeElapsed($this->data['user_last_online'], $append, $none),
            'birth' => Main::timeElapsed(strtotime($this->data['user_birthday']), $append, $none),

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
        if (empty($this->data['user_data']['profileFields'])) {
            return;
        }

        // Create output array
        $profile = [];

        // Check if profile fields aren't fake
        foreach ($profileFields as $field) {
            // Completely strip all special characters from the field name
            $fieldName = Main::cleanString($field['field_name'], true, true);

            // Check if the user has the current field set otherwise continue
            if (!array_key_exists($fieldName, $this->data['user_data']['profileFields'])) {
                continue;
            }

            // Assign field to output with value
            $profile[$fieldName] = array();
            $profile[$fieldName]['name'] = $field['field_name'];
            $profile[$fieldName]['value'] = $this->data['user_data']['profileFields'][$fieldName];
            $profile[$fieldName]['islink'] = $field['field_link'];

            // If the field is set to be a link add a value for that as well
            if ($field['field_link']) {
                $profile[$fieldName]['link'] = str_replace(
                    '{{ VAL }}',
                    $this->data['user_data']['profileFields'][$fieldName],
                    $field['field_linkformat']
                );
            }

            // Check if we have additional options as well
            if ($field['field_additional'] != null) {
                // Decode the json of the additional stuff
                $additional = json_decode($field['field_additional'], true);

                // Go over all additional forms
                foreach ($additional as $subName => $subField) {
                    // Check if the user has the current field set otherwise continue
                    if (!array_key_exists($subName, $this->data['user_data']['profileFields'])) {
                        continue;
                    }

                    // Assign field to output with value
                    $profile[$fieldName][$subName] = $this->data['user_data']['profileFields'][$subName];
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
        if (empty($this->data['user_data']['userOptions'])) {
            return;
        }

        // Create output array
        $options = [];

        // Check if profile fields aren't fake
        foreach ($optionFields as $field) {
            // Check if the user has the current field set otherwise continue
            if (!array_key_exists($field['option_id'], $this->data['user_data']['userOptions'])) {
                continue;
            }

            // Make sure the user has the proper permissions to use this option
            if (!$this->checkPermission('SITE', $field['option_permission'])) {
                continue;
            }

            // Assign field to output with value
            $options[$field['option_id']] = $this->data['user_data']['userOptions'][$field['option_id']];
        }

        // Return appropiate profile data
        return $options;

    }

    // Check if user has Premium
    public function checkPremium()
    {

        // Check if the user has static premium
        if (Permissions::check('SITE', 'STATIC_PREMIUM', $this->data['user_id'], 1)) {
            return [2, 0, time() + 1];
        }

        // Attempt to retrieve the premium record from the database
        $getRecord = Database::fetch('premium', false, [
            'user_id' => [$this->data['user_id'], '='],
        ]);

        // If nothing was returned just return false
        if (empty($getRecord)) {
            return [0];
        }

        // Check if the Tenshi hasn't expired
        if ($getRecord['premium_expire'] < time()) {
            Users::removeUserPremium($this->data['user_id']);
            Users::updatePremiumMeta($this->data['user_id']);
            return [0, $getRecord['premium_start'], $getRecord['premium_expire']];
        }

        // Else return the start and expiration date
        return [1, $getRecord['premium_start'], $getRecord['premium_expire']];

    }

    // Get all warnings issued to the user
    public function getWarnings()
    {

        // Do the database query
        $warnings = Database::fetch('warnings', true, [
            'user_id' => [$this->data['user_id'], '='],
        ]);

        // Return all the warnings
        return $warnings;

    }

    // Get all warnings issued to the user
    public function userPage()
    {

        return isset($this->data['user_data']['userPage']) ?
        Main::mdParse(
            base64_decode(
                $this->data['user_data']['userPage']
            ),
            true
        ) :
        null;

    }

    // Get username change history
    public function getUsernameHistory()
    {

        // Do the database query
        $changes = Database::fetch('username_history', true, [
            'user_id' => [$this->data['user_id'], '='],
        ], ['change_id', true]);

        // Return all the warnings
        return $changes;

    }

    // Set a new username
    public function setUsername($username)
    {

        // Create a cleaned version
        $username_clean = Main::cleanString($username, true);

        // Check if the username is too short
        if (strlen($username_clean) < Configuration::getConfig('username_min_length')) {
            return [0, 'TOO_SHORT'];
        }

        // Check if the username is too long
        if (strlen($username_clean) > Configuration::getConfig('username_max_length')) {
            return [0, 'TOO_LONG'];
        }

        // Check if this username hasn't been used in the last amount of days set in the config
        $getOld = Database::fetch('username_history', false, [
            'username_old_clean' => [$username_clean, '='],
            'change_time' => [(Configuration::getConfig('old_username_reserve') * 24 * 60 * 60), '>'],
        ], ['change_id', true]);

        // Check if anything was returned
        if ($getOld) {
            return [0, 'TOO_RECENT', $getOld['change_time']];
        }

        // Check if the username is already in use
        $getInUse = Database::fetch('users', false, [
            'username_clean' => [$username_clean, '='],
        ]);

        // Check if anything was returned
        if ($getInUse) {
            return [0, 'IN_USE', $getInUse['id']];
        }

        // Insert into username_history table
        Database::insert('username_history', [
            'change_time' => time(),
            'user_id' => $this->data['user_id'],
            'username_new' => $username,
            'username_new_clean' => $username_clean,
            'username_old' => $this->data['username'],
            'username_old_clean' => $this->data['username_clean'],
        ]);

        // Update userrow
        Database::update('users', [
            [
                'username' => $username,
                'username_clean' => $username_clean,
            ],
            [
                'id' => [$this->data['user_id'], '='],
            ],
        ]);

        // Return success
        return [1, 'SUCCESS', $username];

    }

    // Set a new e-mail address
    public function setEMailAddress($email)
    {

        // Validate e-mail address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [0, 'INVALID'];
        }

        // Check if the username is already in use
        $getInUse = Database::fetch('users', false, [
            'email' => [$email, '='],
        ]);

        // Check if anything was returned
        if ($getInUse) {
            return [0, 'IN_USE', $getInUse['id']];
        }

        // Update userrow
        Database::update('users', [
            [
                'email' => $email,
            ],
            [
                'id' => [$this->data['user_id'], '='],
            ],
        ]);

        // Return success
        return [1, 'SUCCESS', $email];

    }

    // Set a new password
    public function setPassword($old, $new, $confirm)
    {
        // Validate password
        switch ($this->data['password_algo']) {
            // Abyssing
            case 'nologin':
                return [0, 'NO_LOGIN'];

            // Default hashing method
            default:
                if (!Hashing::validatePassword($old, [
                    $this->data['password_algo'],
                    $this->data['password_iter'],
                    $this->data['password_salt'],
                    $this->data['password_hash'],
                ])) {
                    return [0, 'INCORRECT_PASSWORD', $this->data['password_chan']];
                }

        }

        // Check password entropy
        if (Main::pwdEntropy($new) < Configuration::getConfig('min_entropy')) {
            return [0, 'PASS_TOO_SHIT'];
        }

        // Passwords do not match
        if ($new != $confirm) {
            return [0, 'PASS_NOT_MATCH'];
        }

        // Create hash
        $password = Hashing::createHash($new);

        // Update userrow
        Database::update('users', [
            [
                'password_hash' => $password[3],
                'password_salt' => $password[2],
                'password_algo' => $password[0],
                'password_iter' => $password[1],
                'password_chan' => time(),
            ],
            [
                'id' => [$this->data['user_id'], '='],
            ],
        ]);

        // Return success
        return [1, 'SUCCESS'];

    }
}
