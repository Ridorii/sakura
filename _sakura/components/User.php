<?php
/*
 * Everything you'd ever need from a specific user
 */

namespace Sakura;

/**
 * Class User
 * @package Sakura
 */
class User
{
    // User data
    private $data = [
        'user_id' => 0,
        'username' => 'User',
        'username_clean' => 'user',
        'password_hash' => '',
        'password_salt' => '',
        'password_algo' => 'nologin',
        'password_iter' => 0,
        'password_chan' => 0,
        'password_new' => '',
        'email' => 'sakura@localhost',
        'rank_main' => 0,
        'user_ranks' => '[0]',
        'user_colour' => '',
        'register_ip' => '127.0.0.1',
        'last_ip' => '127.0.0.1',
        'user_title' => '',
        'user_registered' => 0,
        'user_last_online' => 0,
        'user_birthday' => '',
        'user_country' => 'XX',
        'user_data' => '[]',
    ];
    private $ranks = [];
    private $mainRank = [];

    // Initialise the user object
    public function __construct($uid)
    {
        // Get the user database row
        $getUser = Database::fetch(
            'users',
            false,
            [
                'user_id' => [$uid, '=', true],
                'username_clean' => [Main::cleanString($uid, true), '=', true],
            ]
        );

        // Check if the user actually exists
        if (!empty($getUser)) {
            // If not assign as the fallback user
            $this->data = $getUser;
        }

        // Decode the json in the user_data column
        $this->data['user_data'] = json_decode(!empty($this->data['user_data']) ? $this->data['user_data'] : '[]', true);
        $this->data['user_ranks'] = json_decode($this->data['user_ranks'], true);

        // Get the rows for all the ranks
        foreach ($this->data['user_ranks'] as $rank) {
            // Store the database row in the array
            $this->ranks[$rank] = new Rank($rank);
        }

        // Check if ranks were set
        if (empty($this->ranks)) {
            // If not assign the fallback rank
            $this->ranks[0] = new Rank(0);
        }

        // Assign the user's main rank to a special variable since we'll use it a lot
        $this->mainRank = $this->ranks[
            array_key_exists($this->data['rank_main'], $this->ranks) ?
            $this->data['rank_main'] :
            array_keys($this->ranks)[0]
        ];
    }

    // Get user id
    public function id()
    {
        return $this->data['user_id'];
    }

    // Get username (or clean variant)
    public function username($clean = false)
    {
        return $this->data['username' . ($clean ? '_clean' : '')];
    }

    // Get password data
    public function password()
    {
        return [
            'password_hash' => $this->data['password_hash'],
            'password_salt' => $this->data['password_salt'],
            'password_algo' => $this->data['password_algo'],
            'password_iter' => $this->data['password_iter'],
            'password_chan' => $this->data['password_chan'],
            'password_new' => $this->data['password_new'],
        ];
    }

    // Get email
    public function email()
    {
        return $this->data['email'];
    }

    // Get main rank id
    public function mainRank()
    {
        return $this->data['rank_main'];
    }

    // Get all rank ids
    public function ranks()
    {
        return $this->data['user_ranks'];
    }

    // Get the user's colour
    public function colour()
    {
        return empty($this->data['user_colour']) ? $this->mainRank->colour() : $this->data['user_colour'];
    }

    // Get the user's ip
    public function ip($last = false)
    {
        return $this->data[($last ? 'last' : 'register') . '_ip'];
    }

    // Get the user's title
    public function userTitle()
    {
        return empty($this->data['user_title']) ? $this->mainRank->title() : $this->data['user_title'];
    }

    // Get user event times
    public function dates()
    {
        return [
            'joined' => $this->data['user_registered'],
            'lastOnline' => $this->data['user_last_online'],
            'birth' => $this->data['user_birthday'],
        ];
    }

    // Get the user's long and short country names
    public function country()
    {
        return [
            'long' => Main::getCountryName($this->data['user_country']),
            'short' => $this->data['user_country'],
        ];
    }

    // Get the user's raw additional settings
    public function userData()
    {
        return $this->data['user_data'];
    }

    // Check if a user is online
    public function checkOnline()
    {
        return $this->data['user_last_online'] > (time() - Config::getConfig('max_online_time'));
    }

    // Get user's forum statistics
    public function forumStats()
    {
        return Forums::getUserStats($this->data['user_id']);
    }

    // Get amount of time since user events using the same format as dates()
    public function elapsed($append = ' ago', $none = 'Just now')
    {
        $times = [];

        foreach ($this->dates() as $key => $val) {
            $times[$key] = Main::timeElapsed(is_string($val) ? strtotime($val) : $val, $append, $none);
        }

        return $times;
    }

    // Set the main rank of this user
    public function setMainRank($rank)
    {
        // Only allow this if this rank is actually present in their set of ranks
        if (!in_array($rank, $this->ranks())) {
            return false;
        }

        // If it does exist update their row
        Database::update('user', [
            [
                'rank_main' => $rank,
            ],
            [
                'user_id' => [$this->id(), '='],
            ],
        ]);

        // Return true if everything was successful
        return true;
    }

