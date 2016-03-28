<?php
/**
 * Holds the account section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

/**
 * Account settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class AccountController extends Controller
{
    public function email()
    {
        return $this->go('account.email');
    }

    public function username()
    {
        return $this->go('account.username');
    }

    public function title()
    {
        return $this->go('account.usertitle');
    }

    public function password()
    {
        return $this->go('account.password');
    }

    public function ranks()
    {
        return $this->go('account.ranks');
    }
}
