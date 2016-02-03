<?php
/**
 * Holds the username linking bbcode class.
 * 
 * @package Sakura
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\Parser;
use JBBCode\CodeDefinition;
use JBBCode\ElementNode;

/**
 * Username BBcode for JBBCode.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class User extends CodeDefinition
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("user");
        $this->setUseOption(false);
        $this->setParseContent(false);
    }

    /**
     * Compiles the user bbcode to HTML
     * 
     * @param ElementNode $el The JBBCode element node.
     * 
     * @return string The compiled HTML.
     */
    public function asHtml(ElementNode $el)
    {
        $content = "";

        foreach ($el->getChildren() as $child) {
            $content .= \Sakura\Utils::cleanString($child->getAsText(), true);
        }

        $user = \Sakura\User::construct($content);
        $urls = new \Sakura\Urls();

        return '<a class="default username" href="' . $urls->format('USER_PROFILE', [$user->id]) . '" style="color: ' . $user->colour . '; text-shadow: 0 0 .3em ' . $user->colour . '; font-weight: bold;">' . $user->username . '</a>';
    }
}
