<?php
/**
 * Holds the premium pages controllers.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Exception;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Sakura\Config;
use Sakura\CurrentSession;
use Sakura\Payments;

/**
 * Premium pages controller.
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
        parent::__construct();
        Payments::init();
    }

    /**
     * Returns the premium purchase index.
     * @return string
     */
    public function index()
    {
        $price = config('premium.price_per_month');
        $amountLimit = config('premium.max_months_at_once');
        return view('premium/index', compact('price', 'amountLimit'));
    }

    /**
     * Handles a purchase request.
     * @return string
     */
    public function purchase()
    {
        // Get values from post
        $months = isset($_POST['months']) ? $_POST['months'] : 0;

        // Check if the session is valid
        if (!session_check()
            || !CurrentSession::$user->activated
            || !CurrentSession::$user->verified
            || CurrentSession::$user->restricted) {
            throw new HttpMethodNotAllowedException();
        }

        // Fetch the limit
        $amountLimit = config('premium.max_months_at_once');

        // Check months
        if ($months < 1
            || $months > $amountLimit) {
            redirect(route('premium.error'));
            return;
        }

        $pricePerMonth = config('premium.price_per_month');
        $total = number_format($pricePerMonth * $months, 2, '.', '');

        $siteName = config('general.name');
        $multiMonths = $months !== 1 ? 's' : '';

        $siteUrl = 'http'
            . (isset($_SERVER['HTTPS']) ? 's' : '')
            . "://{$_SERVER['SERVER_NAME']}"
            . ($_SERVER['SERVER_PORT'] != 80 ? ":{$_SERVER['SERVER_PORT']}" : '');
        $handlerRoute = route('premium.handle');

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
            redirect(route('premium.error'));
            return;
        }

        // Store the amount of months in the global session array
        $_SESSION['premiumMonths'] = (int) $months;

        redirect($transaction);
    }

    /**
     * Handles the data returned by PayPal.
     * @return string
     */
    public function handle()
    {
        $success = isset($_GET['success']);
        $payment = isset($_GET['paymentId']) ? $_GET['paymentId'] : null;
        $payer = isset($_GET['PayerID']) ? $_GET['PayerID'] : null;
        $months = isset($_SESSION['premiumMonths']) ? $_SESSION['premiumMonths'] : null;

        $successRoute = route('premium.complete');
        $failRoute = route('premium.error');

        if (!$success
            || !$payment
            || !$payer
            || !$months) {
            redirect($failRoute);
            return;
        }

        // Attempt to complete the transaction
        try {
            $finalise = Payments::completeTransaction($_GET['paymentId'], $_GET['PayerID']);
        } catch (Exception $e) {
            $finalise = false;
        }

        if (!$finalise) {
            redirect($failRoute);
            return;
        }

        CurrentSession::$user->addPremium(self::PERIOD_PER_PAYMENT * $months);

        redirect($successRoute);
    }

    /**
     * Presents the user with a thank you <3.
     * @return string
     */
    public function complete()
    {
        return view('premium/complete');
    }

    /**
     * Errors.
     * @return string
     */
    public function error()
    {
        return view('premium/error');
    }
}
