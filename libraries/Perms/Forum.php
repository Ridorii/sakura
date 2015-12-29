<?php
/*
 * Forum permissions
 */

namespace Sakura\Perms;

/**
 * Class Forum
 * @package Sakura
 */
class Forum
{
    const VIEW = 1; // Can view this forum
    const REPLY = 2; // Can reply to threads in this forum
    const CREATE_THREADS = 4; // Can create threads in this forum
    const EDIT_OWN = 8; // Can edit their posts
    const DELETE_OWN = 16; // Can delete theirs posts
    const STICKY = 32; // Can sticky threads
    const ANNOUNCEMENT = 64; // Can announce threads
    const EDIT_ANY = 128; // Can edit any post
    const DELETE_ANY = 256; // Can delete any post
}
