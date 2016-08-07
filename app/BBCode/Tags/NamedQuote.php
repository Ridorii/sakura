<?php
/**
 * Holds the named quote tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;
use Sakura\CurrentSession;
use Sakura\Forum\Forum;
use Sakura\Forum\Post;
use Sakura\Perms\Forum as ForumPerms;

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
    public static function parse($text)
    {
        return preg_replace_callback(
            '/\[quote\=(#?)(.*?)\](.*)\[\/quote\]/s',
            function ($matches) {
                $quoting = $matches[2];
                $content = $matches[3];

                if ($matches[1] === '#') {
                    $post = new Post(intval($matches[2]));
                    $forum = new Forum($post->forum);

                    if ($post->id !== 0 && $forum->permission(ForumPerms::VIEW, CurrentSession::$user->id)) {
                        $link = route('forums.post', $post->id);

                        $quoting = "<a href='{$link}' style='color: {$post->poster->colour}'>{$post->poster->username}</a>";
                        $content = $post->parsed;
                    }
                }

                return "<blockquote><small>{$quoting}</small>{$content}</blockquote>";
            },
            $text
        );
    }
}
