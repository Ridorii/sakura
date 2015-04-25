<?php
/*
 * Sakura User Profiles
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Catch old profile API and return error
if(isset($_REQUEST['data']))
    die(json_encode(['error' => true]));

var_dump(@$_REQUEST);
