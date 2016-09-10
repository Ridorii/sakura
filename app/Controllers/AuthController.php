<?php
/**
 * Holds the auth controllers.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\ActionCode;
use Sakura\Config;
use Sakura\CurrentSession;
use Sakura\DB;
use Sakura\Net;
use Sakura\Perms\Site;
use Sakura\User;

/**
 * Authentication controllers.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AuthController extends Controller
{
    /**
     * Touch the login rate limit.
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
     * @return string
     */
    public function logout()
    {
        if (!session_check('s')) {
            $message = 'Validation failed, this logout attempt was possibly forged.';
            $redirect = $_REQUEST['redirect'] ?? route('main.index');
            return view('global/information', compact('message', 'redirect'));
        }

        // Destroy the active session
        CurrentSession::stop();

        // Return true indicating a successful logout
        $message = 'Goodbye!';
        $redirect = route('auth.login');
        return view('global/information', compact('message', 'redirect'));
    }

    /**
     * Login page.
     * @return string
     */
    public function login()
    {
        if (!session_check()) {
            return view('auth/login');
        }

        // Preliminarily set login to failed
        $redirect = route('auth.login');

        // Get request variables
        $username = $_REQUEST['username'] ?? null;
        $password = $_REQUEST['password'] ?? null;
        $remember = isset($_REQUEST['remember']);

        // Check if we haven't hit the rate limit
        $rates = DB::table('login_attempts')
            ->where('attempt_ip', Net::pton(Net::ip()))
            ->where('attempt_timestamp', '>', time() - 1800)
            ->where('attempt_success', '0')
            ->count();

        if ($rates > 4) {
            $message = 'Your have hit the login rate limit, try again later.';
            return view('global/information', compact('message', 'redirect'));
        }

        // Get account data
        $user = User::construct(clean_string($username, true, true));

        // Check if the user that's trying to log in actually exists
        if ($user->id === 0) {
            $this->touchRateLimit($user->id);
            $message = 'The user you tried to log into does not exist.';
            return view('global/information', compact('message', 'redirect'));
        }

        if ($user->passwordExpired()) {
            $message = 'Your password expired.';
            $redirect = route('auth.resetpassword');
            return view('global/information', compact('message', 'redirect'));
        }

        if (!$user->verifyPassword($password)) {
            $this->touchRateLimit($user->id);
            $message = 'The password you entered was invalid.';
            return view('global/information', compact('message', 'redirect'));
        }

        // Check if the user has the required privs to log in
        if ($user->permission(Site::DEACTIVATED)) {
            $this->touchRateLimit($user->id);
            $message = 'Your account is deactivated, activate it first!';
            $redirect = route('auth.reactivate');
            return view('global/information', compact('message', 'redirect'));
        }

        // Generate a session key
        $session = CurrentSession::create(
            $user->id,
            Net::ip(),
            get_country_code(),
            clean_string($_SERVER['HTTP_USER_AGENT'] ?? ''),
            $remember
        );

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
            $session->key,
            time() + 604800
        );

        $this->touchRateLimit($user->id, true);

        $redirect = $user->lastOnline ? ($_REQUEST['redirect'] ?? route('main.index')) : route('info.welcome');

        $message = 'Welcome' . ($user->lastOnline ? ' back' : '') . '!';

        return view('global/information', compact('message', 'redirect'));
    }

    /**
     * Do a registration attempt.
     * @return string
     */
    public function register()
    {
        // Preliminarily set registration to failed
        $redirect = route('auth.register');

        // Check if authentication is disallowed
        if (config('user.disable_registration')) {
            $message = 'Registration is disabled for security checkups! Try again later.';
            return view('global/information', compact('message', 'redirect'));
        }

        if (!session_check()) {
            // Attempt to check if a user has already registered from the current IP
            $getUserIP = DB::table('users')
                ->where('register_ip', Net::pton(Net::ip()))
                ->orWhere('last_ip', Net::pton(Net::ip()))
                ->get();

            $vars = [];

            if ($getUserIP) {
                $vars = [
                    'haltRegistration' => count($getUserIP) > 1,
                    'haltName' => $getUserIP[array_rand($getUserIP)]->username,
                ];
            }

            return view('auth/register', $vars);
        }

        // Grab forms
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        $email = $_POST['email'] ?? null;

        // Append username and email to the redirection url
        $redirect .= "?username={$username}&email={$email}";

        // Attempt to get account data
        $user = User::construct(clean_string($username, true, true));

        // Check if the username already exists
        if ($user && $user->id !== 0) {
            $message = "{$user->username} is already a member here!"
                . " If this is you please use the password reset form instead of making a new account.";
            return view('global/information', compact('message', 'redirect'));
        }

        // Username too short
        if (strlen($username) < config('user.name_min')) {
            $message = 'Your name must be at least 3 characters long.';
            return view('global/information', compact('message', 'redirect'));
        }

        // Username too long
        if (strlen($username) > config('user.name_max')) {
            $message = 'Your name can\'t be longer than 16 characters.';
            return view('global/information', compact('message', 'redirect'));
        }

        // Check if the given email address is formatted properly
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Your e-mail address is formatted incorrectly.';
            return view('global/information', compact('message', 'redirect'));
        }

        // Check the MX record of the email
        if (!check_mx_record($email)) {
            $message = 'No valid MX-Record found on the e-mail address you supplied.';
            return view('global/information', compact('message', 'redirect'));
        }

        // Check if the e-mail has already been used
        $emailCheck = DB::table('users')
            ->where('email', $email)
            ->count();
        if ($emailCheck) {
            $message = 'Someone already registered using this email!';
            return view('global/information', compact('message', 'redirect'));
        }

        // Check password entropy
        if (password_entropy($password) < config('user.pass_min_entropy')) {
            $message = 'Your password is too weak, try adding some special characters.';
            return view('global/information', compact('message', 'redirect'));
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
        $redirect = route('auth.login');
        $message = $requireActive
        ? 'Your registration went through! An activation e-mail has been sent.'
        : 'Your registration went through! Welcome to ' . config('general.name') . '!';

        return view('global/information', compact('message', 'redirect'));
    }

    /**
     * Do a activation attempt.
     * @return string
     */
    public function activate()
    {
        // Preliminarily set activation to failed
        $redirect = route('main.index');

        // Attempt to get the required GET parameters
        $userId = $_GET['u'] ?? 0;
        $key = $_GET['k'] ?? "";

        // Attempt to create a user object
        $user = User::construct($userId);

        // Quit if the user ID is 0
        if ($user->id === 0) {
            $message = "This user does not exist! Contact us if you think this isn't right.";
            return view('global/information', compact('message', 'redirect'));
        }

        // Check if the user is already active
        if (!$user->permission(Site::DEACTIVATED)) {
            $message = "Your account is already activated! Why are you here?";
            return view('global/information', compact('message', 'redirect'));
        }

        // Validate the activation key
        $action = ActionCode::validate('ACTIVATE', $key, $user->id);

        if (!$action) {
            $message = "Invalid activation code! Contact us if you think this isn't right.";
            return view('global/information', compact('message', 'redirect'));
        }

        // Get the ids for deactivated and default user ranks
        $rankDefault = config('rank.regular');
        $rankDeactive = config('rank.inactive');

        // Add normal user, remove deactivated and set normal as default
        $user->addRanks([$rankDefault]);
        $user->setMainRank($rankDefault);
        $user->removeRanks([$rankDeactive]);

        $redirect = route('auth.login');
        $message = "Your account is activated, welcome to " . config('general.name') . "!";
        return view('global/information', compact('message', 'redirect'));
    }

    /**
     * Do a reactivation preparation attempt.
     * @return string
     */
    public function reactivate()
    {
        // Validate session
        if (!session_check()) {
            return view('auth/reactivate');
        }

        // Preliminarily set registration to failed
        $redirect = route('auth.reactivate');

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
            return view('global/information', compact('message', 'redirect'));
        }

        // Create user object
        $user = User::construct($getUser[0]->user_id);

        // Check if a user is activated
        if (!$user->permission(Site::DEACTIVATED)) {
            $message = "Your account is already activated! Why are you here?";
            return view('global/information', compact('message', 'redirect'));
        }

        // Send activation e-mail to user
        $this->sendActivationMail($user);

        $redirect = route('auth.login');
        $message = "Sent the e-mail! Make sure to check your spam folder as well!";
        return view('global/information', compact('message', 'redirect'));
    }

    /**
     * Do a password reset attempt.
     * @return string
     */
    public function resetPassword()
    {
        // Validate session
        if (!session_check()) {
            return view('auth/resetpassword');
        }

        // Preliminarily set action to failed
        $redirect = route('main.index');

        // Attempt to get the various required GET parameters
        $userId = $_POST['user'] ?? 0;
        $key = $_POST['key'] ?? "";
        $password = $_POST['password'] ?? "";
        $userName = clean_string($_POST['username'] ?? "", true);
        $email = clean_string($_POST['email'] ?? "", true);

        // Create user object
        $user = User::construct($userId ? $userId : $userName);

        // Quit if the user ID is 0
        if ($user->id === 0 || ($email !== null ? $email !== $user->email : false)) {
            $message = "This user does not exist! Contact us if you think this isn't right.";
            return view('global/information', compact('message', 'redirect'));
        }

        // Check if the user is active
        if ($user->permission(Site::DEACTIVATED)) {
            $message = "Your account is deactivated, go activate it first...";
            return view('global/information', compact('message', 'redirect'));
        }

        if ($key && $password) {
            // Check password entropy
            if (password_entropy($password) < config('user.pass_min_entropy')) {
                $message = "Your password doesn't meet the strength requirements!";
                return view('global/information', compact('message', 'redirect'));
            }

            // Validate the activation key
            $action = ActionCode::validate('LOST_PASS', $key, $user->id);

            if (!$action) {
                $message = "Invalid verification code! Contact us if you think this isn't right.";
                return view('global/information', compact('message', 'redirect'));
            }

            $user->setPassword($password);

            $message = "Changed your password! You may now log in.";
            $redirect = route('auth.login');
        } else {
            // Send the e-mail
            $this->sendPasswordMail($user);

            $message = "Sent the e-mail, keep an eye on your spam folder as well!";
            $redirect = route('main.index');
        }

        return view('global/information', compact('message', 'redirect'));
    }

    /**
     * Send the activation e-mail
     * @param User $user
     */
    private function sendActivationMail($user)
    {
        // Generate activation key
        $activate = ActionCode::generate('ACTIVATE', $user->id);

        $siteName = config('general.name');
        $baseUrl = "http://{$_SERVER['HTTP_HOST']}";
        $activateLink = route('auth.activate') . "?u={$user->id}&k={$activate}";
        $profileLink = route('user.profile', $user->id);
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
     * @param User $user
     */
    private function sendPasswordMail($user)
    {
        // Generate the verification key
        $verk = ActionCode::generate('LOST_PASS', $user->id);

        $siteName = config('general.name');
        $baseUrl = "http://{$_SERVER['HTTP_HOST']}";
        $reactivateLink = route('auth.resetpassword') . "?u={$user->id}&k={$verk}";
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
