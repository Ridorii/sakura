<?php
/*
 * Code bbcode
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\Parser;
use JBBCode\CodeDefinition;
use JBBCode\ElementNode;

class Code extends CodeDefinition
{
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("code");
    }

    public function asHtml(ElementNode $el)
    {
        return preg_replace("#\n*\[code\]\n*(.*?)\n*\[/code\]\n*#s", '<pre class="code"><code>\\1</code></pre>', $el->getAsBBCode());
    }
}
