<?php
/*
 * Text align bbcode
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\Parser;
use JBBCode\CodeDefinition;
use JBBCode\ElementNode;

class Align extends CodeDefinition
{
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("align");
        $this->setUseOption(true);
    }

    public function asHtml(ElementNode $el)
    {
        $alignments = [
            'left',
            'center',
            'right'
        ];

        $content = "";

        foreach ($el->getChildren() as $child) {
            $content .= $child->getAsHTML();
        }

        $alignment = $el->getAttribute()['align'];

        if (!in_array($alignment, $alignments)) {
            return $el->getAsBBCode();
        }

        return '<div style="text-align: ' . $alignment . ';">' . $content . '</div>';
    }
}
