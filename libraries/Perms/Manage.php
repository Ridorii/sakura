<?php
/*
 * Management permissions
 */

namespace Sakura\Perms;

/**
 * Class Manage
 * @package Sakura
 */
class Manage
{
    const USE_MANAGE = 1; // Can use manage
    const CAN_RESTRICT_USERS = 2; // Can change the status of users to restricted
}
