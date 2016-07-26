<?php
/**
 * Holds the list bbcode class.
 *
 * @package Sakura
 */

namespace Sakura\BBcodeDefinitions;

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
 * @package Sakura
 * @author Jackson Owens <jackson_owens@alumni.brown.edu>
 */
class Lists extends CodeDefinition
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parseContent = true;
        $this->useOption = false;
        $this->setTagName('list');
        $this->nestLimit = -1;
    }

    /**
     * Compiles the list bbcode to HTML.
     *
     * @param ElementNode $el The JBBCode element node.
     *
     * @return string The compiled HTML list.
     */
    public function asHtml(ElementNode $el)
    {
        $bodyHtml = '';

        foreach ($el->getChildren() as $child) {
            $bodyHtml .= $child->getAsHTML();
        }

        $listPieces = explode('[*]', $bodyHtml);

        unset($listPieces[0]);

        $listPieces = array_map(function ($li) {
            return "<li>{$li}</li>";
        }, $listPieces);

        $list = implode('', $listPieces);

        return "<ul>{$list}</ul>";
    }
}
