<?php
/*
 * Forum specific permissions class
 */

namespace Sakura\Forum;

use Sakura\Database;

/**
 * Class Permissions
 * @package Sakura
 */
class Permissions
{
    // Permissions
    const VIEW = 1;
    const REPLY = 2;
    const CREATE_THREADS = 4;
    const EDIT_OWN = 8;
    const DELETE_OWN = 16;
    const STICKY = 32;
    const ANNOUNCEMENT = 64;
    const EDIT_ANY = 128;
    const DELETE_ANY = 256;
}
