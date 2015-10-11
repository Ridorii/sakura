<?php
/*
 * Sakura Main Index
 */

// Declare Namespace
namespace Sakura;

// We don't use twig here
define('SAKURA_NO_TPL', true);

// Include components
require_once str_replace(basename(__DIR__), '', dirname(__FILE__)) . '_sakura/sakura.php';

// Path the changelog JSON
$changelog = json_decode(file_get_contents(ROOT . '_sakura/changelog.json'), true);

// Create variable to store HTML in
$changelogHTML = null;

// Format HTML
foreach (array_reverse($changelog['changelog'], true) as $revisionId => $revisionData) {
    $changelogHTML .= '<div class="release" id="r' . $revisionId . '">';

    $changelogHTML .= '<a href="#r'
    . $revisionId
    . '" class="title" style="color: '
    . $changelog['versions'][$revisionData[0]]
    . ';">Revision '
    . $revisionId
    . ' ('
    . ucfirst($revisionData[0])
    . ')</a>';

    unset($revisionData[0]);

    foreach (array_reverse($revisionData) as $id => $changeData) {
        $changelogHTML .= '<div id="r' . $revisionId . 'c' . $id . '">';

        switch ($changeData['type']) {
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

        $changelogHTML .= '<a class="changeuser" target="_blank" href="http://bitbucket.org/'
        . strtolower($changeData['user'])
        . '">';
        $changelogHTML .= $changeData['user'];
        $changelogHTML .= '</a>';

        $changelogHTML .= '</div>';
    }

    $changelogHTML .= '</div>';
}

// Get special template file
$tpl = file_get_contents(ROOT . '_sakura/templates/changeLog.tpl');

// Parse tags
$tpl = str_replace('{{ version }}', SAKURA_VERSION, $tpl);
$tpl = str_replace('{{ version_label }}', SAKURA_VLABEL, $tpl);
$tpl = str_replace('{{ version_type }}', SAKURA_STABLE ? 'Stable' : 'Development', $tpl);
$tpl = str_replace('{{ colour }}', SAKURA_COLOUR, $tpl);
$tpl = str_replace('{{ changeloghtml }}', $changelogHTML, $tpl);

// Print template
print $tpl;
