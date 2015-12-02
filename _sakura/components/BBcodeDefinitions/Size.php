<?php
/*
 * Font size BBcode
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\Parser;
use JBBCode\CodeDefinition;
use JBBCode\ElementNode;

class Size extends CodeDefinition {
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("size");
        $this->setUseOption(true);
    }

    public function asHtml(ElementNode $el)
    {
        $minSize = 0;
        $maxSize = 200;

        $content = "";

        foreach($el->getChildren() as $child) {
            $content .= $child->getAsHTML();
        }

        $size = $el->getAttribute()['size'];

        if ($size < $minSize || $size > $maxSize) {
            return $el->getAsBBCode();
        }

        return '<span style="font-size: ' . ($size / 100) . 'em;">' . $content . '</span>';
    }
}
