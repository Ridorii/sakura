<?php
/**
 * Holds the YouTube embed bbcode class.
 *
 * @package Sakura
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\CodeDefinition;
use JBBCode\ElementNode;

/**
 * YouTube video embedding bbcode for JBBCode
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class YouTube extends CodeDefinition
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("youtube");
    }

    /**
     * Compiles the YouTube bbcode to HTML
     *
     * @param ElementNode $el The JBBCode element node.
     *
     * @return string The compiled HTML.
     */
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
            return "<iframe width='640' height='390' src='https://www.youtube.com/embed/{$matches[1]}'
            frameborder='0' allowfullscreen></iframe>";
        }
    }
}
