<?php
/*
 * Sakura Support/Donate page
 */

// Declare Namespace
namespace Sakura;

use Sakura\Perms\Site;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . 'sakura.php';

// Initialise templating engine
$template = new Template();

// Change templating engine
$template->setTemplate($templateName);

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
                    header('Location: ' . $urls->format('SITE_PREMIUM') . '?fail=true');
                    break;
                }

                // Match session ids for the same reason
                if (!isset($_REQUEST['session'])
                    || $_REQUEST['session'] != session_id()) {
                    header('Location: ' . $urls->format('SITE_PREMIUM') . '?fail=true');
                    break;
                }

                // Half if shit isn't gucci
                if (!isset($_POST['months'])
                    || !is_numeric($_POST['months'])
                    || (int) $_POST['months'] < 1
                    || (int) $_POST['months'] > Config::get('premium_amount_max')) {
                    header('Location: ' . $urls->format('SITE_PREMIUM') . '?fail=true');
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

                        header('Location: ' . $transaction);
                        exit;
                    } else {
                        header('Location: ' . $urls->format('SITE_PREMIUM') . '?fail=true');
                    }
                }

                break;

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
                        trigger_error('Something went horribly wrong.', E_USER_ERROR);
                    }

                    // Attempt to complete the transaction
                    if ($finalise) {
                        // Make the user premium
                        $expiration = Users::addUserPremium($currentUser->id, (2628000 * $_SESSION['premiumMonths']));
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
                        header('Location: ' . $urls->format('SITE_PREMIUM') . '?mode=complete');
                        exit;
                    }
                }

                header('Location: ' . $urls->format('SITE_PREMIUM') . '?fail=true');
                break;

            case 'complete':
                $renderData = array_merge([
                    'page' => [
                        'expiration' => ($prem = $currentUser->isPremium()[2]) !== null ? $prem : 0,
                    ],
                ], $renderData);

                // Set parse variables
                $template->setVariables($renderData);

                // Print page contents
                echo $template->render('main/premiumcomplete');
                break;

            default:
                header('Location: ' . $urls->format('SITE_PREMIUM'));
                break;

        }
    }

    exit;
}

// Premium tracker
if (isset($_GET['tracker'])) {
    $renderData['tracker'] =  Utils::getPremiumTrackerData();

    // Set parse variables
    $template->setVariables($renderData);

    // Print page contents
    echo $template->render('main/supporttracker');
    exit;
}

// Set default variables
$renderData['page'] = [

    'fail' => isset($_GET['fail']),
    'price' => Config::get('premium_price_per_month'),
    'current' => $currentUser->isPremium(),
    'amount_max' => Config::get('premium_amount_max'),

];

// Set parse variables
$template->setVariables($renderData);

// Print page contents
echo $template->render('main/support');
