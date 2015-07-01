<?php
/*
 * Sakura API
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Trim leading slashes
$path = ltrim($_SERVER['REQUEST_URI'], '/');

// Explode the elements
$elems = explode('/', $path);

// Correct the path if mod_rewrite isn't used
if($elems[0] == explode('/', ltrim($_SERVER['PHP_SELF'], '/'))[0]) {

    // Remove the entry
    unset($elems[0]);

    // Resort the array
    $elems = array_values($elems);

}

print_r($elems);
