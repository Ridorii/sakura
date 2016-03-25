<?php
/**
 * Holds the forum post quoting bbcode class.
 *
 * @package Sakura
 */

namespace Sakura\BBcodeDefinitions;

use JBBCode\CodeDefinition;
use JBBCode\ElementNode;
use Sakura\Forum\Forum;
use Sakura\Forum\Post;
use Sakura\Perms\Forum as ForumPerms;
use Sakura\Router;
use Sakura\User;

/**
 * Quote BBcode for JBBCode.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Quote extends CodeDefinition
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("quote");
        $this->setUseOption(true);
        $this->setParseContent(true);
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
        global $currentUser;

        $attr = $el->getAttribute()['quote'];

        if (substr($attr, 0, 1) === '#') {
            $postId = substr($attr, 1);
            $post = new Post($postId);
            $forum = new Forum($post->forum);

            if ($post->id !== 0
                && $forum->permission(ForumPerms::VIEW, $currentUser->id)) {
                $postLink = Router::route('forums.post', $post->id);

                $content = "<blockquote><div class='quotee'><a href='{$postLink}' style='color: inherit;'>"
                    . "<span style='color: {$post->poster->colour}'>"
                    . "{$post->poster->username}</span> wrote</a></div>"
                    . "<div class='quote'>{$post->parsed}</div></blockquote>";

                return $content;
            }
        }

        $content = "";

        foreach ($el->getChildren() as $child) {
            $content .= $child->getAsHTML();
        }

        return "<blockquote><div class='quotee'>{$attr} wrote</div>
        <div class='quote'>{$content}</div></blockquote>";
    }
}
