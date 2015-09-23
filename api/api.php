<?php
/*
 * Sakura API
 */

// Declare Namespace
namespace Sakura;

// Define that this page won't require templating
define('SAKURA_NO_TPL', true);

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Change to content type to text/plain and set the charset to UTF-8
header('Content-Type: text/plain; charset=utf-8');

// Trim leading slashes
$path = ltrim($_SERVER['REQUEST_URI'], '/');

// Explode the elements
$elems = explode('/', $path);

// Correct the path if mod_rewrite isn't used
if ($elems[0] == explode('/', ltrim($_SERVER['PHP_SELF'], '/'))[0]) {
    // Remove the entry
    unset($elems[0]);

    // Resort the array
    $elems = array_values($elems);

    // Make sure there's at least one entry (even if empty)
    if (!isset($elems[0])) {
        $elems[] = "";
    }
}

// Make sure the GET requests aren't present in the last entry
if (strpos($elems[max(array_keys($elems))], '?')) {
    // If there are cut them all
    $elems[max(array_keys($elems))] = strstr($elems[max(array_keys($elems))], '?', true);
}

// Predefine the return variable
$return = [];

// Select API version
switch (isset($elems[0]) ? $elems[0] : false) {
    // API Version 1
    case 'v1':
        switch (isset($elems[1]) ? $elems[1] : false) {
            // Authentication
            case 'authenticate':
                switch (isset($elems[2]) ? $elems[2] : false) {
                    case 'login':
                        $return = ['success' => 'LOGIN_PROCESS_HERE'];
                        break;

                    default:
                        $return = ['error' => ['NO_DATA_REQ']];
                }
                break;

            default:
                $return = ['error' => ['NO_DATA_REQ']];
        }
        break;

    // Default fallback
    default:
        $return = ['error' => ['NO_API_VERSION']];
}

echo json_encode([$return], (isset($_GET['pretty']) ? JSON_PRETTY_PRINT : 0));
