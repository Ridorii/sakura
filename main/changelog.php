<?php
/*
 * Sakura Main Index
 */

// Declare Namespace
namespace Sakura;

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Path the changelog JSON
$changelogFile  = json_decode(file_get_contents(ROOT .'_sakura/changelog.json'), true);
$changelog      = array();

// Create version categories
foreach($changelogFile['versions'] as $name => $data) {
    
    // Reverse the array
    $data['builds'] = array_reverse($data['builds'], true);

    foreach($data['builds'] as $build) {

        $changelog[$build]['name']      = $name;
        $changelog[$build]['colour']    = $data['colour'];
        $changelog[$build]['changes']   = array();

    }

}

// Sort changes properly
foreach($changelogFile['changelog'] as $ver => $data) {

    // Reverse the array
    $data = array_reverse($data, true);

    // Add the log to the array
    foreach($data as $id => $change)
        $changelog[$ver]['changes'][$id] = $change;

}

// Add a thing to only get the json
if(isset($_REQUEST['getjson'])) {

    // Print encoded json and exit
    print json_encode($changelog);
    exit;

}

// Create variable to store HTML in
$changelogHTML = null;

// Format HTML
foreach($changelog as $build => $buildData) {

    $changelogHTML .= '<div class="release" id="b'. $build .'">';

    $changelogHTML .= '<a href="#b'. $build .'" class="title" style="color: '. $buildData['colour'] .';">Build '. $build .' ('. $buildData['name'] .')</a>';

    foreach($buildData['changes'] as $id => $changeData) {

        $changelogHTML .= '<div id="b'. $build .'c'. $id .'">';

        switch($changeData['type']) {

            case 'ADD':
                $changelogHTML .= '<span class="tag addition-tag">Added</span>';
                break;

            case 'REM':
                $changelogHTML .= '<span class="tag removal-tag">Removed</span>';
                break;

            case 'FIX':
                $changelogHTML .= '<span class="tag fixed-tag">Fixed</span>';
                break;

            case 'UPD':
                $changelogHTML .= '<span class="tag update-tag">Updated</span>';
                break;

            default:
                $changelogHTML .= '<span class="tag">Unknown</span>';

        }

        $changelogHTML .= '<span class="changedesc">';
        $changelogHTML .= $changeData['change'];
        $changelogHTML .= '</span>';

        $changelogHTML .= '</div>';

    }

    $changelogHTML .= '</div>';

}

// Get special template file
$tpl = file_get_contents(ROOT .'_sakura/templates/versionInfo.tpl');

// Parse tags
$tpl = str_replace('{{ version }}',         SAKURA_VERSION, $tpl);
$tpl = str_replace('{{ version_label }}',   SAKURA_VLABEL,  $tpl);
$tpl = str_replace('{{ version_type }}',    SAKURA_VTYPE,   $tpl);
$tpl = str_replace('{{ colour }}',          SAKURA_COLOUR,  $tpl);
$tpl = str_replace('{{ changeloghtml }}',   $changelogHTML, $tpl);

// Print template
print $tpl;
