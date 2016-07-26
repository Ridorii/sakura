<?php
/**
 * Holds the auth controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\ActionCode;
use Sakura\ActiveUser;
use Sakura\Config;
use Sakura\DB;
use Sakura\Hashing;
use Sakura\Net;
use Sakura\Perms\Site;
use Sakura\Router;
use Sakura\Session;
use Sakura\Template;
use Sakura\User;

/**
 * Authentication controllers.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AuthController extends Controller
{
    /**
     * Touch the login rate limit.
     *
     * @param $user int The ID of the user that attempted to log in.
     * @param $sucess bool Whether the login attempt was successful.
     */
    protected function touchRateLimit($user, $success = false)
    {
        DB::table('login_attempts')
            ->insert([
                'attempt_success' => $success ? 1 : 0,
                'attempt_timestamp' => time(),
                'attempt_ip' => Net::pton(Net::ip()),
                'user_id' => $user,
            ]);
    }

    /**
     * End the current session.
     *
     * @return string
     */
    public function logout()
    {
        if (!ActiveUser::$session->validate()
            || !isset($_REQUEST['s'])
            || $_REQUEST['s'] != session_id()) {
            $message = 'Something happened! This probably happened because you went here without being logged in.';
            $redirect = (isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : Router::route('main.index'));

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Destroy the active session
        ActiveUser::$session->destroy();

        // Return true indicating a successful logout
        $message = 'Goodbye!';
        $redirect = Router::route('auth.login');

        Template::vars(compact('message', 'redirect'));

        return Template::render('global/information');
    }

    /**
     * Get the login page.
     *
     * @return string
     */
    public function loginGet()
    {
        return Template::render('auth/login');
    }

    /**
     * Do a login attempt.
     *
     * @return string
     */
    public function loginPost()
    {
        // Preliminarily set login to failed
        $redirect = Router::route('auth.login');

        // Get request variables
        $username = isset($_REQUEST['username']) ? $_REQUEST['username'] : null;
        $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : null;
        $remember = isset($_REQUEST['remember']);

        // Check if we haven't hit the rate limit
        $rates = DB::table('login_attempts')
            ->where('attempt_ip', Net::pton(Net::ip()))
            ->where('attempt_timestamp', '>', time() - 1800)
            ->where('attempt_success', '0')
            ->count();

        if ($rates > 4) {
            $message = 'Your have hit the login rate limit, try again later.';
            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Get account data
        $user = User::construct(clean_string($username, true, true));

        // Check if the user that's trying to log in actually exists
        if ($user->id === 0) {
            $this->touchRateLimit($user->id);
            $message = 'The user you tried to log into does not exist.';
            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Validate password
        switch ($user->passwordAlgo) {
            // Disabled
            case 'disabled':
                $this->touchRateLimit($user->id);
                $message = 'Logging into this account is disabled.';
                Template::vars(compact('message', 'redirect'));

                return Template::render('global/information');

            // Default hashing method
            default:
                if (!Hashing::validatePassword($password, [
                    $user->passwordAlgo,
                    $user->passwordIter,
                    $user->passwordSalt,
                    $user->passwordHash,
                ])) {
                    $this->touchRateLimit($user->id);
                    $message = 'The password you entered was invalid.';
                    Template::vars(compact('message', 'redirect'));

                    return Template::render('global/information');
                }
        }

        // Check if the user has the required privs to log in
        if ($user->permission(Site::DEACTIVATED)) {
            $this->touchRateLimit($user->id);
            $message = 'Your account is deactivated, activate it first!';
            $redirect = Router::route('auth.reactivate');
            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Create a new session
        $session = new Session($user->id);

        // Generate a session key
        $sessionKey = $session->create($remember);

        $cookiePrefix = config('cookie.prefix');

        // User ID cookie
        setcookie(
            "{$cookiePrefix}id",
            $user->id,
            time() + 604800
        );

        // Session ID cookie
        setcookie(
            "{$cookiePrefix}session",
            $sessionKey,
            time() + 604800
        );

        $this->touchRateLimit($user->id, true);

        $redirect = $user->lastOnline
        ? (isset($_REQUEST['redirect'])
            ? $_REQUEST['redirect']
            : Router::route('main.index'))
        : Router::route('main.infopage', 'welcome');

        $message = 'Welcome' . ($user->lastOnline ? ' back' : '') . '!';

        Template::vars(compact('message', 'redirect'));

        return Template::render('global/information');
    }

    /**
     * Get the registration page.
     *
     * @return string
     */
    public function registerGet()
    {
        // Attempt to check if a user has already registered from the current IP
        $getUserIP = DB::table('users')
            ->where('register_ip', Net::pton(Net::ip()))
            ->orWhere('last_ip', Net::pton(Net::ip()))
            ->get();

        if ($getUserIP) {
            Template::vars([
                'haltRegistration' => count($getUserIP) > 1,
                'haltName' => $getUserIP[array_rand($getUserIP)]->username,
            ]);
        }

        return Template::render('auth/register');
    }

    /**
     * Do a registration attempt.
     *
     * @return string
     */
    public function registerPost()
    {
        // Preliminarily set registration to failed
        $redirect = Router::route('auth.register');

        // Check if authentication is disallowed
        if (config('user.disable_registration')) {
            $message = 'Registration is disabled for security checkups! Try again later.';

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Check if authentication is disallowed
        if (!isset($_POST['session']) || $_POST['session'] != session_id()) {
            $message = "Your session expired, refreshing the page will most likely fix this!";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Grab forms
        $username = isset($_POST['username']) ? $_POST['username'] : null;
        $password = isset($_POST['password']) ? $_POST['password'] : null;
        $email = isset($_POST['email']) ? $_POST['email'] : null;

        // Append username and email to the redirection url
        $redirect .= "?username={$username}&email={$email}";

        // Attempt to get account data
        $user = User::construct(clean_string($username, true, true));

        // Check if the username already exists
        if ($user && $user->id !== 0) {
            $message = "{$user->username} is already a member here!"
                . " If this is you please use the password reset form instead of making a new account.";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Username too short
        if (strlen($username) < config('user.name_min')) {
            $message = 'Your name must be at least 3 characters long.';

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Username too long
        if (strlen($username) > config('user.name_max')) {
            $message = 'Your name can\'t be longer than 16 characters.';

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Check if the given email address is formatted properly
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Your e-mail address is formatted incorrectly.';

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Check the MX record of the email
        if (!check_mx_record($email)) {
            $message = 'No valid MX-Record found on the e-mail address you supplied.';

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Check if the e-mail has already been used
        $emailCheck = DB::table('users')
            ->where('email', $email)
            ->count();
        if ($emailCheck) {
            $message = 'Someone already registered using this email!';

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Check password entropy
        if (password_entropy($password) < config('user.pass_min_entropy')) {
            $message = 'Your password is too weak, try adding some special characters.';

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Set a few variables
        $requireActive = config('user.require_activation');
        $ranks = $requireActive ? [config('rank.inactive')] : [config('rank.regular')];

        // Create the user
        $user = User::create($username, $password, $email, $ranks);

        // Check if we require e-mail activation
        if ($requireActive) {
            // Send activation e-mail to user
            $this->sendActivationMail($user);
        }

        // Return true with a specific message if needed
        $redirect = Router::route('auth.login');
        $message = $requireActive
        ? 'Your registration went through! An activation e-mail has been sent.'
        : 'Your registration went through! Welcome to ' . config('general.name') . '!';

        Template::vars(compact('message', 'redirect'));

        return Template::render('global/information');
    }

    /**
     * Do a activation attempt.
     *
     * @return string
     */
    public function activate()
    {
        // Preliminarily set activation to failed
        $redirect = Router::route('main.index');

        // Attempt to get the required GET parameters
        $userId = isset($_GET['u']) ? $_GET['u'] : 0;
        $key = isset($_GET['k']) ? $_GET['k'] : "";

        // Attempt to create a user object
        $user = User::construct($userId);

        // Quit if the user ID is 0
        if ($user->id === 0) {
            $message = "This user does not exist! Contact us if you think this isn't right.";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Check if the user is already active
        if (!$user->permission(Site::DEACTIVATED)) {
            $message = "Your account is already activated! Why are you here?";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Validate the activation key
        $action = ActionCode::validate('ACTIVATE', $key, $user->id);

        if (!$action) {
            $message = "Invalid activation code! Contact us if you think this isn't right.";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Get the ids for deactivated and default user ranks
        $rankDefault = config('rank.regular');
        $rankDeactive = config('rank.inactive');

        // Add normal user, remove deactivated and set normal as default
        $user->addRanks([$rankDefault]);
        $user->setMainRank($rankDefault);
        $user->removeRanks([$rankDeactive]);

        $redirect = Router::route('auth.login');
        $message = "Your account is activated, welcome to " . config('general.name') . "!";

        Template::vars(compact('message', 'redirect'));

        return Template::render('global/information');
    }

    /**
     * Get the reactivation request form.
     *
     * @return string
     */
    public function reactivateGet()
    {
        return Template::render('auth/reactivate');
    }

    /**
     * Do a reactivation preparation attempt.
     *
     * @return string
     */
    public function reactivatePost()
    {
        // Preliminarily set registration to failed
        $redirect = Router::route('auth.reactivate');

        // Validate session
        if (!isset($_POST['session']) || $_POST['session'] != session_id()) {
            $message = "Your session expired, refreshing the page will most likely fix this!";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Grab forms
        $username = isset($_POST['username']) ? clean_string($_POST['username'], true) : null;
        $email = isset($_POST['email']) ? clean_string($_POST['email'], true) : null;

        // Do database request
        $getUser = DB::table('users')
            ->where('username_clean', $username)
            ->where('email', $email)
            ->get(['user_id']);

        // Check if user exists
        if (!$getUser) {
            $message = "User not found! Double check your username and e-mail address!";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Create user object
        $user = User::construct($getUser[0]->user_id);

        // Check if a user is activated
        if (!$user->permission(Site::DEACTIVATED)) {
            $message = "Your account is already activated! Why are you here?";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Send activation e-mail to user
        $this->sendActivationMail($user);

        $redirect = Router::route('auth.login');
        $message = "Sent the e-mail! Make sure to check your spam folder as well!";

        Template::vars(compact('message', 'redirect'));

        return Template::render('global/information');
    }

    /**
     * Get the password reset forum.
     *
     * @return string
     */
    public function resetPasswordGet()
    {
        return Template::render('auth/resetpassword');
    }

    /**
     * Do a password reset attempt.
     *
     * @return string
     */
    public function resetPasswordPost()
    {
        // Preliminarily set action to failed
        $redirect = Router::route('main.index');

        // Validate session
        if (!isset($_POST['session']) || $_POST['session'] != session_id()) {
            $message = "Your session expired, refreshing the page will most likely fix this!";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Attempt to get the various required GET parameters
        $userId = isset($_POST['user']) ? $_POST['user'] : 0;
        $key = isset($_POST['key']) ? $_POST['key'] : "";
        $password = isset($_POST['password']) ? $_POST['password'] : "";
        $userName = isset($_POST['username']) ? clean_string($_POST['username'], true) : "";
        $email = isset($_POST['email']) ? clean_string($_POST['email'], true) : null;

        // Create user object
        $user = User::construct($userId ? $userId : $userName);

        // Quit if the user ID is 0
        if ($user->id === 0 || ($email !== null ? $email !== $user->email : false)) {
            $message = "This user does not exist! Contact us if you think this isn't right.";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Check if the user is active
        if ($user->permission(Site::DEACTIVATED)) {
            $message = "Your account is deactivated, go activate it first...";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        if ($key && $password) {
            // Check password entropy
            if (password_entropy($password) < config('user.pass_min_entropy')) {
                $message = "Your password doesn't meet the strength requirements!";

                Template::vars(compact('message', 'redirect'));

                return Template::render('global/information');
            }

            // Validate the activation key
            $action = ActionCode::validate('LOST_PASS', $key, $user->id);

            if (!$action) {
                $message = "Invalid verification code! Contact us if you think this isn't right.";

                Template::vars(compact('message', 'redirect'));

                return Template::render('global/information');
            }

            // Hash the password
            $pw = Hashing::createHash($password);

            // Update the user
            DB::table('users')
                ->where('user_id', $user->id)
                ->update([
                    'password_hash' => $pw[3],
                    'password_salt' => $pw[2],
                    'password_algo' => $pw[0],
                    'password_iter' => $pw[1],
                    'password_chan' => time(),
                ]);

            $message = "Changed your password! You may now log in.";
            $redirect = Router::route('auth.login');
        } else {
            // Send the e-mail
            $this->sendPasswordMail($user);

            $message = "Sent the e-mail, keep an eye on your spam folder as well!";
            $redirect = Router::route('main.index');
        }

        Template::vars(compact('message', 'redirect'));

        return Template::render('global/information');
    }

    /**
     * Send the activation e-mail
     *
     * @param User $user
     */
    private function sendActivationMail($user)
    {
        // Generate activation key
        $activate = ActionCode::generate('ACTIVATE', $user->id);

        $siteName = config('general.name');
        $baseUrl = "http://{$_SERVER['HTTP_HOST']}";
        $activateLink = Router::route('auth.activate') . "?u={$user->id}&k={$activate}";
        $profileLink = Router::route('user.profile', $user->id);
        $signature = config('mail.signature');

        // Build the e-mail
        $message = "Welcome to {$siteName}!\r\n\r\n"
            . "Please keep this e-mail for your records. Your account intormation is as follows:\r\n\r\n"
            . "----------------------------\r\n\r\n"
            . "Username: {$user->username}\r\n\r\n"
            . "Your profile: {$baseUrl}{$profileLink}\r\n\r\n"
            . "----------------------------\r\n\r\n"
            . "Please visit the following link in order to activate your account:\r\n\r\n"
            . "{$baseUrl}{$activateLink}\r\n\r\n"
            . "Your password has been securely stored in our database and cannot be retrieved. "
            . "In the event that it is forgotten,"
            . " you will be able to reset it using the email address associated with your account.\r\n\r\n"
            . "Thank you for registering.\r\n\r\n"
            . "--\r\n\r\nThanks\r\n\r\n{$signature}";

        // Send the message
        send_mail([$user->email => $user->username], "{$siteName} activation mail", $message);
    }

    /**
     * Send the activation e-mail
     *
     * @param User $user
     */
    private function sendPasswordMail($user)
    {
        // Generate the verification key
        $verk = ActionCode::generate('LOST_PASS', $user->id);

        $siteName = config('general.name');
        $baseUrl = "http://{$_SERVER['HTTP_HOST']}";
        $reactivateLink = Router::route('auth.resetpassword') . "?u={$user->id}&k={$verk}";
        $signature = config('mail.signature');

        // Build the e-mail
        $message = "Hello {$user->username},\r\n\r\n"
            . "You are receiving this notification because you have (or someone pretending to be you has)"
            . " requested a password reset link to be sent for your account on \"{$siteName}\"."
            . " If you did not request this notification then please ignore it,"
            . " if you keep receiving it please contact the site administrator.\r\n\r\n"
            . "To use this password reset key you need to go to a special page."
            . " To do this click the link provided below.\r\n\r\n"
            . "{$baseUrl}{$reactivateLink}\r\n\r\n"
            . "If successful you should be able to change your password here.\r\n\r\n"
            . "You can of course change this password yourself via the settings page."
            . " If you have any difficulties please contact the site administrator.\r\n\r\n"
            . "--\r\n\r\nThanks\r\n\r\n{$signature}";

        // Send the message
        send_mail([$user->email => $user->username], "{$siteName} password restoration", $message);
    }
}
