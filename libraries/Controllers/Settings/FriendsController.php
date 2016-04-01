<?php
/**
 * Holds the friends section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\Template;

/**
 * Friends settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class FriendsController extends Controller
{
    public function listing()
    {
        return Template::render('settings/friends/listing');
    }

    public function requests()
    {
        return Template::render('settings/friends/requests');
    }
}
