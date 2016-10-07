<?php
/**
 * Holds the user tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;
use Sakura\User;

/**
 * User tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class UserTag extends TagBase
{
    /**
     * Parses the bbcode.
     * @param string $text
     * @return string
     */
    public static function parse($text, User $poster)
    {
        return preg_replace_callback(
            '/\[user\]([0-9]+)\[\/user\]/s',
            function ($matches) {
                $user = User::construct($matches[1]);
                $route = route('user.profile', $user->id);

                return "<a href='{$route}' class='bbcode__user' style='color: {$user->colour}; "
                    . "text-shadow: 0 0 .3em {$user->colour}; font-weight: bold'>{$user->username}</a>";
            },
            $text
        );
    }
}
