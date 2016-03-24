<?php
/**
 * Holds the auth controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\ActionCode;
use Sakura\Config;
use Sakura\DB;
use Sakura\Hashing;
use Sakura\Net;
use Sakura\Perms\Site;
use Sakura\Router;
use Sakura\Session;
use Sakura\Template;
use Sakura\User;
use Sakura\Users;
use Sakura\Utils;

/**
 * Authentication controllers.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AuthController extends Controller
{
    protected function touchRateLimit($user, $mode = 0)
    {
        DB::table('login_attempts')
            ->insert([
                'attempt_success' => $mode,
                'attempt_timestamp' => time(),
                'attempt_ip' => Net::pton(Net::IP()),
                'user_id' => $user,
            ]);
    }

    public function logout()
    {
        // Check if user is logged in
        $check = Users::checkLogin();

        if (!$check || !isset($_REQUEST['s']) || $_REQUEST['s'] != session_id()) {
            $message = 'Something happened! This probably happened because you went here without being logged in.';
            $redirect = (isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : Router::route('main.index'));

            Template::vars(['page' => ['success' => 0, 'redirect' => $redirect, 'message' => $message]]);

            return Template::render('global/information');
        }

        // Destroy the active session
        (new Session($check[0], $check[1]))->destroy();

        // Return true indicating a successful logout
        $message = 'Goodbye!';
        $redirect = Router::route('auth.login');

        Template::vars(['page' => ['success' => 1, 'redirect' => $redirect, 'message' => $message]]);

        return Template::render('global/information');
    }

    public function loginGet()
    {
        return Template::render('main/login');
    }

    public function loginPost()
    {
        // Preliminarily set login to failed
        $success = 0;
        $redirect = Router::route('auth.login');

        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            $message = 'Logging in is disabled for security checkups! Try again later.';
            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Get request variables
        $username = isset($_REQUEST['username']) ? $_REQUEST['username'] : null;
        $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : null;
        $remember = isset($_REQUEST['remember']);

        // Check if we haven't hit the rate limit
        $rates = DB::table('login_attempts')
            ->where('attempt_ip', Net::pton(Net::IP()))
            ->where('attempt_timestamp', '>', time() - 1800)
            ->where('attempt_success', '0')
            ->count();

        if ($rates > 4) {
            $message = 'Your have hit the login rate limit, try again later.';
            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Get account data
        $user = User::construct(Utils::cleanString($username, true, true));

        // Check if the user that's trying to log in actually exists
        if ($user->id === 0) {
            $this->touchRateLimit($user->id);
            $message = 'The user you tried to log into does not exist.';
            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Validate password
        switch ($user->passwordAlgo) {
            // Disabled
            case 'disabled':
                $this->touchRateLimit($user->id);
                $message = 'Logging into this account is disabled.';
                Template::vars(['page' => compact('success', 'redirect', 'message')]);

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
                    Template::vars(['page' => compact('success', 'redirect', 'message')]);

                    return Template::render('global/information');
                }
        }

        // Check if the user has the required privs to log in
        if ($user->permission(Site::DEACTIVATED)) {
            $this->touchRateLimit($user->id);
            $message = 'Your account does not have the required permissions to log in.';
            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Create a new session
        $session = new Session($user->id);

        // Generate a session key
        $sessionKey = $session->create($remember);

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

        $this->touchRateLimit($user->id, 1);

        $success = 1;

        $redirect = $user->lastOnline
        ? (isset($_REQUEST['redirect'])
            ? $_REQUEST['redirect']
            : Router::route('main.index'))
        : Router::route('main.infopage', 'welcome');

        $message = 'Welcome' . ($user->lastOnline ? ' back' : '') . '!';

        Template::vars(['page' => compact('success', 'redirect', 'message')]);

        return Template::render('global/information');
    }

    public function registerGet()
    {
        // Attempt to check if a user has already registered from the current IP
        $getUserIP = DB::table('users')
            ->where('register_ip', Net::pton(Net::IP()))
            ->orWhere('last_ip', Net::pton(Net::IP()))
            ->get();

        if ($getUserIP) {
            Template::vars([
                'haltRegistration' => count($getUserIP) > 1,
                'haltName' => $getUserIP[array_rand($getUserIP)]->username,
            ]);
        }

        return Template::render('main/register');
    }

    public function registerPost()
    {
        // Preliminarily set registration to failed
        $success = 0;
        $redirect = Router::route('auth.register');

        // Check if authentication is disallowed
        if (Config::get('lock_authentication') || Config::get('disable_registration')) {
            $message = 'Registration is disabled for security checkups! Try again later.';

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Check if authentication is disallowed
        if (!isset($_POST['session']) || $_POST['session'] != session_id()) {
            $message = "Your session expired, refreshing the page will most likely fix this!";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Grab forms
        $username = isset($_POST['username']) ? $_POST['username'] : null;
        $password = isset($_POST['password']) ? $_POST['password'] : null;
        $email = isset($_POST['email']) ? $_POST['email'] : null;
        $captcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : null;
        $terms = isset($_POST['tos']);

        // Append username and email to the redirection url
        $redirect .= "?username={$username}&email={$email}";

        // Check if the user agreed to the ToS
        if (!$terms) {
            $message = 'You are required to agree to the Terms of Service.';

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Check if we require a captcha
        if (Config::get('recaptcha')) {
            // Get secret key from the config
            $secret = Config::get('recaptcha_private');

            // Attempt to verify the captcha
            $response = Net::fetch("https://google.com/recaptcha/api/siteverify?secret={$secret}&response={$captcha}");

            // Attempt to decode as json
            if ($response) {
                $response = json_decode($response);
            }

            if (!$response || !$response->success) {
                $message = 'Captcha verification failed, please try again.';

                Template::vars(['page' => compact('success', 'redirect', 'message')]);

                return Template::render('global/information');
            }
        }

        // Attempt to get account data
        $user = User::construct(Utils::cleanString($username, true, true));

        // Check if the username already exists
        if ($user && $user->id !== 0) {
            $message = "{$user->username} is already a member here! If this is you please use the password reset form instead of making a new account.";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Username too short
        if (strlen($username) < Config::get('username_min_length')) {
            $message = 'Your name must be at least 3 characters long.';

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Username too long
        if (strlen($username) > Config::get('username_max_length')) {
            $message = 'Your name can\'t be longer than 16 characters.';

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Check if the given email address is formatted properly
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Your e-mail address is formatted incorrectly.';

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Check the MX record of the email
        if (!Utils::checkMXRecord($email)) {
            $message = 'No valid MX-Record found on the e-mail address you supplied.';

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Check if the e-mail has already been used
        $emailCheck = DB::table('users')
            ->where('email', $email)
            ->count();
        if ($emailCheck) {
            $message = 'Someone already registered using this email!';

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Check password entropy
        if (Utils::pwdEntropy($password) < Config::get('min_entropy')) {
            $message = 'Your password is too weak, try adding some special characters.';

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Set a few variables
        $requireActive = Config::get('require_activation');
        $ranks = $requireActive ? [1] : [2];

        // Create the user
        $user = User::create($username, $password, $email, $ranks);

        // Check if we require e-mail activation
        if ($requireActive) {
            // Send activation e-mail to user
            Users::sendActivationMail($user->id);
        }

        // Return true with a specific message if needed
        $success = 1;
        $redirect = Router::route('auth.login');
        $message = $requireActive
        ? 'Your registration went through! An activation e-mail has been sent.'
        : 'Your registration went through! Welcome to ' . Config::get('sitename') . '!';

        Template::vars(['page' => compact('success', 'redirect', 'message')]);

        return Template::render('global/information');
    }

    public function activate()
    {
        // Preliminarily set activation to failed
        $success = 0;
        $redirect = Router::route('main.index');

        // Attempt to get the required GET parameters
        $userId = isset($_GET['u']) ? $_GET['u'] : 0;
        $key = isset($_GET['k']) ? $_GET['k'] : "";

        // Attempt to create a user object
        $user = User::construct($userId);

        // Quit if the user ID is 0
        if ($user->id === 0) {
            $message = "This user does not exist! Contact us if you think this isn't right.";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Check if the user is already active
        if (!$user->permission(Site::DEACTIVATED)) {
            $message = "Your account is already activated! Why are you here?";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Validate the activation key
        $action = ActionCode::validate('ACTIVATE', $key, $user->id);

        if (!$action) {
            $message = "Invalid activation code! Contact us if you think this isn't right.";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Get the ids for deactivated and default user ranks
        $rankDefault = Config::get('default_rank_id');
        $rankDeactive = Config::get('deactive_rank_id');

        // Add normal user, remove deactivated and set normal as default
        $user->addRanks([$rankDefault]);
        $user->setMainRank($rankDefault);
        $user->removeRanks([$rankDeactive]);

        $success = 1;
        $redirect = Router::route('auth.login');
        $message = "Your account is activated, welcome to " . Config::get('sitename') . "!";

        Template::vars(['page' => compact('success', 'redirect', 'message')]);

        return Template::render('global/information');
    }

    public function reactivateGet()
    {
        return Template::render('main/reactivate');
    }

    public function reactivatePost()
    {
        // Preliminarily set registration to failed
        $success = 0;
        $redirect = Router::route('auth.reactivate');

        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            $message = "You can't request a reactivation at this time, sorry!";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Validate session
        if (!isset($_POST['session']) || $_POST['session'] != session_id()) {
            $message = "Your session expired, refreshing the page will most likely fix this!";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Grab forms
        $username = isset($_POST['username']) ? Utils::cleanString($_POST['username'], true) : null;
        $email = isset($_POST['email']) ? Utils::cleanString($_POST['email'], true) : null;

        // Do database request
        $getUser = DB::table('users')
            ->where('username_clean', $username)
            ->where('email', $email)
            ->get(['user_id']);

        // Check if user exists
        if (!$getUser) {
            $message = "User not found! Double check your username and e-mail address!";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Create user object
        $user = User::construct($getUser[0]->user_id);

        // Check if a user is activated
        if (!$user->permission(Site::DEACTIVATED)) {
            $message = "Your account is already activated! Why are you here?";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Send activation e-mail to user
        Users::sendActivationMail($user->id);

        $success = 1;
        $redirect = Router::route('auth.login');
        $message = "Sent the e-mail! Make sure to check your spam folder as well!";

        Template::vars(['page' => compact('success', 'redirect', 'message')]);

        return Template::render('global/information');
    }

    public function resetPasswordGet()
    {
        return Template::render('main/resetpassword');
    }

    public function resetPasswordPost()
    {
        // Preliminarily set action to failed
        $success = 0;
        $redirect = Router::route('main.index');

        // Check if authentication is disallowed
        if (Config::get('lock_authentication')) {
            $message = "You can't request a reactivation at this time, sorry!";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Validate session
        if (!isset($_POST['session']) || $_POST['session'] != session_id()) {
            $message = "Your session expired, refreshing the page will most likely fix this!";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Attempt to get the various required GET parameters
        $userId = isset($_POST['user']) ? $_POST['user'] : 0;
        $key = isset($_POST['key']) ? $_POST['key'] : "";
        $password = isset($_POST['password']) ? $_POST['password'] : "";
        $userName = isset($_POST['username']) ? Utils::cleanString($_POST['username'], true) : "";
        $email = isset($_POST['email']) ? Utils::cleanString($_POST['email'], true) : null;

        // Create user object
        $user = User::construct($userId ? $userId : $userName);

        // Quit if the user ID is 0
        if ($user->id === 0 || ($email !== null ? $email !== $user->email : false)) {
            $message = "This user does not exist! Contact us if you think this isn't right.";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        // Check if the user is active
        if ($user->permission(Site::DEACTIVATED)) {
            $message = "Your account is deactivated, go activate it first...";

            Template::vars(['page' => compact('success', 'redirect', 'message')]);

            return Template::render('global/information');
        }

        if ($key && $password) {
            // Check password entropy
            if (Utils::pwdEntropy($password) < Config::get('min_entropy')) {
                $message = "Your password doesn't meet the strength requirements!";

                Template::vars(['page' => compact('success', 'redirect', 'message')]);

                return Template::render('global/information');
            }

            // Validate the activation key
            $action = ActionCode::validate('LOST_PASS', $key, $user->id);

            if (!$action) {
                $message = "Invalid verification code! Contact us if you think this isn't right.";

                Template::vars(['page' => compact('success', 'redirect', 'message')]);

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

            $success = 1;
            $message = "Changed your password! You may now log in.";
            $redirect = Router::route('auth.login');
        } else {
            // Send e-mail
            Users::sendPasswordForgot($user->id, $user->email);

            $success = 1;
            $message = "Sent the e-mail, keep an eye on your spam folder as well!";
            $redirect = Router::route('main.index');
        }

        Template::vars(['page' => compact('success', 'redirect', 'message')]);

        return Template::render('global/information');
    }
}
