<?php
/**
 * Holds the premium pages controllers.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Exception;
use Sakura\ActiveUser;
use Sakura\Config;
use Sakura\Payments;
use Sakura\Perms\Site;
use Sakura\Router;
use Sakura\Template;

/**
 * Premium pages controller.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class PremiumController extends Controller
{
    /**
     * The amount of premium a user received per period.
     */
    const PERIOD_PER_PAYMENT = 2628000;

    /**
     * Constructor.
     */
    public function __construct()
    {
        Payments::init();
    }

    /**
     * Returns the premium purchase index.
     *
     * @return mixed
     */
    public function index()
    {
        $price = Config::get('premium_price_per_month');
        $amountLimit = Config::get('premium_amount_max');

        Template::vars(compact('price', 'amountLimit'));

        return Template::render('premium/index');
    }

    /**
     * Handles a purchase request.
     *
     * @return mixed
     */
    public function purchase()
    {
        // Get values from post
        $session = isset($_POST['session']) ? $_POST['session'] : '';
        $months = isset($_POST['months']) ? $_POST['months'] : 0;

        // Check if the session is valid
        if ($session !== session_id()
            || ActiveUser::$user->permission(Site::DEACTIVATED)
            || !ActiveUser::$user->permission(Site::OBTAIN_PREMIUM)) {
            $message = "You are not allowed to get premium!";
            $redirect = Router::route('premium.index');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Fetch the limit
        $amountLimit = Config::get('premium_amount_max');

        // Check months
        if ($months < 1
            || $months > $amountLimit) {
            $message = "An incorrect amount of months was specified, stop messing with the source.";
            $redirect = Router::route('premium.index');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        $pricePerMonth = Config::get('premium_price_per_month');
        $total = number_format($pricePerMonth * $months, 2, '.', '');

        $siteName = Config::get('sitename');
        $multiMonths = $months !== 1 ? 's' : '';

        $siteUrl = 'http'
            . (isset($_SERVER['HTTPS']) ? 's' : '')
            . "://{$_SERVER['SERVER_NAME']}"
            . ($_SERVER['SERVER_PORT'] != 80 ? ":{$_SERVER['SERVER_PORT']}" : '');
        $handlerRoute = Router::route('premium.handle');

        $itemName = "{$siteName} Premium - {$months} month{$multiMonths}";
        $transactionName = "{$siteName} premium purchase";
        $handlerUrl = "{$siteUrl}{$handlerRoute}";

        // Create the transaction
        $transaction = Payments::createTransaction(
            $total,
            $itemName,
            $transactionName,
            $handlerUrl
        );

        // Attempt to create a transaction
        if (!$transaction) {
            $message = "Something went wrong while preparing the transaction.";
            $redirect = Router::route('premium.index');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Store the amount of months in the global session array
        $_SESSION['premiumMonths'] = (int) $months;

        return header("Location: {$transaction}");
    }

    /**
     * Handles the data returned by PayPal.
     *
     * @return mixed
     */
    public function handle()
    {
        $success = isset($_GET['success']);
        $payment = isset($_GET['paymentId']) ? $_GET['paymentId'] : null;
        $payer = isset($_GET['PayerID']) ? $_GET['PayerID'] : null;
        $months = isset($_SESSION['premiumMonths']) ? $_SESSION['premiumMonths'] : null;

        $successRoute = Router::route('premium.complete');
        $failRoute = Router::route('premium.index') . "?fail=true";

        if (!$success
            || !$payment
            || !$payer
            || !$months) {
            return header("Location: {$failRoute}");
        }

        // Attempt to complete the transaction
        try {
            $finalise = Payments::completeTransaction($_GET['paymentId'], $_GET['PayerID']);
        } catch (Exception $e) {
            $finalise = false;
        }

        if (!$finalise) {
            return header("Location: {$failRoute}");
        }

        ActiveUser::$user->addPremium(self::PERIOD_PER_PAYMENT * $months);

        return header("Location: {$successRoute}");
    }

    /**
     * Presents the user with a thank you <3.
     *
     * @return mixed
     */
    public function complete()
    {
        return Template::render('premium/complete');
    }
}
