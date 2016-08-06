<?php
/**
 * Holds the user tag.
 * @package Sakura
 */

namespace Sakura\BBCode\Tags;

use Sakura\BBCode\TagBase;
use Sakura\User as UserObject;

/**
 * User tag.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class User extends TagBase
{
    /**
     * Parses the bbcode.
     * @param string $text
     * @return string
     */
    public static function parse($text)
    {
        return preg_replace_callback(
            '/\[user\]([0-9]+)\[\/user\]/s',
            function ($matches) {
                $user = UserObject::construct($matches[1]);
                $route = route('user.profile', $user->id);

                return "<a href='{$route}' class='default username' style='color: {$user->colour}; "
                    . "text-shadow: 0 0 .3em {$user->colour}; font-weight: bold'>{$user->username}</a>";
            },
            $text
        );
    }
}
