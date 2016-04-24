<?php
/**
 * Holds the font size bbcode class.
 *
 * @package Sakura
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\CodeDefinition;
use JBBCode\ElementNode;

/**
 * Size BBcode for JBBCode.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Size extends CodeDefinition
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("size");
        $this->setUseOption(true);
    }

    /**
     * Compiles the size bbcode to HTML
     *
     * @param ElementNode $el The JBBCode element node.
     *
     * @return string The compiled HTML.
     */
    public function asHtml(ElementNode $el)
    {
        $minSize = 0;
        $maxSize = 200;

        $content = "";

        foreach ($el->getChildren() as $child) {
            $content .= $child->getAsHTML();
        }

        $size = $el->getAttribute()['size'];

        if ($size < $minSize || $size > $maxSize) {
            return $el->getAsBBCode();
        }

        $size = $size / 100;

        return "<span style='font-size: {$size}em;'>{$content}</span>";
    }
}
