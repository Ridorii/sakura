<?php
/*
 * User bbcode
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\Parser;
use JBBCode\CodeDefinition;
use JBBCode\ElementNode;

class User extends CodeDefinition
{
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("user");
        $this->setUseOption(false);
        $this->setParseContent(false);
    }

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
