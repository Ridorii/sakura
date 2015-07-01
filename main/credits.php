<?php
/*
 * Sakura Credits Page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Add page specific things
$renderData['page'] = [
    'title' => 'Sakura Credits'
];
$renderData['contributors'] = [
    'Flashwave'     => ['Main developer.', 'http://flash.moe'],
    'Kurasha244'    => ['Writing the base for the old backend.', 'http://saibateku.net'],
    'nookls'        => ['Being nookls.', 'http://nookls.org'],
    'MallocNull'    => ['Sock Chat and debug help.', 'http://aroltd.com'],
    'kamil'         => ['Pointing out mistakes and fixing them and literally writing the entire payments system.', 'http://krakow.pw'],
    'RandomGuy'     => ['Coming up with cool things to add and security stuff.', 'http://flashii.net/u/12']
];
$renderData['thirdParty'] = [
    'ReCAPTCHA'     => ['Providing the Captcha system we use.', 'http://recaptcha.net'],
    'Twig'          => ['The templating engine used by Sakura.', 'http://twig.sensiolabs.org/'],
    'Parsedown'     => ['A PHP markdown parser.', 'http://parsedown.org/'],
    'Defuse'        => ['Making the PBKDF2 implementation for PHP', 'http://defuse.ca/'],
    'PHPMailer'     => ['Writing PHPMailer and making e-mail sending a not pain in the ass', 'https://github.com/PHPMailer/PHPMailer'],
    'PayPal'        => ['Making a PayPal API', 'https://paypal.com']
];

// Print page contents
print Templates::render('main/credits.tpl', $renderData);
