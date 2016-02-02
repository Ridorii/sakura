<?php
namespace Sakura;

use Sakura\Perms\Site;

/**
 * User management
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Users
{
    /**
     * Check if a user is logged in
     * 
     * @param int $uid The user ID.
     * @param string $sid The session ID.
     * 
     * @return array|bool Either false or the ID and session in an array.
     */
    public static function checkLogin($uid = null, $sid = null)
    {
        // Assign $uid and $sid
        $uid = $uid ? $uid : (isset($_COOKIE[Config::get('cookie_prefix') . 'id'])
            ? $_COOKIE[Config::get('cookie_prefix') . 'id']
            : 0);
        $sid = $sid ? $sid : (isset($_COOKIE[Config::get('cookie_prefix') . 'session'])
            ? $_COOKIE[Config::get('cookie_prefix') . 'session']
            : 0);

        // Get session
        $session = new Session($uid, $sid);

        // Validate the session
        $sessionValid = $session->validate();

        // Get user object
        $user = User::construct($uid);

        // Check if the session exists and check if the user is activated
        if ($sessionValid == 0 || $user->permission(Site::DEACTIVATED)) {
            // Unset User ID
            setcookie(
                Config::get('cookie_prefix') . 'id',
                0,
                time() - 60,
                Config::get('cookie_path')
            );

            // Unset Session ID
            setcookie(
                Config::get('cookie_prefix') . 'session',
                '',
                time() - 60,
                Config::get('cookie_path')
            );

            return false;
        }

        // Extend the cookie times if the remember flag is set
        if ($sessionValid == 2) {
            // User ID cookie
            setcookie(
                Config::get('cookie_prefix') . 'id',
                $uid,
                time() + 604800,
                Config::get('cookie_path')
            );

            // Session ID cookie
            setcookie(
                Config::get('cookie_prefix') . 'session',
                $sid,
                time() + 604800,
                Config::get('cookie_path')
            );
        }

        // Update last online
        Database::update('users', [
            [
                'user_last_online' => time(),
            ],
            [
                'user_id' => [$uid, '='],
            ],
        ]);

        // Update the premium meta
        self::updatePremiumMeta($uid);

        // If everything went through return true
        return [$uid, $sid];
    }

    /**
     * Log in to an account.
     * 
     * @param string $username The username.
     * @param string $password The password.
     * @param bool $remember Stay logged in "forever"?
     * @param bool $cookies Set cookies?
     * 
     * @return array Return the status.
     */
    public static function login($username, $password, $remember = false, $cookies = true)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Check if we haven't hit the rate limit
        $rates = Database::fetch('login_attempts', true, [
            'attempt_ip' => [Utils::getRemoteIP(), '='],
            'attempt_timestamp' => [time() - 1800, '>'],
            'attempt_success' => [0, '='],
        ]);

        if (count($rates) > 4) {
            return [0, 'RATE_LIMIT'];
        }

        // Check if the user that's trying to log in actually exists
        if (!$uid = self::userExists($username, false)) {
            return [0, 'USER_NOT_EXIST'];
        }

        // Get account data
        $user = User::construct($uid);

        // Validate password
        switch ($user->passwordAlgo) {
            // Disabled
            case 'disabled':
                return [0, 'NO_LOGIN'];

            // Default hashing method
            default:
                if (!Hashing::validatePassword($password, [
                    $user->passwordAlgo,
                    $user->passwordIter,
                    $user->passwordSalt,
                    $user->passwordHash,
                ])) {
                    return [0, 'INCORRECT_PASSWORD', $user->id, $user->passwordChan];
                }
        }

        // Check if the user has the required privs to log in
        if ($user->permission(Site::DEACTIVATED)) {
            return [0, 'NOT_ALLOWED', $user->id];
        }

        // Create a new session
        $session = new Session($user->id);

        // Generate a session key
        $sessionKey = $session->create($remember);

        // Set cookies
        if ($cookies) {
            // User ID cookie
            setcookie(
                Config::get('cookie_prefix') . 'id',
                $user->id,
                time() + 604800,
                Config::get('cookie_path')
            );

            // Session ID cookie
            setcookie(
                Config::get('cookie_prefix') . 'session',
                $sessionKey,
                time() + 604800,
                Config::get('cookie_path')
            );
        }

        // Successful login! (also has a thing for the legacy password system)
        return [1, 'LOGIN_SUCCESS', $user->id];
    }

    /**
     * Logout
     * 
     * @return bool Was the logout successful?
     */
    public static function logout()
    {
        // Check if user is logged in
        if (!$check = self::checkLogin()) {
            return false;
        }

        // Destroy the active session
        (new Session($check[0], $check[1]))->destroy();

        // Unset User ID
        setcookie(
            Config::get('cookie_prefix') . 'id',
            0,
            time() - 60,
            Config::get('cookie_path')
        );

        // Unset Session ID
        setcookie(
            Config::get('cookie_prefix') . 'session',
            '',
            time() - 60,
            Config::get('cookie_path')
        );

        // Return true indicating a successful logout
        return true;
    }

    /**
     * Register a new account.
     * 
     * @param string $username The username.
     * @param string $password The password.
     * @param string $confirmpass The password, again.
     * @param string $email The e-mail.
     * @param bool $tos Agreeing to the ToS.
     * @param string $captcha Captcha.
     * @param string $regkey Registration key (unused).
     * 
     * @return array Status.
     */
    public static function register($username, $password, $confirmpass, $email, $tos, $captcha = null, $regkey = null)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Check if registration is even enabled
        if (Config::get('disable_registration')) {
            return [0, 'DISABLED'];
        }

        // Check if the user agreed to the ToS
        if (!$tos) {
            return [0, 'TOS'];
        }

        // Verify the captcha if it's enabled
        if (Config::get('recaptcha')) {
            if (!Utils::verifyCaptcha($captcha)['success']) {
                return [0, 'CAPTCHA_FAIL'];
            }
        }

        // Check if the username already exists
        if (self::userExists($username, false)) {
            return [0, 'USER_EXISTS'];
        }

        // Username too short
        if (strlen($username) < Config::get('username_min_length')) {
            return [0, 'NAME_TOO_SHORT'];
        }

        // Username too long
        if (strlen($username) > Config::get('username_max_length')) {
            return [0, 'NAME_TOO_LONG'];
        }

        // Check if the given email address is formatted properly
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [0, 'INVALID_EMAIL'];
        }

        // Check the MX record of the email
        if (!Utils::checkMXRecord($email)) {
            return [0, 'INVALID_MX'];
        }

        // Check if the e-mail has already been used
        if (Database::count('users', ['email' => [$email, '=']])[0] > 0) {
            return [0, 'EMAIL_EXISTS'];
        }

        // Check password entropy
        if (Utils::pwdEntropy($password) < Config::get('min_entropy')) {
            return [0, 'PASS_TOO_SHIT'];
        }

        // Passwords do not match
        if ($password != $confirmpass) {
            return [0, 'PASS_NOT_MATCH'];
        }

        // Set a few variables
        $requireActive = Config::get('require_activation');
        $ranks = $requireActive ? [1] : [2];

        // Create the user
        $user = User::create($username, $password, $email, $ranks);

        // Check if we require e-mail activation
        if ($requireActive) {
            // Send activation e-mail to user
            self::sendActivationMail($user->id);
        }

        // Return true with a specific message if needed
        return [1, ($requireActive ? 'EMAILSENT' : 'SUCCESS')];
    }

    /**
     * Send password forgot e-mail
     * 
     * @param string $username The username.
     * @param string $email The e-mail.
     * 
     * @return array The status.
     */
    public static function sendPasswordForgot($username, $email)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Clean username string
        $usernameClean = Utils::cleanString($username, true);
        $emailClean = Utils::cleanString($email, true);

        // Do database request
        $user = Database::fetch('users', false, [
            'username_clean' => [$usernameClean, '='],
            'email' => [$emailClean, '='],
        ]);

        // Check if user exists
        if (count($user) < 2) {
            return [0, 'USER_NOT_EXIST'];
        }

        // Create user object
        $userObj = User::construct($user['user_id']);

        // Check if the user has the required privs to log in
        if ($userObj->permission(Site::DEACTIVATED)) {
            return [0, 'NOT_ALLOWED'];
        }

        // Generate the verification key
        $verk = ActionCode::generate('LOST_PASS', $userObj->id);

        // Create new urls object
        $urls = new Urls();

        // Build the e-mail
        $message = "Hello " . $user['username'] . ",\r\n\r\n";
        $message .= "You are receiving this notification because you have (or someone pretending to be you has) requested a password reset link to be sent for your account on \"" . Config::get('sitename') . "\". If you did not request this notification then please ignore it, if you keep receiving it please contact the site administrator.\r\n\r\n";
        $message .= "To use this password reset key you need to go to a special page. To do this click the link provided below.\r\n\r\n";
        $message .= "http://" . Config::get('url_main') . $urls->format('SITE_FORGOT_PASSWORD') . "?pw=true&uid=" . $user['user_id'] . "&key=" . $verk . "\r\n\r\n";
        $message .= "If successful you should be able to change your password here.\r\n\r\n";
        $message .= "Alternatively if the above method fails for some reason you can go to http://" . Config::get('url_main') . $urls->format('SITE_FORGOT_PASSWORD') . "?pw=true&uid=" . $user['user_id'] . " and use the key listed below:\r\n\r\n";
        $message .= "Verification key: " . $verk . "\r\n\r\n";
        $message .= "You can of course change this password yourself via the profile page. If you have any difficulties please contact the site administrator.\r\n\r\n";
        $message .= "--\r\n\r\nThanks\r\n\r\n" . Config::get('mail_signature');

        // Send the message
        Utils::sendMail([$user['email'] => $user['username']], Config::get('sitename') . ' password restoration', $message);

        // Return success
        return [1, 'SUCCESS'];
    }

    /**
     * Reset a password.
     * 
     * @param string $verk The e-mail verification key.
     * @param int $uid The user id.
     * @param string $newpass New pass.
     * @param string $verpass Again.
     * 
     * @return array Status.
     */
    public static function resetPassword($verk, $uid, $newpass, $verpass)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Check password entropy
        if (Utils::pwdEntropy($newpass) < Config::get('min_entropy')) {
            return [0, 'PASS_TOO_SHIT'];
        }

        // Passwords do not match
        if ($newpass != $verpass) {
            return [0, 'PASS_NOT_MATCH'];
        }

        // Check the verification key
        $action = ActionCode::validate('LOST_PASS', $verk, $uid);

        // Check if we got a negative return
        if (!$action) {
            return [0, 'INVALID_CODE'];
        }

        // Hash the password
        $password = Hashing::createHash($newpass);
        $time = time();

        // Update the user
        Database::update('users', [
            [
                'password_hash' => $password[3],
                'password_salt' => $password[2],
                'password_algo' => $password[0],
                'password_iter' => $password[1],
                'password_chan' => $time,
            ],
            [
                'user_id' => [$uid, '='],
            ],
        ]);

        // Return success
        return [1, 'SUCCESS'];
    }

    /**
     * Resend activation e-mail.
     * 
     * @param string $username Username.
     * @param string $email E-mail.
     * 
     * @return array Status
     */
    public static function resendActivationMail($username, $email)
    {
        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            return [0, 'AUTH_LOCKED'];
        }

        // Clean username string
        $usernameClean = Utils::cleanString($username, true);
        $emailClean = Utils::cleanString($email, true);

        // Do database request
        $user = Database::fetch('users', false, [
            'username_clean' => [$usernameClean, '='],
            'email' => [$emailClean, '='],
        ]);

        // Check if user exists
        if (count($user) < 2) {
            return [0, 'USER_NOT_EXIST'];
        }

        $userObj = User::construct($user['user_id']);

        // Check if a user is activated
        if (!$userObj->permission(Site::DEACTIVATED)) {
            return [0, 'USER_ALREADY_ACTIVE'];
        }

        // Send activation e-mail
        self::sendActivationMail($user['user_id']);

        // Return success
        return [1, 'SUCCESS'];
    }

    /**
     * Send activation e-mail.
     * 
     * @param mixed $uid User ID.
     * @param mixed $customKey Key.
     * 
     * @return bool Always true.
     */
    public static function sendActivationMail($uid, $customKey = null)
    {

        // Get the user data
        $user = User::construct($uid);

        // User is already activated or doesn't even exist
        if (!$user->id || !$user->permission(Site::DEACTIVATED)) {
            return false;
        }

        // Generate activation key
        $activate = ActionCode::generate('ACTIVATE', $user->id);

        // Create new urls object
        $urls = new Urls();

        // Build the e-mail
        $message = "Welcome to " . Config::get('sitename') . "!\r\n\r\n";
        $message .= "Please keep this e-mail for your records. Your account intormation is as follows:\r\n\r\n";
        $message .= "----------------------------\r\n\r\n";
        $message .= "Username: " . $user->username . "\r\n\r\n";
        $message .= "Your profile: http://" . Config::get('url_main') . $urls->format('USER_PROFILE', [$user->id]) . "\r\n\r\n";
        $message .= "----------------------------\r\n\r\n";
        $message .= "Please visit the following link in order to activate your account:\r\n\r\n";
        $message .= "http://" . Config::get('url_main') . $urls->format('SITE_ACTIVATE') . "?mode=activate&u=" . $user->id . "&k=" . $activate . "\r\n\r\n";
        $message .= "Your password has been securely stored in our database and cannot be retrieved. ";
        $message .= "In the event that it is forgotten, you will be able to reset it using the email address associated with your account.\r\n\r\n";
        $message .= "Thank you for registering.\r\n\r\n";
        $message .= "--\r\n\r\nThanks\r\n\r\n" . Config::get('mail_signature');

        // Send the message
        Utils::sendMail(
            [
                $user->email => $user->username,
            ],
            Config::get('sitename') . ' Activation Mail',
            $message
        );

        // Return true indicating that the things have been sent
        return true;
    }

    /**
     * Activate a user.
     * 
     * @param int $uid The ID.
     * @param bool $requireKey Require a key.
     * @param string $key The key.
     * 
     * @return array Status.
     */
    public static function activateUser($uid, $requireKey = false, $key = null)
    {
        // Get the user data
        $user = User::construct($uid);

        // Check if user exists
        if (!$user->id) {
            return [0, 'USER_NOT_EXIST'];
        }

        // Check if user is already activated
        if (!$user->permission(Site::DEACTIVATED)) {
            return [0, 'USER_ALREADY_ACTIVE'];
        }

        // Check if a key is set
        if ($requireKey) {
            // Check the action code
            $action = ActionCode::validate('ACTIVATE', $key, $user->id);

            // Check if we got a negative return
            if (!$action) {
                return [0, 'INVALID_CODE'];
            }
        }
        
        // Add normal user, remove deactivated and set normal as default
        $user->addRanks([2]);
        $user->removeRanks([1]);
        $user->setMainRank(2);

        // Return success
        return [1, 'SUCCESS'];
    }

    /**
     * Check if a user exists.
     * 
     * @param mixed $user The Username or ID.
     * @param bool $id Use id instead.
     * 
     * @return mixed Returns the ID if it exists, false otherwise.
     */
    public static function userExists($user, $id = true)
    {
        // Clean string
        $user = Utils::cleanString($user, true);

        // Do database request
        $user = Database::fetch('users', true, [($id ? 'user_id' : 'username_clean') => [$user, '=']]);

        // Return count (which would return 0, aka false, if nothing was found)
        return count($user) ? $user[0]['user_id'] : false;
    }

    /**
     * Get all available profile fields.
     * 
     * @return array|null The fields.
     */
    public static function getProfileFields()
    {
        // Get profile fields
        $profileFields = Database::fetch('profilefields');

        // If there's nothing just return null
        if (!count($profileFields)) {
            return null;
        }

        // Create output array
        $fields = [];

        // Iterate over the fields and clean them up
        foreach ($profileFields as $field) {
            $fields[$field['field_id']] = $field;
            $fields[$field['field_id']]['field_identity'] = Utils::cleanString($field['field_name'], true, true);
            $fields[$field['field_id']]['field_additional'] = json_decode($field['field_additional'], true);
        }

        // Return the yeahs
        return $fields;
    }

    /**
     * Get all available option fields.
     * 
     * @return array|null The fields.
     */
    public static function getOptionFields()
    {
        // Get option fields
        $optionFields = Database::fetch('optionfields');

        // If there's nothing just return null
        if (!count($optionFields)) {
            return null;
        }

        // Create output array
        $fields = [];

        $user = User::construct(self::checkLogin()[0]);

        // Iterate over the fields and clean them up
        foreach ($optionFields as $field) {
            if (!$user->permission(constant('Sakura\Perms\Site::' . $field['option_permission']))) {
                continue;
            }

            $fields[$field['option_id']] = $field;
        }

        // Return the yeahs
        return $fields;
    }

    /**
     * Get all online users.
     * 
     * @return array Array containing User instances.
     */
    public static function checkAllOnline()
    {
        // Assign time - 500 to a variable
        $time = time() - Config::get('max_online_time');

        $return = [];

        // Get all online users in the past 5 minutes
        $getAll = Database::fetch('users', true, ['user_last_online' => [$time, '>']]);

        foreach ($getAll as $user) {
            $return[] = User::construct($user['user_id']);
        }

        // Return all the online users
        return $return;
    }

    /**
     * Add premium time to a user.
     * 
     * @param int $id The user ID.
     * @param int $seconds The amount of extra seconds.
     * 
     * @return array|double|int The new expiry date.
     */
    public static function addUserPremium($id, $seconds)
    {
        // Check if there's already a record of premium for this user in the database
        $getUser = Database::fetch('premium', false, [
            'user_id' => [$id, '='],
        ]);

        // Calculate the (new) start and expiration timestamp
        $start = isset($getUser['premium_start']) ? $getUser['premium_start'] : time();
        $expire = isset($getUser['premium_expire']) ? $getUser['premium_expire'] + $seconds : time() + $seconds;

        // If the user already exists do an update call, otherwise an insert call
        if (empty($getUser)) {
            Database::insert('premium', [
                'user_id' => $id,
                'premium_start' => $start,
                'premium_expire' => $expire,
            ]);
        } else {
            Database::update('premium', [
                [
                    'premium_expire' => $expire,
                ],
                [
                    'user_id' => [$id, '='],
                ],
            ]);
        }

        // Return the expiration timestamp
        return $expire;
    }

    /**
     * Process premium meta data.
     * 
     * @param int $id The user ID.
     */
    public static function updatePremiumMeta($id)
    {
        // Get the ID for the premium user rank from the database
        $premiumRank = Config::get('premium_rank_id');
        $excepted = Config::get('restricted_rank_id');

        // Create user object
        $user = User::construct($id);

        // Run the check
        $check = $user->isPremium();

        // Check if the user has premium
        if ($check[0] && !array_key_exists($excepted, $user->ranks)) {
            // If so add the rank to them
            $user->addRanks([$premiumRank]);

            // Check if the user's default rank is standard user and update it to premium
            if ($user->mainRankId == 2) {
                $user->setMainRank($premiumRank);
            }
        } elseif (!$check[0]) {
            // Remove the expired entry
            Database::delete('premium', [
                'user_id' => [$user->id, '='],
            ]);

            // Else remove the rank from them
            $user->removeRanks([$premiumRank]);
        }
    }

    /**
     * Get all users that registered from a certain IP.
     * 
     * @param string $ip The IP. 
     * 
     * @return array The users.
     */
    public static function getUsersByIP($ip)
    {
        // Get users by registration IP
        $registeredFrom = Database::fetch('users', true, ['register_ip' => [$ip, '=']]);

        // Get users by last IP
        $lastFrom = Database::fetch('users', true, ['last_ip' => [$ip, '='], 'register_ip' => [$ip, '!=']]);

        // Merge the arrays
        $users = array_merge($registeredFrom, $lastFrom);

        // Return the array with users
        return $users;
    }

    /**
     * Get users from a rank.
     * 
     * @param int $rankId The rank ID.
     * @param mixed $users Array with users.
     * @param mixed $excludeAbyss Unused.
     * 
     * @return array Users.
     */
    public static function getUsersInRank($rankId, $users = null, $excludeAbyss = true)
    {
        // Get all users (or use the supplied user list to keep server load down)
        if (!$users) {
            $users = self::getAllUsers();
        }

        // Make output array
        $rank = [];

        // Go over all users and check if they have the rank id
        foreach ($users as $user) {
            // If so store the user's row in the array
            if ($user->hasRanks([$rankId], $user->id)) {
                $rank[] = $user;
            }
        }

        // Then return the array with the user rows
        return $rank;
    }

    /**
     * Get all users.
     * 
     * @param mixed $includeInactive include deactivated users.
     * @param mixed $includeRestricted include restricted users.
     * 
     * @return array The users.
     */
    public static function getAllUsers($includeInactive = true, $includeRestricted = false)
    {
        // Execute query
        $getUsers = Database::fetch('users', true);

        // Define variable
        $users = [];

        // Reorder shit
        foreach ($getUsers as $user) {
            $user = User::construct($user['user_id']);

            // Skip if inactive and not include deactivated users
            if (!$includeInactive && $user->permission(Site::DEACTIVATED)) {
                continue;
            }

            // Skip if inactive and not include restricted users
            if (!$includeRestricted && $user->permission(Site::RESTRICTED)) {
                continue;
            }

            $users[$user->id] = $user;
        }

        // and return an array with the users
        return $users;
    }

    /**
     * Get all ranks.
     * 
     * @return array All ranks.
     */
    public static function getAllRanks()
    {
        // Execute query
        $getRanks = Database::fetch('ranks', true);

        // Define variable
        $ranks = [];

        // Reorder shit
        foreach ($getRanks as $rank) {
            $ranks[$rank['rank_id']] = Rank::construct($rank['rank_id']);
        }

        // and return an array with the ranks
        return $ranks;
    }

    /**
     * Get a user's notifications.
     * 
     * @param int $uid The user id.
     * @param int $timediff The maximum difference in time.
     * @param bool $excludeRead Exclude notifications that were already read.
     * @param bool $markRead Automatically mark as read.
     * 
     * @return array The notifications.
     */
    public static function getNotifications($uid = null, $timediff = 0, $excludeRead = true, $markRead = false)
    {
        // Prepare conditions
        $conditions = [];
        $conditions['user_id'] = [($uid ? $uid : self::checkLogin()[0]), '='];

        if ($timediff) {
            $conditions['alert_timestamp'] = [time() - $timediff, '>'];
        }

        if ($excludeRead) {
            $conditions['alert_read'] = [0, '='];
        }

        // Get notifications for the database
        $notifications = Database::fetch('notifications', true, $conditions);

        // Mark the notifications as read
        if ($markRead) {
            // Iterate over all entries
            foreach ($notifications as $notification) {
                // If the notifcation is already read skip
                if ($notification['alert_read']) {
                    continue;
                }

                // Mark them as read
                self::markNotificationRead($notification['alert_id']);
            }
        }

        // Return the notifications
        return $notifications;
    }

    /**
     * Mark a notification as read
     * 
     * @param mixed $id The notification's ID. 
     * @param mixed $mode Read or unread.
     */
    public static function markNotificationRead($id, $mode = true)
    {
        // Execute an update statement
        Database::update('notifications', [
            [
                'alert_read' => ($mode ? 1 : 0),
            ],
            [
                'alert_id' => [$id, '='],
            ],
        ]);
    }

    /**
     * Create a new notification.
     * 
     * @param int $user The user id.
     * @param string $title The notification title.
     * @param string $text The rest of the text.
     * @param int $timeout After how many seconds the notification should disappear.
     * @param string $img The image.
     * @param string $link The link.
     * @param int $sound Whether it should play a noise.
     */
    public static function createNotification($user, $title, $text, $timeout = 60000, $img = 'FONT:fa-info-circle', $link = '', $sound = 0)
    {
        // Get current timestamp
        $time = time();

        // Insert it into the database
        Database::insert('notifications', [
            'user_id' => $user,
            'alert_timestamp' => $time,
            'alert_read' => 0,
            'alert_sound' => ($sound ? 1 : 0),
            'alert_title' => $title,
            'alert_text' => $text,
            'alert_link' => $link,
            'alert_img' => $img,
            'alert_timeout' => $timeout,
        ]);
    }

    /**
     * Get the newest member's ID.
     * 
     * @return int The user ID.
     */
    public static function getNewestUserId()
    {
        return Database::fetch('users', false, ['rank_main' => [Config::get('restricted_rank_id'), '!=']], ['user_id', true], ['1'])['user_id'];
    }
}
