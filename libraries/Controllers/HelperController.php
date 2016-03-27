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
    /**
     * Parsed BBcode from a post request
     *
     * @return string The parsed BBcode
     */
    public function bbcodeParse()
    {
        $text = isset($_POST['text']) ? $_POST['text'] : '';

        $text = BBcode::toHTML($text);

        return $text;
    }
}