    // Check if this user has the specified ranks
    public function hasRanks($ranks)
    {
        // Check if the main rank is the specified rank
        if (in_array($this->mainRank->id(), $ranks)) {
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

    // For compatibility, too lazy to update the references right now!
    public function checkIfUserHasRanks($ranks)
    {
        return $this->hasRanks($ranks);
    }

    // Add a new friend
    public function addFriend($uid)
    {
        // Create the foreign object
        $user = new User($uid);

        // Validate that the user exists
        if ($user->checkPermission('SITE', 'DEACTIVATED')) {
            return [0, 'USER_NOT_EXIST'];
        }

        // Get check
        $check = $this->checkFriends($uid);

        // Check if the user already has this user a friend
        if ($check) {
            return [0, 'ALREADY_FRIENDS'];
        }

        // Add friend
        Database::insert('friends', [
            'user_id' => $this->data['user_id'],
            'friend_id' => $uid,
            'friend_timestamp' => time(),
        ]);

        // Return true because yay
        return [1, $check == 2 ? 'FRIENDS' : 'NOT_MUTUAL'];
    }

    // Remove a friend
    public function removeFriend($uid, $deleteRequest = false)
    {
        // Create the foreign object
        $user = new User($uid);

        // Validate that the user exists
        if ($user->checkPermission('SITE', 'DEACTIVATED')) {
            return [0, 'USER_NOT_EXIST'];
        }

        // Check if the user has this user a friend
        if (!$this->checkFriends($uid)) {
            return [0, 'ALREADY_REMOVED'];
        }

        // Remove friend
        Database::delete('friends', [
            'user_id' => [$this->data['user_id'], '='],
            'friend_id' => [$uid, '='],
        ]);

        // Attempt to remove the request
        if ($deleteRequest) {
            Database::delete('friends', [
                'friend_id' => [$this->data['user_id'], '='],
                'user_id' => [$uid, '='],
            ]);
        }

        // Return true because yay
        return [1, 'REMOVED'];
    }

    // Check if the user is friends with the currently authenticated
    public function checkFriends($with)
    {
        // Get the friend's friends
        $friend = in_array($this->data['user_id'], (new User($with))->getFriends());

        // Get the user's friends
        $self = in_array($with, $this->getFriends());

        // Check if the friend is actually in the user's array
        if ($friend && $self) {
            return 2;
        }

        // Check if the friend is actually in the user's array
        if ($self) {
            return 1;
        }

        // Return true if all went through
        return 0;
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

    // Get the user's profile fields
    public function profileFields()
    {
        // Get profile fields
        $profileFields = Database::fetch('profilefields');

        // If there's nothing just return null
        if (!count($profileFields)) {
            return [];
        }

        // Once again if nothing was returned just return null
        if (empty($this->data['user_data']['profileFields'])) {
            return [];
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
            $profile[$fieldName] = [];
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
            return [];
        }

        // Once again if nothing was returned just return null
        if (empty($this->data['user_data']['userOptions'])) {
            return [];
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
        $getWarnings = Database::fetch('warnings', true, [
            'user_id' => [$this->data['user_id'], '='],
        ]);

        // Storage array
        $warnings = [];

        // Add special stuff
        foreach ($getWarnings as $warning) {
            // Check if it hasn't expired
            if ($warning['warning_expires'] < time()) {
                Database::delete('warnings', ['warning_id' => [$warning['warning_id'], '=']]);
                continue;
            }

            // Text action
            switch ($warning['warning_action']) {
                default:
                case '0':
                    $warning['warning_action_text'] = 'Warning';
                    break;
                case '1':
                    $warning['warning_action_text'] = 'Silence';
                    break;
                case '2':
                    $warning['warning_action_text'] = 'Restriction';
                    break;
                case '3':
                    $warning['warning_action_text'] = 'Ban';
                    break;
                case '4':
                    $warning['warning_action_text'] = 'Abyss';
                    break;
            }

            // Text expiration
            $warning['warning_length'] = round(($warning['warning_expires'] - $warning['warning_issued']) / 60);

            // Add to array
            $warnings[$warning['warning_id']] = $warning;
        }

        // Return all the warnings
        return $warnings;
    }

    // Get a user's userpage
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

    // Get a user's signature
    public function signature()
    {
        return isset($this->data['user_data']['signature']) ?
        Main::bbParse(
            Main::parseEmotes(
                base64_decode(
                    $this->data['user_data']['signature']
                )
            )
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
        if (strlen($username_clean) < Config::getConfig('username_min_length')) {
            return [0, 'TOO_SHORT'];
        }

        // Check if the username is too long
        if (strlen($username_clean) > Config::getConfig('username_max_length')) {
            return [0, 'TOO_LONG'];
        }

        // Check if this username hasn't been used in the last amount of days set in the config
        $getOld = Database::fetch('username_history', false, [
            'username_old_clean' => [$username_clean, '='],
            'change_time' => [(Config::getConfig('old_username_reserve') * 24 * 60 * 60), '>'],
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
            return [0, 'IN_USE', $getInUse['user_id']];
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
                'user_id' => [$this->data['user_id'], '='],
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
            return [0, 'IN_USE', $getInUse['user_id']];
        }

        // Update userrow
        Database::update('users', [
            [
                'email' => $email,
            ],
            [
                'user_id' => [$this->data['user_id'], '='],
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
        if (Main::pwdEntropy($new) < Config::getConfig('min_entropy')) {
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
                'user_id' => [$this->data['user_id'], '='],
            ],
        ]);

        // Return success
        return [1, 'SUCCESS'];
    }

    // Update a user's userData
    public function setUserData($data)
    {
        // Merge the arrays
        $data = array_merge($this->userData(), $data);

        // Encode it
        $data = json_encode($data);

        // Save it in the database
        Database::update('users', [
            [
                'user_data' => $data,
            ],
            [
                'user_id' => [$this->id(), '='],
            ],
        ]);
    }
}
