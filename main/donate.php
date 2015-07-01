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

        $renderData['page'] = [
            'title'     => 'Action failed',
            'redirect'  => '/support',
            'message'   => 'Failed to initialise the Payment handling components, contact a staff member as soon as possible.'
        ];

    } else {

        switch($_REQUEST['mode']) {

            // Create the purchase 
            case 'purchase':

                // Compare time and session so we know the link isn't forged
                if(!isset($_REQUEST['time']) || $_REQUEST['time'] < time() - 1000) {

                    $renderData['page'] = [
                        'title'     => 'Action failed',
                        'redirect'  => '/support',
                        'message'   => 'Timestamps differ too much, refresh the page and try again.'
                    ];

                    break;

                }

                // Match session ids for the same reason
                if(!isset($_REQUEST['session']) || $_REQUEST['session'] != session_id()) {

                    $renderData['page'] = [
                        'title'     => 'Action failed',
                        'redirect'  => '/support',
                        'message'   => 'Invalid session, please try again.'
                    ];

                    break;

                }

                // Half if shit isn't gucci
                if(!isset($_POST['months']) || !is_numeric($_POST['months']) || (int)$_POST['months'] < 1 || (int)$_POST['months'] > 24) {

                    header('Location: /support?fail=true');

                } else {

                    // Calculate the total
                    $total = (float)Configuration::getConfig('premium_price_per_month') * (int)$_POST['months'];
                    $total = money_format('%!i', $total);

                    // Generate item name
                    $itemName = 'Flashii Tenshi - '. (string)$_POST['months'] .' month'. ((int)$_POST['months'] == 1 ? '' : 's');

                    // Attempt to create a transaction
                    if($transaction = Payments::createTransaction($total, $itemName, 'Flashii Tenshi Purchase', 'http://'. Configuration::getLocalConfig('urls', 'main') .'/support')) {

                        // Store the amount of months in the global session array
                        $_SESSION['premiumMonths'] = (int)$_POST['months'];

                        header('Location: '. $transaction);
                        exit;

                    } else {

                        // Add page specific things
                        $renderData['page'] = [
                            'title'     => 'Information',
                            'redirect'  => '/support',
                            'message'   => 'An error has occurred while trying to create the transaction, try again later.'
                        ];

                    }

                }

                break;

            // Finalising the purchase
            case 'finish':

                // Check if the success GET request is set and is true
                if(isset($_GET['success']) && isset($_GET['paymentId']) && isset($_GET['PayerID']) && isset($_SESSION['premiumMonths'])) {

                    // Attempt to complete the transaction
                    if(Payments::completeTransaction($_GET['paymentId'], $_GET['PayerID'])) {

                        // execution of tenshification here

                        // Redirect to the complete
                        header('Location: ?mode=complete');
                        exit;

                    }

                }

                header('Location: /support?fail=true');
                break;

            case 'complete':
                print Templates::render('errors/premiumComplete.tpl', array_merge(['page' => ['title' => 'Premium purchase complete!']], $renderData));
                break;

            default:
                header('Location: /support');
                break;

        }

    }

    exit;

}

// Set default variables
$renderData['page'] = [
    'title'     => 'Support Flashii',
    'fail'      => isset($_GET['fail']),
    'whytenshi' => [
        [
            'Maintained by one person!',
            'The site, server and it\'s code are all maintained and paid for by one guy in the Netherlands.'
        ],
        [
            'No ads!',
            'Unlike a good chunk of the internet we don\'t make money by shoving ads in your face.'
        ],
        [
            'Helping us survive!',
            'It helps us with getting new hardware to make your Flashii Experience&trade; better and paying the bills to stay alive.'
        ],
        [
            'Extra features!',
            'You get some extra things to play with if you donate more than $5!'
        ]
    ],
    'tenshifeatures' => [
        [
            'A special colour',
            'Your username will be <span style="font-weight:bold;color:#EE9400;">orange</span> so you can be recognised in chat and elsewhere on the site!'
        ],
        [
            'Early access',
            'You get early access to new features before regular users such as access the developement domain.'
        ],
        [
            'Username',
            'You get the ability to change your username once a month.'
        ],
        [
            'User title',
            'You get the ability to change your user title whenever you wish to.'
        ],
        [
            'Chat logs',
            '<del>You can read the <a class="default" href="http://chat.flashii.net/logs" target="_blank">chat logs</a> where all the messages since the original launch are saved.</del><br />Temporarily unavailable due to permissioning issues, <a href="/u/303" class="default">go yell at malloc</a>.'
        ],
        [
            'Private channel',
            'You get your own Private Channel in the Chat.'
        ],
        [
            'Profile background',
            'You get the ability to set a custom background on your profile.'
        ],
        [
            'A good feeling',
            'You get the good feeling of helping me keep Flashii alive and growing (and of course the fact that you get all your special stuff that you can brag about to regular users).'
        ]
    ]
];

// Print page contents
print Templates::render('main/donate.tpl', $renderData);
