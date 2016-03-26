<?php
/**
 * Holds the code format bbcode class.
 *
 * @package Sakura
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\CodeDefinition;
use JBBCode\ElementNode;

/**
 * Code bbcode for JBBCode
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Code extends CodeDefinition
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("code");
    }

    /**
     * Compiles the code bbcode to HTML.
     *
     * @param ElementNode $el The JBBCode element node.
     *
     * @return mixed The compiled HTML.
     */
    public function asHtml(ElementNode $el)
    {
        $content = "";

        foreach ($el->getChildren() as $child) {
            $content .= $child->getAsBBCode();
        }

        return "<pre class='code'><code>{$content}</code></pre>";
    }
}
