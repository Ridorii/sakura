<?php
/*
 * Sakura Support/Donate page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Switch between modes (we only allow this to be used by logged in user)
if(isset($_REQUEST['mode']) && Users::checkLogin() && Permissions::check('SITE', 'OBTAIN_PREMIUM', Session::$userId, 1)) {

    // Initialise Payments class
    if(!Payments::init()) {

        header('Location: /support?fail=true');

    } else {

        switch($_REQUEST['mode']) {

            // Create the purchase
            case 'purchase':

                // Compare time and session so we know the link isn't forged
                if(!isset($_REQUEST['time']) || $_REQUEST['time'] < time() - 1000) {

                    header('Location: /support?fail=true');
                    break;

                }

                // Match session ids for the same reason
                if(!isset($_REQUEST['session']) || $_REQUEST['session'] != session_id()) {

                    header('Location: /support?fail=true');
                    break;

                }

                // Half if shit isn't gucci
                if(!isset($_POST['months']) || !is_numeric($_POST['months']) || (int)$_POST['months'] < 1 || (int)$_POST['months'] > Configuration::getConfig('premium_amount_max')) {

                    header('Location: /support?fail=true');

                } else {

                    // Calculate the total
                    $total = (float)Configuration::getConfig('premium_price_per_month') * (int)$_POST['months'];
                    $total = number_format($total, 2, '.', '');

                    // Generate item name
                    $itemName = Configuration::getConfig('sitename') .' Premium - '. (string)$_POST['months'] .' month'. ((int)$_POST['months'] == 1 ? '' : 's');

                    // Attempt to create a transaction
                    if($transaction = Payments::createTransaction($total, $itemName, Configuration::getConfig('sitename') .' Premium Purchase', 'http://'. Configuration::getConfig('url_main') .'/support')) {

                        // Store the amount of months in the global session array
                        $_SESSION['premiumMonths'] = (int)$_POST['months'];

                        header('Location: '. $transaction);
                        exit;

                    } else {

                        header('Location: /support?fail=true');

                    }

                }

                break;

            // Finalising the purchase
            case 'finish':

                // Check if the success GET request is set and is true
                if(isset($_GET['success']) && isset($_GET['paymentId']) && isset($_GET['PayerID']) && isset($_SESSION['premiumMonths'])) {

                    // Attempt to complete the transaction
                    try{
                        $finalise = Payments::completeTransaction($_GET['paymentId'], $_GET['PayerID']);
                    } catch(Exception $e) {}

                    // Attempt to complete the transaction
                    if($finalise) {

                        // Make the user premium
                        $expiration = Users::addUserPremium(Session::$userId, (2628000 * $_SESSION['premiumMonths']));
                        Users::updatePremiumMeta(Session::$userId);
                        Main::updatePremiumTracker(Session::$userId, ((float)Configuration::getConfig('premium_price_per_month') * $_SESSION['premiumMonths']), $currentUser->data['username'] .' bought premium for '. $_SESSION['premiumMonths'] .' month'. ($_SESSION['premiumMonths'] == 1 ? '' : 's') .'.');

                        // Redirect to the complete
                        header('Location: ?mode=complete');
                        exit;

                    }

                }

                header('Location: /support?fail=true');
                break;

            case 'complete':
                print Templates::render('errors/premiumComplete.tpl', array_merge([
                    'page' => [
                        'title' => 'Premium purchase complete!',
                        'expiration' => ($prem = Users::checkUserPremium(Session::$userId)[2]) !== null ? $prem : 0
                    ]
               ], $renderData));
                break;

            default:
                header('Location: /support');
                break;

        }

    }

    exit;

}

// Premium tracker
if(isset($_GET['tracker'])) {

    $renderData['page'] = [

        'title'         => 'Donation Tracker',
        'currentPage'   => isset($_GET['page']) && ($_GET['page'] - 1) >= 0 ? $_GET['page'] - 1 : 0,
        'premiumData'   => ($_PREMIUM = Main::getPremiumTrackerData()),
        'premiumTable'  => array_chunk($_PREMIUM['table'], 20, true)

    ];

    print Templates::render('main/supporttracker.tpl', $renderData);
    exit;

}

// Set default variables
$renderData['page'] = [

    'title'         => 'Support '. Configuration::getConfig('sitename'),
    'fail'          => isset($_GET['fail']),
    'price'         => Configuration::getConfig('premium_price_per_month'),
    'current'       => $currentUser->checkPremium(),
    'amount_max'    => Configuration::getConfig('premium_amount_max')

];

// Print page contents
print Templates::render('main/support.tpl', $renderData);
