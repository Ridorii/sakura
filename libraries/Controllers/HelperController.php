<?php
/**
 * Holds helpers for JavaScript.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\BBcode;

/**
 * Helper controller.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class HelperController extends Controller
{
    public function bbcodeParse()
    {
        $text = isset($_POST['text']) ? $_POST['text'] : null;

        $text = BBcode::toHTML($text);

        return $text;
    }
}
