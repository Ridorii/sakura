<?php
/**
 * Holds the user object class.
 *
 * @package Sakura
 */

namespace Sakura;

use Sakura\Perms;
use Sakura\Perms\Site;
use stdClass;

/**
 * Everything you'd ever need from a specific user.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class User
{
    /**
     * The User's ID.
     *
     * @var int
     */
    public $id = 0;

    /**
     * The user's username.
     *
     * @var string
     */
    public $username = 'User';

    /**
     * A cleaned version of the username.
     *
     * @var string
     */
    public $usernameClean = 'user';

    /**
     * The user's password hash.
     *
     * @var string
     */
    public $passwordHash = '';

    /**
     * The user's password salt.
     *
     * @var string
     */
    public $passwordSalt = '';

    /**
     * The user's password algorithm.
     *
     * @var string
     */
    public $passwordAlgo = 'disabled';

    /**
     * The password iterations.
     *
     * @var int
     */
    public $passwordIter = 0;

    /**
     * UNIX timestamp of last time the password was changed.
     *
     * @var int
     */
    public $passwordChan = 0;

    /**
     * The user's e-mail address.
     *
     * @var string
     */
    public $email = 'user@sakura';

    /**
     * The rank object of the user's main rank.
     *
     * @var Rank
     */
    public $mainRank = null;

    /**
     * The ID of the main rank.
     *
     * @var int
     */
    public $mainRankId = 1;

    /**
     * The index of rank objects.
     *
     * @var array
     */
    public $ranks = [];

    /**
     * The user's username colour.
     *
     * @var string
     */
    public $colour = '';

    /**
     * The IP the user registered from.
     *
     * @var string
     */
    public $registerIp = '0.0.0.0';

    /**
     * The IP the user was last active from.
     *
     * @var string
     */
    public $lastIp = '0.0.0.0';

    /**
     * A user's title.
     *
     * @var string
     */
    public $title = '';

    /**
     * The UNIX timestamp of when the user registered.
     *
     * @var int
     */
    public $registered = 0;

    /**
     * The UNIX timestamp of when the user was last online.
     *
     * @var int
     */
    public $lastOnline = 0;

    /**
     * The 2 character country code of a user.
     *
     * @var string
     */
    public $country = 'XX';

    /**
     * The File id of the user's avatar.
     *
     * @var int
     */
    public $avatar = 0;

    /**
     * The File id of the user's background.
     *
     * @var int
     */
    public $background = 0;

    /**
     * The File id of the user's header.
     * @var mixed
     */
    public $header = 0;

    /**
     * The raw userpage of the user.
     *
     * @var string
     */
    public $page = '';

    /**
     * The raw signature of the user.
     *
     * @var string
     */
    public $signature = '';

    /**
     * The user's birthday.
     *
     * @var string
     */
    private $birthday = '0000-00-00';

    /**
     * The user's permission container.
     *
     * @var Perms
     */
    private $permissions;

    /**
     * The user's option fields.
     *
     * @var array
     */
    private $optionFields = null;

    /**
     * The user's profile fields.
     *
     * @var array
     */
    private $profileFields = null;

    /**
     * The User instance cache array.
     *
     * @var array
     */
    protected static $_userCache = [];

    /**
     * Cached constructor.
     *
     * @param int|string $uid The user ID or clean username.
     * @param bool $forceRefresh Force a recreation.
     *
     * @return User Returns a user object.
     */
    public static function construct($uid, $forceRefresh = false)
    {
        // Check if a user object isn't present in cache
        if ($forceRefresh || !array_key_exists($uid, self::$_userCache)) {
            // If not create a new object and cache it
            self::$_userCache[$uid] = new User($uid);
        }

        // Return the cached object
        return self::$_userCache[$uid];
    }

    /**
     * Create a new user.
     *
     * @param string $username The username of the user.
     * @param string $password The password of the user.
     * @param string $email The e-mail, used primarily for activation.
     * @param array $ranks The ranks assigned to the user on creation.
     *
     * @return User The newly created user's object.
     */
    public static function create($username, $password, $email, $ranks = [2])
    {
        // Set a few variables
        $usernameClean = clean_string($username, true);
        $emailClean = clean_string($email, true);
        $password = Hashing::createHash($password);

        // Insert the user into the database and get the id
        $userId = DB::table('users')
            ->insertGetId([
                'username' => $username,
                'username_clean' => $usernameClean,
                'password_hash' => $password[3],
                'password_salt' => $password[2],
                'password_algo' => $password[0],
                'password_iter' => $password[1],
                'email' => $emailClean,
                'rank_main' => 0,
                'register_ip' => Net::pton(Net::ip()),
                'last_ip' => Net::pton(Net::ip()),
                'user_registered' => time(),
                'user_last_online' => 0,
                'user_country' => get_country_code(),
            ]);

        // Create a user object
        $user = self::construct($userId);

        // Assign the default rank
        $user->addRanks($ranks);

        // Set the default rank
        $user->setMainRank($ranks[0]);

        // Return the user object
        return $user;
    }

    /**
     * The actual constructor
     *
     * @param int|string $userId The user ID or clean username.
     */
    private function __construct($userId)
    {
        // Get the user database row
        $userRow = DB::table('users')
            ->where('user_id', $userId)
            ->orWhere('username_clean', clean_string($userId, true, true))
            ->get();

        // Populate the variables
        if ($userRow) {
            $userRow = $userRow[0];
            $this->id = $userRow->user_id;
            $this->username = $userRow->username;
            $this->usernameClean = $userRow->username_clean;
            $this->passwordHash = $userRow->password_hash;
            $this->passwordSalt = $userRow->password_salt;
            $this->passwordAlgo = $userRow->password_algo;
            $this->passwordIter = $userRow->password_iter;
            $this->passwordChan = $userRow->password_chan;
            $this->email = $userRow->email;
            $this->mainRankId = $userRow->rank_main;
            $this->colour = $userRow->user_colour;
            $this->registerIp = Net::ntop($userRow->register_ip);
            $this->lastIp = Net::ntop($userRow->last_ip);
            $this->title = $userRow->user_title;
            $this->registered = $userRow->user_registered;
            $this->lastOnline = $userRow->user_last_online;
            $this->birthday = $userRow->user_birthday;
            $this->country = $userRow->user_country;
            $this->avatar = $userRow->user_avatar;
            $this->background = $userRow->user_background;
            $this->header = $userRow->user_header;
            $this->page = $userRow->user_page;
            $this->signature = $userRow->user_signature;
        }

        // Get all ranks
        $ranks = DB::table('user_ranks')
            ->where('user_id', $this->id)
            ->get(['rank_id']);

        // Get the rows for all the ranks
        foreach ($ranks as $rank) {
            // Store the database row in the array
            $this->ranks[$rank->rank_id] = Rank::construct($rank->rank_id);
        }

        // Check if ranks were set
        if (empty($this->ranks)) {
            // If not assign the fallback rank
            $this->ranks[1] = Rank::construct(1);
        }

        // Check if the rank is actually assigned to this user
        if (!array_key_exists($this->mainRankId, $this->ranks)) {
            $this->mainRankId = array_keys($this->ranks)[0];
            $this->setMainRank($this->mainRankId);
        }

        // Assign the main rank to its own var
        $this->mainRank = $this->ranks[$this->mainRankId];

        // Set user colour
        $this->colour = $this->colour ? $this->colour : $this->mainRank->colour;

        // Set user title
        $this->title = $this->title ? $this->title : $this->mainRank->title;

        // Init the permissions
        $this->permissions = new Perms(Perms::SITE);
    }

    /**
     * Get the user's birthday.
     *
     * @param bool $age Just get the age.
     *
     * @return int|string Return the birthday.
     */
    public function birthday($age = false)
    {
        // If age is requested calculate it
        if ($age) {
            // Create dates
            $birthday = date_create($this->birthday);
            $now = date_create(date('Y-m-d'));

            // Get the difference
            $diff = date_diff($birthday, $now);

            // Return the difference in years
            return (int) $diff->format('%Y');
        }

        // Otherwise just return the birthday value
        return $this->birthday;
    }

    /**
     * Get the user's country.
     *
     * @param bool $long Get the full country name.
     *
     * @return string The country.
     */
    public function country($long = false)
    {
        return $long ? get_country_name($this->country) : $this->country;
    }

    /**
     * Check if a user is online.
     *
     * @return bool Are they online?
     */
    public function isOnline()
    {
        // Count sessions
        $sessions = DB::table('sessions')
            ->where('user_id', $this->id)
            ->count();

        // If there's no entries just straight up return false
        if (!$sessions) {
            return false;
        }

        // Otherwise use the standard method
        return $this->lastOnline > (time() - Config::get('max_online_time'));
    }

    /**
     * Runs some checks to see if this user is activated.
     *
     * @return bool Are they activated?
     */
    public function isActive()
    {
        return $this->id !== 0 && !$this->permission(Site::DEACTIVATED);
    }

    /**
     * Get a few forum statistics.
     *
     * @return array Post and thread counts.
     */
    public function forumStats()
    {
        $posts = DB::table('posts')
            ->where('poster_id', $this->id)
            ->count();

        $threads = DB::table('posts')
            ->where('poster_id', $this->id)
            ->distinct()
            ->groupBy('topic_id')
            ->orderBy('post_time')
            ->count();

        return [
            'posts' => $posts,
            'topics' => $threads,
        ];
    }

    /**
     * Add ranks to a user.
     *
     * @param array $ranks Array containing the rank IDs.
     */
    public function addRanks($ranks)
    {
        // Update the ranks array
        $ranks = array_diff(
            array_unique(
                array_merge(
                    array_keys($this->ranks),
                    $ranks
                )
            ),
            array_keys($this->ranks)
        );

        // Save to the database
        foreach ($ranks as $rank) {
            DB::table('user_ranks')
                ->insert([
                    'rank_id' => $rank,
                    'user_id' => $this->id,
                ]);
        }
    }

    /**
     * Remove a set of ranks from a user.
     *
     * @param array $ranks Array containing the IDs of ranks to remove.
     */
    public function removeRanks($ranks)
    {
        // Current ranks
        $remove = array_intersect(array_keys($this->ranks), $ranks);

        // Iterate over the ranks
        foreach ($remove as $rank) {
            DB::table('user_ranks')
                ->where('user_id', $this->id)
                ->where('rank_id', $rank)
                ->delete();
        }
    }

    /**
     * Change the main rank of a user.
     *
     * @param int $rank The ID of the new main rank.
     *
     * @return bool Always true.
     */
    public function setMainRank($rank)
    {
        // If it does exist update their row
        DB::table('users')
            ->where('user_id', $this->id)
            ->update([
                'rank_main' => $rank,
            ]);

        // Return true if everything was successful
        return true;
    }

    /**
     * Check if a user has a certain set of rank.
     *
     * @param array $ranks Ranks IDs to check.
     *
     * @return bool Successful?
     */
    public function hasRanks($ranks)
    {
        // Check if the main rank is the specified rank
        if (in_array($this->mainRankId, $ranks)) {
            return true;
        }

        // If not go over all ranks and check if the user has them
        foreach ($ranks as $rank) {
            // We check if $rank is in $this->ranks and if yes return true
            if (in_array($rank, array_keys($this->ranks))) {
                return true;
            }
        }

        // If all fails return false
        return false;
    }

    /**
     * Add a new friend.
     *
     * @param int $uid The ID of the friend.
     */
    public function addFriend($uid)
    {
        // Create the foreign object
        $user = User::construct($uid);

        // Add friend
        DB::table('friends')
            ->insert([
                'user_id' => $this->id,
                'friend_id' => $uid,
                'friend_timestamp' => time(),
            ]);
    }

    /**
     * Remove a friend.
     *
     * @param int $uid The friend Id
     * @param bool $deleteRequest Delete the open request as well (remove you from their friends list).
     */
    public function removeFriend($uid, $deleteRequest = false)
    {
        // Create the foreign object
        $user = User::construct($uid);

        // Remove friend
        DB::table('friends')
            ->where('user_id', $this->id)
            ->where('friend_id', $uid)
            ->delete();

        // Attempt to remove the request
        if ($deleteRequest) {
            DB::table('friends')
                ->where('user_id', $uid)
                ->where('friend_id', $this->id)
                ->delete();
        }
    }

    /**
     * Check if this user is friends with another user.
     *
     * @param int $with ID of the other user.
     *
     * @return int 0 = no, 1 = pending request, 2 = mutual
     */
    public function isFriends($with)
    {
        // Accepted from this user
        $user = DB::table('friends')
            ->where('user_id', $this->id)
            ->where('friend_id', $with)
            ->count();

        // And the other user
        $friend = DB::table('friends')
            ->where('user_id', $with)
            ->where('friend_id', $this->id)
            ->count();

        if ($user && $friend) {
            return 2; // Mutual friends
        } elseif ($user) {
            return 1; // Pending request
        }

        // Else return 0
        return 0;
    }

    /**
     * Get all the friends from this user.
     *
     * @param int $level Friend level; (figure out what the levels are at some point)
     * @param bool $noObj Just return IDs.
     *
     * @return array The array with either the objects or the ids.
     */
    public function friends($level = 0, $noObj = false)
    {
        // User ID container
        $users = [];

        // Select the correct level
        switch ($level) {
            // Mutual
            case 2:
                // Get all the current user's friends
                $self = DB::table('friends')
                    ->where('user_id', $this->id)
                    ->get(['friend_id']);
                $self = array_column($self, 'friend_id');

                // Get all the people that added this user as a friend
                $others = DB::table('friends')
                    ->where('friend_id', $this->id)
                    ->get(['user_id']);
                $others = array_column($others, 'user_id');

                // Create a difference map
                $users = array_intersect($self, $others);
                break;

            // Non-mutual (from user perspective)
            case 1:
                $users = DB::table('friends')
                    ->where('user_id', $this->id)
                    ->get(['friend_id']);
                $users = array_column($users, 'friend_id');
                break;

            // All friend cases
            case 0:
            default:
                // Get all the current user's friends
                $self = DB::table('friends')
                    ->where('user_id', $this->id)
                    ->get(['friend_id']);
                $self = array_column($self, 'friend_id');

                // Get all the people that added this user as a friend
                $others = DB::table('friends')
                    ->where('friend_id', $this->id)
                    ->get(['user_id']);
                $others = array_column($others, 'user_id');

                // Create a difference map
                $users = array_merge($others, $self);
                break;

            // Open requests
            case -1:
                // Get all the current user's friends
                $self = DB::table('friends')
                    ->where('user_id', $this->id)
                    ->get(['friend_id']);
                $self = array_column($self, 'friend_id');

                // Get all the people that added this user as a friend
                $others = DB::table('friends')
                    ->where('friend_id', $this->id)
                    ->get(['user_id']);
                $others = array_column($others, 'user_id');

                // Create a difference map
                $users = array_diff($others, $self);
                break;
        }

        // Check if we only requested the IDs
        if ($noObj) {
            // If so just return $users
            return $users;
        }

        // Create the storage array
        $objects = [];

        // Create the user objects
        foreach ($users as $user) {
            // Create new object
            $objects[$user] = User::construct($user);
        }

        // Return the objects
        return $objects;
    }

    /**
     * Check if the user has a certaing permission flag.
     *
     * @param int $flag The permission flag.
     * @param string $mode The permission mode.
     *
     * @return bool Success?
     */
    public function permission($flag, $mode = null)
    {
        // Set mode
        $this->permissions->mode($mode ? $mode : Perms::SITE);

        // Set default permission value
        $perm = 0;

        // Bitwise OR it with the permissions for this forum
        $perm = $this->permissions->user($this->id);

        return $this->permissions->check($flag, $perm);
    }

    /**
     * Get the comments from the user's profile.
     *
     * @return Comments
     */
    public function profileComments()
    {
        $commentIds = DB::table('comments')
            ->where('comment_category', "profile-{$this->id}")
            ->orderBy('comment_id', 'desc')
            ->where('comment_reply_to', 0)
            ->get(['comment_id']);
        $commentIds = array_column($commentIds, 'comment_id');

        $comments = [];

        foreach ($commentIds as $comment) {
            $comments[$comment] = new Comment($comment);
        }

        return $comments;
    }

    /**
     * Get the user's profile fields.
     *
     * @return array The profile fields.
     */
    public function profileFields()
    {
        // Check if we have cached data
        if ($this->profileFields) {
            return $this->profileFields;
        }

        // Create array and get values
        $profile = [];

        $profileFields = DB::table('profilefields')
            ->get();

        $profileValuesRaw = DB::table('user_profilefields')
            ->where('user_id', $this->id)
            ->get();

        $profileValues = array_column($profileValuesRaw, 'field_value', 'field_name');

        // Check if anything was returned
        if (!$profileFields || !$profileValues) {
            return $profile;
        }

        // Check if profile fields aren't fake
        foreach ($profileFields as $field) {
            // Completely strip all special characters from the field name
            $fieldName = clean_string($field->field_name, true, true);

            // Check if the user has the current field set otherwise continue
            if (!array_key_exists($fieldName, $profileValues)) {
                continue;
            }

            // Assign field to output with value
            $profile[$fieldName] = [];
            $profile[$fieldName]['name'] = $field->field_name;
            $profile[$fieldName]['value'] = $profileValues[$fieldName];
            $profile[$fieldName]['islink'] = $field->field_link;

            // If the field is set to be a link add a value for that as well
            if ($field->field_link) {
                $profile[$fieldName]['link'] = str_replace(
                    '{{ VAL }}',
                    $profileValues[$fieldName],
                    $field->field_linkformat
                );
            }

            // Check if we have additional options as well
            if (!empty($field->field_additional)) {
                // Decode the json of the additional stuff
                $additional = json_decode($field->field_additional, true);

                // Go over all additional forms
                foreach ($additional as $subName => $subField) {
                    // Check if the user has the current field set otherwise continue
                    if (!array_key_exists($subName, $profileValues)) {
                        continue;
                    }

                    // Assign field to output with value
                    $profile[$fieldName][$subName] = $profileValues[$subName];
                }
            }
        }

        // Assign cache
        $this->profileFields = $profile;

        // Return appropiate profile data
        return $profile;
    }

    /**
     * Get a user's option fields.
     *
     * @return array The array containing the fields.
     */
    public function optionFields()
    {
        // Check if we have cached data
        if ($this->optionFields) {
            return $this->optionFields;
        }

        // Create array and get values
        $options = [];

        $optionFields = DB::table('optionfields')
            ->get();

        $optionValuesRaw = DB::table('user_optionfields')
            ->where('user_id', $this->id)
            ->get();

        $optionValues = array_column($optionValuesRaw, 'field_value', 'field_name');

        // Check if anything was returned
        if (!$optionFields || !$optionValues) {
            return $options;
        }

        // Check if option fields aren't fake
        foreach ($optionFields as $field) {
            // Check if the user has the current field set otherwise continue
            if (!array_key_exists($field->option_id, $optionValues)) {
                continue;
            }

            // Make sure the user has the proper permissions to use this option
            if (!$this->permission(constant('Sakura\Perms\Site::' . $field->option_permission))) {
                continue;
            }

            // Assign field to output with value
            $options[$field->option_id] = $optionValues[$field->option_id];
        }

        // Assign cache
        $this->optionFields = $options;

        // Return appropiate option data
        return $options;
    }

    /**
     * Add premium in seconds.
     *
     * @param int $seconds The amount of seconds.
     *
     * @return int The new expiry date.
     */
    public function addPremium($seconds)
    {
        // Check if there's already a record of premium for this user in the database
        $getUser = DB::table('premium')
            ->where('user_id', $this->id)
            ->get();

        // Calculate the (new) start and expiration timestamp
        $start = $getUser ? $getUser[0]->premium_start : time();
        $expire = $getUser ? $getUser[0]->premium_expire + $seconds : time() + $seconds;

        // If the user already exists do an update call, otherwise an insert call
        if ($getUser) {
            DB::table('premium')
                ->where('user_id', $this->id)
                ->update([
                    'premium_expire' => $expire,
                ]);
        } else {
            DB::table('premium')
                ->insert([
                    'user_id' => $this->id,
                    'premium_start' => $start,
                    'premium_expire' => $expire,
                ]);
        }

        // Return the expiration timestamp
        return $expire;
    }

    /**
     * Does this user have premium?
     *
     * @return int Returns the premium expiration date.
     */
    public function isPremium()
    {
        // Get rank IDs from the db
        $premiumRank = (int) Config::get('premium_rank_id');
        $defaultRank = (int) Config::get('default_rank_id');

        // Fetch expiration date
        $expire = $this->premiumInfo()->expire;

        // Check if the user has static premium
        if (!$expire
            && $this->permission(Site::STATIC_PREMIUM)) {
            $expire = time() + 1;
        }

        // Check if the user has premium and isn't in the premium rank
        if ($expire
            && !$this->hasRanks([$premiumRank])) {
            // Add the premium rank
            $this->addRanks([$premiumRank]);

            // Set it as default
            if ($this->mainRankId == $defaultRank) {
                $this->setMainRank($premiumRank);
            }
        } elseif (!$expire
            && $this->hasRanks([$premiumRank])) {
            $this->removeRanks([$premiumRank]);

            if ($this->mainRankId == $premiumRank) {
                $this->setMainRank($defaultRank);
            }
        }

        return $expire;
    }

    public function premiumInfo()
    {
        // Attempt to retrieve the premium record from the database
        $check = DB::table('premium')
            ->where('user_id', $this->id)
            ->where('premium_expire', '>', time())
            ->get();

        $return = new stdClass;

        $return->start = $check ? $check[0]->premium_start : 0;
        $return->expire = $check ? $check[0]->premium_expire : 0;

        return $return;
    }

    /**
     * Get the open warnings on this user.
     *
     * @return array The warnings.
     */
    public function getWarnings()
    {
        // Do the database query
        $getWarnings = DB::table('warnings')
            ->where('user_id', $this->id)
            ->get();

        // Storage array
        $warnings = [];

        // Add special stuff
        foreach ($getWarnings as $warning) {
            // Check if it hasn't expired
            if ($warning->warning_expires < time()) {
                DB::table('warnings')
                    ->where('warning_id', $warning['warning_id'])
                    ->delete();
                continue;
            }

            // Text action
            switch ($warning->warning_action) {
                default:
                case '0':
                    $warning->warning_action_text = 'Warning';
                    break;
                case '1':
                    $warning->warning_action_text = 'Silence';
                    break;
                case '2':
                    $warning->warning_action_text = 'Restriction';
                    break;
                case '3':
                    $warning->warning_action_text = 'Ban';
                    break;
                case '4':
                    $warning->warning_action_text = 'Abyss';
                    break;
            }

            // Text expiration
            $warning->warning_length = round(($warning->warning_expires - $warning->warning_issued) / 60);

            // Add to array
            $warnings[$warning->warning_id] = $warning;
        }

        // Return all the warnings
        return $warnings;
    }

    /**
     * Parse the user's userpage.
     *
     * @return string The parsed page.
     */
    public function userPage()
    {
        return BBcode::toHTML(htmlentities($this->page));
    }

    /**
     * Parse a user's signature
     *
     * @return string The parsed signature.
     */
    public function signature()
    {
        return BBcode::toHTML(htmlentities($this->signature));
    }

    /**
     * Get a user's username history.
     *
     * @return array The history.
     */
    public function getUsernameHistory()
    {
        return DB::table('username_history')
            ->where('user_id', $this->id)
            ->orderBy('change_id', 'desc')
            ->get();
    }

    /**
     * Alter the user's username
     *
     * @param string $username The new username.
     *
     * @return array Status indicator.
     */
    public function setUsername($username)
    {
        // Create a cleaned version
        $username_clean = clean_string($username, true);

        // Check if the username is too short
        if (strlen($username_clean) < Config::get('username_min_length')) {
            return [0, 'TOO_SHORT'];
        }

        // Check if the username is too long
        if (strlen($username_clean) > Config::get('username_max_length')) {
            return [0, 'TOO_LONG'];
        }

        // Check if this username hasn't been used in the last amount of days set in the config
        $getOld = DB::table('username_history')
            ->where('username_old_clean', $username_clean)
            ->where('change_time', '>', (Config::get('old_username_reserve') * 24 * 60 * 60))
            ->orderBy('change_id', 'desc')
            ->get();

        // Check if anything was returned
        if ($getOld && $getOld[0]->user_id != $this->id) {
            return [0, 'TOO_RECENT', $getOld[0]['change_time']];
        }

        // Check if the username is already in use
        $getInUse = DB::table('users')
            ->where('username_clean', $username_clean)
            ->get();

        // Check if anything was returned
        if ($getInUse) {
            return [0, 'IN_USE', $getInUse[0]->user_id];
        }

        // Insert into username_history table
        DB::table('username_history')
            ->insert([
                'change_time' => time(),
                'user_id' => $this->id,
                'username_new' => $username,
                'username_new_clean' => $username_clean,
                'username_old' => $this->username,
                'username_old_clean' => $this->usernameClean,
            ]);

        // Update userrow
        DB::table('users')
            ->where('user_id', $this->id)
            ->update([
                'username' => $username,
                'username_clean' => $username_clean,
            ]);

        // Return success
        return [1, 'SUCCESS', $username];
    }

    /**
     * Alter a user's e-mail address
     *
     * @param string $email The new e-mail address.
     *
     * @return array Status indicator.
     */
    public function setEMailAddress($email)
    {
        // Validate e-mail address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [0, 'INVALID'];
        }

        // Check if the username is already in use
        $getInUse = DB::table('users')
            ->where('email', $email)
            ->get();

        // Check if anything was returned
        if ($getInUse) {
            return [0, 'IN_USE', $getInUse[0]->user_id];
        }

        // Update userrow
        DB::table('users')
            ->where('user_id', $this->id)
            ->update([
                'email' => $email,
            ]);

        // Return success
        return [1, 'SUCCESS', $email];
    }

    /**
     * Change the user's password
     *
     * @param string $old The old password.
     * @param string $new The new password
     * @param string $confirm The new one again.
     *
     * @return array Status indicator.
     */
    public function setPassword($old, $new, $confirm)
    {
        // Validate password
        switch ($this->passwordAlgo) {
            // Disabled account
            case 'disabled':
                return [0, 'NO_LOGIN'];

            // Default hashing method
            default:
                if (!Hashing::validatePassword($old, [
                    $this->passwordAlgo,
                    $this->passwordIter,
                    $this->passwordSalt,
                    $this->passwordHash,
                ])) {
                    return [0, 'INCORRECT_PASSWORD', $this->passwordChan];
                }

        }

        // Check password entropy
        if (password_entropy($new) < Config::get('min_entropy')) {
            return [0, 'PASS_TOO_SHIT'];
        }

        // Passwords do not match
        if ($new != $confirm) {
            return [0, 'PASS_NOT_MATCH'];
        }

        // Create hash
        $password = Hashing::createHash($new);

        // Update userrow
        DB::table('users')
            ->where('user_id', $this->id)
            ->update([
                'password_hash' => $password[3],
                'password_salt' => $password[2],
                'password_algo' => $password[0],
                'password_iter' => $password[1],
                'password_chan' => time(),
            ]);

        // Return success
        return [1, 'SUCCESS'];
    }

    /**
     * Get all the notifications for this user.
     *
     * @param int $timeDifference The timeframe of alerts that should be fetched.
     * @param bool $excludeRead Whether alerts that are marked as read should be included.
     *
     * @return array An array with Notification objects.
     */
    public function notifications($timeDifference = 0, $excludeRead = true)
    {
        $alertIds = DB::table('notifications')
            ->where('user_id', $this->id);

        if ($timeDifference) {
            $alertIds->where('alert_timestamp', '>', time() - $timeDifference);
        }

        if ($excludeRead) {
            $alertIds->where('alert_read', 0);
        }

        $alertIds = array_column($alertIds->get(['alert_id']), 'alert_id');
        $alerts = [];

        foreach ($alertIds as $alertId) {
            $alerts[$alertId] = new Notification($alertId);
        }

        return $alerts;
    }
}
