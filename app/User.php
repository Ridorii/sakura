<?php
/**
 * Holds the user object class.
 * @package Sakura
 */

namespace Sakura;

use Carbon\Carbon;
use LastFmApi\Api\AuthApi;
use LastFmApi\Api\UserApi;
use LastFmApi\Exception\LastFmApiExeption;
use Sakura\Exceptions\NetAddressTypeException;
use stdClass;

/**
 * Everything you'd ever need from a specific user.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class User
{
    /**
     * The User's ID.
     * @var int
     */
    public $id = 0;

    /**
     * The user's username.
     * @var string
     */
    public $username = 'User';

    /**
     * A cleaned version of the username.
     * @var string
     */
    public $usernameClean = 'user';

    /**
     * The user's password hash.
     * @var string
     */
    private $password = '';

    /**
     * UNIX timestamp of last time the password was changed.
     * @var int
     */
    public $passwordChan = 0;

    /**
     * The user's e-mail address.
     * @var string
     */
    public $email = 'user@sakura';

    /**
     * The rank object of the user's main rank.
     * @var Rank
     */
    public $mainRank = null;

    /**
     * The ID of the main rank.
     * @var int
     */
    public $mainRankId = 1;

    /**
     * The index of rank objects.
     * @var array
     */
    public $ranks = [];

    /**
     * The user's username colour.
     * @var string
     */
    public $colour = '';

    /**
     * The IP the user registered from.
     * @var string
     */
    public $registerIp = '0.0.0.0';

    /**
     * The IP the user was last active from.
     * @var string
     */
    public $lastIp = '0.0.0.0';

    /**
     * A user's title.
     * @var string
     */
    public $title = '';

    /**
     * The UNIX timestamp of when the user registered.
     * @var int
     */
    public $registered = 0;

    /**
     * The UNIX timestamp of when the user was last online.
     * @var int
     */
    public $lastOnline = 0;

    /**
     * The 2 character country code of a user.
     * @var string
     */
    public $country = 'XX';

    /**
     * The File id of the user's avatar.
     * @var int
     */
    public $avatar = 0;

    /**
     * The File id of the user's background.
     * @var int
     */
    public $background = 0;

    /**
     * The File id of the user's header.
     * @var int
     */
    public $header = 0;

    /**
     * The raw userpage of the user.
     * @var string
     */
    public $page = '';

    /**
     * The raw signature of the user.
     * @var string
     */
    public $signature = '';

    /**
     * Whether the user's background should be displayed sitewide.
     * @var bool
     */
    public $backgroundSitewide = false;

    /**
     * The user's website url.
     * @var string
     */
    public $website = '';

    /**
     * The user's twitter handle.
     * @var string
     */
    public $twitter = '';

    /**
     * The user's github username.
     * @var string
     */
    public $github = '';

    /**
     * The user's skype username.
     * @var string
     */
    public $skype = '';

    /**
     * The user's discord tag.
     * @var string
     */
    public $discord = '';

    /**
     * The user's youtube channel id/name.
     * @var string
     */
    public $youtube = '';

    /**
     * The user's steam community username.
     * @var string
     */
    public $steam = '';

    /**
     * The user's osu! username.
     * @var string
     */
    public $osu = '';

    /**
     * The user's lastfm username.
     * @var string
     */
    public $lastfm = '';

    /**
     * The user's selected design.
     * @var string
     */
    private $design = '';

    /**
     * Title of the track this user last listened to.
     * @var string
     */
    public $musicTrack = '';

    /**
     * Artist of the track this user last listened to.
     * @var string
     */
    public $musicArtist = '';

    /**
     * Last time this was updated.
     * @var int
     */
    public $musicCheck = 0;

    /**
     * Whether the user is actively listening.
     * @var bool
     */
    public $musicListening = false;

    /**
     * Is this user active?
     * @var bool
     */
    public $activated = false;

    /**
     * Is this user verified?
     * @var bool
     */
    public $verified = false;

    /**
     * Is this user restricted?
     * @var bool
     */
    public $restricted = false;

    /**
     * The user's birthday.
     * @var string
     */
    private $birthday = '0000-00-00';

    /**
     * Holds the permission checker for this user.
     * @var UserPerms
     */
    public $perms;

    /**
     * The User instance cache array.
     * @var array
     */
    protected static $userCache = [];

    /**
     * Cached constructor.
     * @param int|string $uid
     * @param bool $forceRefresh
     * @return User
     */
    public static function construct($uid, $forceRefresh = false)
    {
        // Check if a user object isn't present in cache
        if ($forceRefresh || !array_key_exists($uid, self::$userCache)) {
            // If not create a new object and cache it
            self::$userCache[$uid] = new User($uid);
        }

        // Return the cached object
        return self::$userCache[$uid];
    }

    /**
     * Create a new user.
     * @param string $username
     * @param string $password
     * @param string $email
     * @param array $ranks
     * @return User
     */
    public static function create($username, $password, $email, $ranks = [2])
    {
        // Set a few variables
        $usernameClean = clean_string($username, true);
        $emailClean = clean_string($email, true);
        $password = password_hash($password, PASSWORD_BCRYPT);

        // Insert the user into the database and get the id
        $userId = DB::table('users')
            ->insertGetId([
                'username' => $username,
                'username_clean' => $usernameClean,
                'password' => $password,
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
     * The actual constructor.
     * @param int|string $userId
     */
    private function __construct($userId)
    {
        // Get the user database row
        $userRow = DB::table('users')
            ->where('user_id', $userId)
            ->orWhere('username_clean', clean_string($userId, true))
            ->first();

        // Populate the variables
        if ($userRow) {
            $this->id = intval($userRow->user_id);
            $this->username = $userRow->username;
            $this->usernameClean = $userRow->username_clean;
            $this->password = $userRow->password;
            $this->passwordChan = intval($userRow->password_chan);
            $this->email = $userRow->email;
            $this->mainRankId = intval($userRow->rank_main);
            $this->colour = $userRow->user_colour;
            $this->title = $userRow->user_title;
            $this->registered = intval($userRow->user_registered);
            $this->lastOnline = intval($userRow->user_last_online);
            $this->birthday = $userRow->user_birthday;
            $this->country = $userRow->user_country;
            $this->avatar = intval($userRow->user_avatar);
            $this->background = intval($userRow->user_background);
            $this->header = intval($userRow->user_header);
            $this->page = $userRow->user_page;
            $this->signature = $userRow->user_signature;
            $this->backgroundSitewide = boolval($userRow->user_background_sitewide);
            $this->website = $userRow->user_website;
            $this->twitter = $userRow->user_twitter;
            $this->github = $userRow->user_github;
            $this->skype = $userRow->user_skype;
            $this->discord = $userRow->user_discord;
            $this->youtube = $userRow->user_youtube;
            $this->steam = $userRow->user_steam;
            $this->osu = $userRow->user_osu;
            $this->lastfm = $userRow->user_lastfm;
            $this->design = $userRow->user_design;
            $this->musicTrack = $userRow->user_music_track;
            $this->musicArtist = $userRow->user_music_artist;
            $this->musicListening = boolval($userRow->user_music_listening);
            $this->musicCheck = intval($userRow->user_music_check);
            $this->activated = boolval($userRow->user_activated);
            $this->verified = boolval($userRow->user_verified);
            $this->restricted = boolval($userRow->user_restricted);

            // Temporary backwards compatible IP storage system
            try {
                $this->registerIp = Net::ntop($userRow->register_ip);
            } catch (NetAddressTypeException $e) {
                $this->registerIp = $userRow->register_ip;

                DB::table('users')
                    ->where('user_id', $this->id)
                    ->update([
                        'register_ip' => Net::pton($this->registerIp),
                    ]);
            }

            try {
                $this->lastIp = Net::ntop($userRow->last_ip);
            } catch (NetAddressTypeException $e) {
                $this->lastIp = $userRow->last_ip;

                DB::table('users')
                    ->where('user_id', $this->id)
                    ->update([
                        'last_ip' => Net::pton($this->lastIp),
                    ]);
            }
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
        $this->perms = new UserPerms($this);
    }

    /**
     * Get a Carbon object of the registration date.
     * @return Carbon
     */
    public function registerDate()
    {
        return Carbon::createFromTimestamp($this->registered);
    }

    /**
     * Get a Carbon object of the last online date.
     * @return Carbon
     */
    public function lastDate()
    {
        return Carbon::createFromTimestamp($this->lastOnline);
    }

    /**
     * Get the user's birthday.
     * @param bool $age
     * @return int|string
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
     * @param bool $long
     * @return string
     */
    public function country($long = false)
    {
        return $long ? get_country_name($this->country) : $this->country;
    }

    /**
     * Check if a user is online.
     * @return bool
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
        return $this->lastOnline > (time() - 120);
    }

    /**
     * Updates the last IP and online time of the user.
     */
    public function updateOnline()
    {
        $this->lastOnline = time();
        $this->lastIp = Net::ip();

        DB::table('users')
            ->where('user_id', $this->id)
            ->update([
                'user_last_online' => $this->lastOnline,
                'last_ip' => Net::pton($this->lastIp),
            ]);
    }

    /**
     * Runs some checks to see if this user is activated.
     * @return bool
     */
    public function isActive()
    {
        return $this->id !== 0 && $this->activated;
    }

    /**
     * Get a few forum statistics.
     * @return array
     */
    public function forumStats()
    {
        $posts = DB::table('posts')
            ->where('poster_id', $this->id)
            ->count();

        $topics = DB::table('posts')
            ->where('poster_id', $this->id)
            ->distinct()
            ->groupBy('topic_id')
            ->count();

        return [
            'posts' => $posts,
            'topics' => $topics,
        ];
    }

    /**
     * Add ranks to a user.
     * @param array $ranks
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
            $this->ranks[$rank] = Rank::construct($rank);

            DB::table('user_ranks')
                ->insert([
                    'rank_id' => $rank,
                    'user_id' => $this->id,
                ]);
        }
    }

    /**
     * Remove a set of ranks from a user.
     * @param array $ranks
     */
    public function removeRanks($ranks)
    {
        // Current ranks
        $remove = array_intersect(array_keys($this->ranks), $ranks);

        // Iterate over the ranks
        foreach ($remove as $rank) {
            unset($this->ranks[$rank]);

            DB::table('user_ranks')
                ->where('user_id', $this->id)
                ->where('rank_id', $rank)
                ->delete();
        }
    }

    /**
     * Change the main rank of a user.
     * @param int $rank
     */
    public function setMainRank($rank)
    {
        $this->mainRankId = $rank;
        $this->mainRank = $this->ranks[$rank];

        DB::table('users')
            ->where('user_id', $this->id)
            ->update([
                'rank_main' => $this->mainRankId,
            ]);
    }

    /**
     * Check if a user has a certain set of rank.
     * @param array $ranks
     * @return bool
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
     * @param int $uid
     */
    public function addFriend($uid)
    {
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
     * @param int $uid
     * @param bool $deleteRequest
     */
    public function removeFriend($uid, $deleteRequest = false)
    {
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
     * 0 = no, 1 = pending request, 2 = mutual.
     * @param int $with
     * @return int
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
     * @param int $level
     * @param bool $noObj
     * @return array
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
     * Get the comments from the user's profile.
     * @return array
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
     * Add premium in seconds.
     * @param int $seconds
     * @return int
     */
    public function addPremium($seconds)
    {
        // Check if there's already a record of premium for this user in the database
        $getUser = DB::table('premium')
            ->where('user_id', $this->id)
            ->first();

        // Calculate the (new) start and expiration timestamp
        $start = $getUser ? $getUser->premium_start : time();
        $expire = $getUser ? $getUser->premium_expire + $seconds : time() + $seconds;

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
     * @return int
     */
    public function isPremium()
    {
        // Get rank IDs from the db
        $premiumRank = (int) config('rank.premium');
        $defaultRank = (int) config('rank.regular');

        // Fetch expiration date
        $expire = $this->premiumInfo()->expire;

        // Check if the user has static premium
        if (!$expire) {
            $expire = time() + 1;
        }

        // Check if the user has premium and isn't in the premium rank
        if ($expire && !$this->hasRanks([$premiumRank])) {
            // Add the premium rank
            $this->addRanks([$premiumRank]);

            // Set it as default
            if ($this->mainRankId == $defaultRank) {
                $this->setMainRank($premiumRank);
            }
        } elseif (!$expire && $this->hasRanks([$premiumRank])) {
            $this->removeRanks([$premiumRank]);

            if ($this->mainRankId == $premiumRank) {
                $this->setMainRank($defaultRank);
            }
        }

        return $expire;
    }

    /**
     * Gets the start and end date of this user's premium tag.
     * @return stdClass
     */
    public function premiumInfo()
    {
        // Attempt to retrieve the premium record from the database
        $check = DB::table('premium')
            ->where('user_id', $this->id)
            ->where('premium_expire', '>', time())
            ->first();

        $return = new stdClass;

        $return->start = $check->premium_start ?? 0;
        $return->expire = $check->premium_expire ?? 0;

        return $return;
    }

    /**
     * Parse the user's userpage.
     * @return string
     */
    public function userPage()
    {
        return BBCode\Parser::toHTML(htmlentities($this->page), $this);
    }

    /**
     * Parse a user's signature.
     * @return string
     */
    public function signature()
    {
        return BBCode\Parser::toHTML(htmlentities($this->signature), $this);
    }

    /**
     * Get a user's username history.
     * @return array
     */
    public function getUsernameHistory()
    {
        return DB::table('username_history')
            ->where('user_id', $this->id)
            ->orderBy('change_id', 'desc')
            ->get();
    }

    /**
     * Alter the user's username.
     * @param string $username
     */
    public function setUsername($username)
    {
        $username_clean = clean_string($username, true);

        DB::table('username_history')
            ->insert([
                'change_time' => time(),
                'user_id' => $this->id,
                'username_new' => $username,
                'username_new_clean' => $username_clean,
                'username_old' => $this->username,
                'username_old_clean' => $this->usernameClean,
            ]);

        $this->username = $username;
        $this->usernameClean = $username_clean;

        DB::table('users')
            ->where('user_id', $this->id)
            ->update([
                'username' => $this->username,
                'username_clean' => $this->usernameClean,
            ]);
    }

    /**
     * Alter a user's e-mail address.
     * @param string $email
     */
    public function setMail($email)
    {
        $this->email = $email;

        DB::table('users')
            ->where('user_id', $this->id)
            ->update([
                'email' => $this->email,
            ]);
    }

    /**
     * Change the user's password.
     * @param string $password
     */
    public function setPassword($password)
    {
        // Create hash
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->passwordChan = time();

        // Update userrow
        DB::table('users')
            ->where('user_id', $this->id)
            ->update([
                'password' => $this->password,
                'password_chan' => $this->passwordChan,
            ]);
    }

    /**
     * Check if password expired.
     * @return bool
     */
    public function passwordExpired()
    {
        return strlen($this->password) < 1;
    }

    /**
     * Verify the user's password.
     * @param string $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Get all the notifications for this user.
     * @param int $timeDifference
     * @param bool $excludeRead
     * @return array
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
            $alerts[] = new Notification($alertId);
        }

        return $alerts;
    }

    /**
     * Invalidate all sessions related to this user.
     */
    public function purgeSessions()
    {
        DB::table('sessions')
            ->where('user_id', $this->id)
            ->delete();
    }

    /**
     * Get all a user's sessions
     * @return array
     */
    public function sessions()
    {
        $sessions = [];
        $ids = array_column(DB::table('sessions')
                ->where('user_id', $this->id)
                ->get(['session_id']), 'session_id');

        foreach ($ids as $id) {
            $sessions[$id] = new Session($id);
        }

        return $sessions;
    }

    /**
     * Gets the user's selected design.
     * @return string
     */
    public function design()
    {
        return Template::exists($this->design) ? $this->design : config('general.design');
    }

    /**
     * Gets the user's proper (highest) hierarchy.
     * @return int
     */
    public function hierarchy()
    {
        return DB::table('ranks')
            ->join('user_ranks', 'ranks.rank_id', '=', 'user_ranks.rank_id')
            ->where('user_id', $this->id)
            ->max('ranks.rank_hierarchy');
    }

    /**
     * Update last listened data.
     */
    public function updateLastTrack()
    {
        if (strlen($this->lastfm) < 1
            || $this->musicCheck + config('user.music_update') > time()) {
            return;
        }

        $lfm = new UserApi(
            new AuthApi('setsession', ['apiKey' => config('lastfm.api_key')])
        );

        try {
            $last = $lfm->getRecentTracks(['user' => $this->lastfm, 'limit' => '1']);
        } catch (LastFmApiExeption $e) {
            return;
        }

        if (count($last) < 1) {
            return;
        }

        $this->musicCheck = time();
        $this->musicListening = isset($last[0]['nowplaying']);
        $this->musicTrack = $last[0]['name'] ?? null;
        $this->musicArtist = $last[0]['artist']['name'] ?? null;

        DB::table('users')
            ->where('user_id', $this->id)
            ->update([
                'user_music_check' => $this->musicCheck,
                'user_music_listening' => $this->musicListening,
                'user_music_track' => $this->musicTrack,
                'user_music_artist' => $this->musicArtist,
            ]);
    }
}
