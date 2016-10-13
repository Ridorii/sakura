<?php
/**
 * Holds the named quote tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;
use Sakura\Forum\Forum;
use Sakura\Forum\Post;
use Sakura\Perms\Forum as ForumPerms;
use Sakura\User;

/**
 * Named quote tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class NamedQuote extends TagBase
{
    /**
     * Parses the bbcode.
     * @param string $text
     * @return string
     */
    public static function parse($text, User $poster)
    {
        return preg_replace_callback(
            '/\[quote\=(#?)(.*?)\](.*)\[\/quote\]/s',
            function ($matches) use ($poster) {
                $quoting = $matches[2];
                $content = $matches[3];

                if ($matches[1] === '#') {
                    $post = new Post(intval($matches[2]));
                    $forum = new Forum($post->forum);

                    if ($post->id !== 0 && $forum->permission(ForumPerms::VIEW, $poster->id)) {
                        $link = route('forums.post', $post->id);

                        $quoting = "<a href='{$link}' style='color: {$post->poster->colour}' class='bbcode__quote-post'>{$post->poster->username}</a>";
                        $content = $post->parsed;
                    }
                }

                return "<blockquote class='bbcode__quote bbcode__quote--named'><small class='bbcode__quote-name'>{$quoting}</small>{$content}</blockquote>";
            },
            $text
        );
    }
}
