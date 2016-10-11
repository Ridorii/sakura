<?php
/**
 * Holds helpers for JavaScript.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\BBCode\Parser as BBParser;
use Sakura\CurrentSession;

/**
 * Helper controller.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class HelperController extends Controller
{
    /**
     * Parsed BBcode from a post request.
     * @return string
     */
    public function bbcodeParse()
    {
        return BBParser::toHTML(htmlentities($_POST['text'] ?? ''), CurrentSession::$user);
    }
}
