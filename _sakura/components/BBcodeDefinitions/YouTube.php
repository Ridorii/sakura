<?php
/*
 * YouTube BBcode
 * As displayed on this page http://jbbcode.com/docs
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\Parser;
use JBBCode\CodeDefinition;
use JBBCode\ElementNode;

class YouTube extends CodeDefinition
{
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("youtube");
    }

    public function asHtml(ElementNode $el)
    {
        $content = "";

        foreach ($el->getChildren() as $child) {
            $content .= $child->getAsBBCode();
        }

        $foundMatch = preg_match('/^([A-z0-9=\-]+?)$/i', $content, $matches);

        if (!$foundMatch) {
            return $el->getAsBBCode();
        } else {
            return "<iframe width=\"640\" height=\"390\" src=\"https://www.youtube.com/embed/".$matches[1]."\" frameborder=\"0\" allowfullscreen></iframe>";
        }
    }
}
