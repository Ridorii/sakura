<?php
/*
 * Sakura User Profiles
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Get user data
if(isset($_GET['u'])) {

    // Get the user's context
    $profile = new User($_GET['u']);

    // Assign the object to a renderData variable
    $renderData['profile'] = $profile;

    $renderData['page'] = [

        'notfound'  => false,
        'title'     => ($profile->data['id'] < 1 || $profile->data['password_algo'] == 'nologin' ? 'User not found!' : 'Profile of '. $profile->data['username']),
        'style'     => (!empty($profile->data['userData']['profileBackground']) ? [

            '#userBackground' => [

                'background'    => 'url("/bg/'. $profile->data['id'] .'") no-repeat center center / cover transparent !important',
                'position'      => 'fixed',
                'top'           => '0',
                'bottom'        => '0',
                'right'         => '0',
                'left'          => '0',
                'z-index'       => '-1'

            ]

        ] : null)

    ];

} else {

    $renderData['page'] = [

        'notfound'  => true,
        'title'     => 'User not found!'

    ];

}

// Print page contents
print Templates::render('main/profile.tpl', $renderData);
