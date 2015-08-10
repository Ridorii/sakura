<?php
/*
 * Sakura Main Index
 */

// Declare Namespace
namespace Sakura;

// We don't use twig here
define('SAKURA_NO_TPL', true);

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) .'_sakura/sakura.php';

// Path the changelog JSON
$changelogFile  = json_decode(file_get_contents(ROOT .'_sakura/changelog.json'), true);
$changelog      = array();

// Create version categories
foreach($changelogFile['versions'] as $name => $data) {
    
    // Reverse the array
    $data['revisions'] = array_reverse($data['revisions'], true);

    foreach($data['revisions'] as $rev) {

        $changelog[$rev]['name']      = $name;
        $changelog[$rev]['colour']    = $data['colour'];
        $changelog[$rev]['changes']   = array();

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
foreach($changelog as $revisionId => $revisionData) {

    $changelogHTML .= '<div class="release" id="r'. $revisionId .'">';

    $changelogHTML .= '<a href="#r'. $revisionId .'" class="title" style="color: '. $revisionData['colour'] .';">Revision '. $revisionId .' ('. $revisionData['name'] .')</a>';

    foreach($revisionData['changes'] as $id => $changeData) {

        $changelogHTML .= '<div id="r'. $revisionId .'c'. $id .'">';

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
$tpl = file_get_contents(ROOT .'_sakura/templates/changeLog.tpl');

// Parse tags
$tpl = str_replace('{{ version }}',         SAKURA_VERSION,                             $tpl);
$tpl = str_replace('{{ version_label }}',   SAKURA_VLABEL,                              $tpl);
$tpl = str_replace('{{ version_type }}',    SAKURA_STABLE ? 'Stable' : 'Development',   $tpl);
$tpl = str_replace('{{ colour }}',          SAKURA_COLOUR,                              $tpl);
$tpl = str_replace('{{ changeloghtml }}',   $changelogHTML,                             $tpl);

// Print template
print $tpl;
