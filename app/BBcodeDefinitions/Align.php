<?php
/**
 * Holds the text alignment bbcode class.
 *
 * @package Sakura
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\CodeDefinition;
use JBBCode\ElementNode;

/**
 * Text alignment bbcode for JBBCode
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Align extends CodeDefinition
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("align");
        $this->setUseOption(true);
    }

    /**
     * Creates compiled HTML from the align bbcode.
     *
     * @param ElementNode $el The JBBCode element node.
     *
     * @return string Compiled HTML.
     */
    public function asHtml(ElementNode $el)
    {
        $alignments = [
            'left',
            'center',
            'right',
        ];

        $content = "";

        foreach ($el->getChildren() as $child) {
            $content .= $child->getAsHTML();
        }

        $alignment = $el->getAttribute()['align'];

        if (!in_array($alignment, $alignments)) {
            return $el->getAsBBCode();
        }

        return "<div style='text-align: {$alignment};'>{$content}</div>";
    }
}
