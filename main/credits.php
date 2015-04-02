<?php
/*
 * Sakura Credits Page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once '/var/www/flashii.net/_sakura/sakura.php';

// Add page specific things
$renderData['page'] = [
    'title' => 'Sakura Credits'
];
$renderData['contributors'] = [
    'Flashwave'     => ['Main contributer and owner of the site.', 'http://flash.moe'],
    'Kurasha244'    => ['Writing the base for the old backend.', 'http://saibateku.net'],
    'nookls'        => ['Code guidance and debug help.', 'http://nookls.org'],
    'MallocNull'    => ['Sock Chat and debug help.', 'http://aroltd.com']
];
$renderData['thirdParty'] = [
    'ReCAPTCHA'     => ['Providing the Captcha system we use.', 'http://recaptcha.net'],
    'Twig'          => ['The templating engine used by Sakura.', 'http://twig.sensiolabs.org/'],
    'Parsedown'     => ['A PHP markdown parser.', 'http://parsedown.org/']
];

// Print page contents
print Main::tplRender('main/credits.tpl', $renderData);
