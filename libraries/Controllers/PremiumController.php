<?php
/**
 * Holds the premium pages controllers.
 * 
 * @package Sakura
 */

namespace Sakura\Controllers;

use Exception;
use Sakura\Config;
use Sakura\Template;
use Sakura\User;
use Sakura\Users;
use Sakura\Utils;
use Sakura\Payments;
use Sakura\Perms\Site;

/**
 * Premium pages controller.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class PremiumController extends Controller
{
    public function index()
    {
        global $currentUser, $urls;

        // Switch between modes (we only allow this to be used by logged in user)
        if (isset($_REQUEST['mode'])
            && Users::checkLogin()
            && $currentUser->permission(Site::OBTAIN_PREMIUM)) {
            // Initialise Payments class
            if (!Payments::init()) {
                header('Location: ' . $urls->format('SITE_PREMIUM') . '?fail=true');
            } else {
                switch ($_REQUEST['mode']) {
                    // Create the purchase
                    case 'purchase':
                        // Compare time and session so we know the link isn't forged
                        if (!isset($_REQUEST['time'])
                            || $_REQUEST['time'] < time() - 1000) {
                            return header('Location: ' . $urls->format('SITE_PREMIUM') . '?fail=true');
                        }

                        // Match session ids for the same reason
                        if (!isset($_REQUEST['session'])
                            || $_REQUEST['session'] != session_id()) {
                            return header('Location: ' . $urls->format('SITE_PREMIUM') . '?fail=true');
                        }

                        // Half if shit isn't gucci
                        if (!isset($_POST['months'])
                            || !is_numeric($_POST['months'])
                            || (int) $_POST['months'] < 1
                            || (int) $_POST['months'] > Config::get('premium_amount_max')) {
                            return header('Location: ' . $urls->format('SITE_PREMIUM') . '?fail=true');
                        } else {
                            // Calculate the total
                            $total = (float) Config::get('premium_price_per_month') * (int) $_POST['months'];
                            $total = number_format($total, 2, '.', '');

                            // Generate item name
                            $itemName = Config::get('sitename')
                            . ' Premium - '
                            . (string) $_POST['months']
                                . ' month'
                                . ((int) $_POST['months'] == 1 ? '' : 's');

                            // Attempt to create a transaction
                            if ($transaction = Payments::createTransaction(
                                $total,
                                $itemName,
                                Config::get('sitename') . ' Premium Purchase',
                                'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . Config::get('url_main') . $urls->format('SITE_PREMIUM')
                            )) {
                                // Store the amount of months in the global session array
                                $_SESSION['premiumMonths'] = (int) $_POST['months'];

                                return header('Location: ' . $transaction);
                            } else {
                                return header('Location: ' . $urls->format('SITE_PREMIUM') . '?fail=true');
                            }
                        }

                    // Finalising the purchase
                    case 'finish':
                        // Check if the success GET request is set and is true
                        if (isset($_GET['success'])
                            && isset($_GET['paymentId'])
                            && isset($_GET['PayerID'])
                            && isset($_SESSION['premiumMonths'])) {
                            // Attempt to complete the transaction
                            try {
                                $finalise = Payments::completeTransaction($_GET['paymentId'], $_GET['PayerID']);
                            } catch (Exception $e) {
                                return trigger_error('Something went horribly wrong.', E_USER_ERROR);
                            }

                            // Attempt to complete the transaction
                            if ($finalise) {
                                // Make the user premium
                                Users::addUserPremium($currentUser->id, (2628000 * $_SESSION['premiumMonths']));
                                Users::updatePremiumMeta($currentUser->id);
                                Utils::updatePremiumTracker(
                                    $currentUser->id,
                                    ((float) Config::get('premium_price_per_month') * $_SESSION['premiumMonths']),
                                    $currentUser->username
                                    . ' bought premium for '
                                    . $_SESSION['premiumMonths']
                                    . ' month'
                                    . ($_SESSION['premiumMonths'] == 1 ? '' : 's')
                                    . '.'
                                );

                                // Redirect to the complete
                                return header('Location: ' . $urls->format('SITE_PREMIUM') . '?mode=complete');
                            }
                        }

                        return header('Location: ' . $urls->format('SITE_PREMIUM') . '?fail=true');

                    case 'complete':
                        // Set parse variables
                        Template::vars([
                            'page' => [
                                'expiration' => ($prem = $currentUser->isPremium()[2]) !== null ? $prem : 0,
                            ],
                        ]);

                        // Print page contents
                        return Template::render('main/premiumcomplete');

                    default:
                        return header('Location: ' . $urls->format('SITE_PREMIUM'));

                }
            }
        }

        // Set parse variables
        Template::vars([
            'page' => [
                'fail' => isset($_GET['fail']),
                'price' => Config::get('premium_price_per_month'),
                'current' => $currentUser->isPremium(),
                'amount_max' => Config::get('premium_amount_max'),
            ],
        ]);

        // Print page contents
        return Template::render('main/support');
    }

    public function tracker()
    {
        // Set parse variables
        Template::vars([
            'tracker' => Utils::getPremiumTrackerData(),
        ]);

        // Print page contents
        return Template::render('main/supporttracker');
    }
}
