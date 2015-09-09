<?php
/*
 * Sakura Forum Posting
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Set location
$locId      = isset($_GET['f']) ? $_GET['f']    : (isset($_GET['t'])                        ? $_GET['t']    : (isset($_GET['p']) ? Forum::getTopicIdFromPostId($_GET['p']) : 0));
$locMode    = isset($_GET['f']) ? 'f'           : (isset($_GET['t']) || isset($_GET['p'])   ? 't'           : null);

// Set additional render data
$renderData = array_merge($renderData, [

    'posting' => [

        'emoticons' => Main::getEmotes(),
        'bbcodes'   => Main::getBBcodes()

    ]

]);

print Templates::render('forum/posting.tpl', $renderData);
