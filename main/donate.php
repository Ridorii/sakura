<?php
/*
 * Sakura Support/Donate page
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Set default variables
$renderData['page'] = [
    'title'     => 'Support Flashii',
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
