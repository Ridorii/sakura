<?php
/*
 * List BBcode
 * https://gist.github.com/jbowens/5646994
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\Parser;
use JBBCode\CodeDefinition;
use JBBCode\ElementNode;

/**
 * Implements a [list] code definition that provides the following syntax:
 *
 * [list]
 *   [*] first item
 *   [*] second item
 *   [*] third item
 * [/list]
 *
 */
class Lists extends CodeDefinition
{
    public function __construct()
    {
        $this->parseContent = true;
        $this->useOption = false;
        $this->setTagName('list');
        $this->nestLimit = -1;
    }

    public function asHtml(ElementNode $el)
    {
        $bodyHtml = '';
        foreach ($el->getChildren() as $child) {
            $bodyHtml .= $child->getAsHTML();
        }

        $listPieces = explode('[*]', $bodyHtml);
        unset($listPieces[0]);
        $listPieces = array_map(function ($li) {
            return '<li>'.$li.'</li>';
        }, $listPieces);
        return '<ul>'.implode('', $listPieces).'</ul>';
    }
}
