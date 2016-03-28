<?php
/**
 * Holds the base settings controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\Controllers\Controller as BaseController;
use Sakura\Urls;

/**
 * Base controller (which other controllers should extend on).
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Controller extends BaseController
{
    private $urls;

    public function __construct()
    {
        $this->urls = new Urls();
    }

    public function go($location)
    {
        $location = explode('.', $location);

        $url = $this->urls->format('SETTING_MODE', $location, null, false);

        return header("Location: {$url}");
    }
}
