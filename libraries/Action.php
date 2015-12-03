<?php
/*
 * Action code handler
 */

namespace Sakura;

/**
 * Class Action
 * @package Sakura
 */
class Action
{
    private $actions = []; // Contains the action methods
    private $code = null; // Contains the action code we're working with

    // Constructor
    public function __construct($code = null)
    {
        // Populate $actions, sets $code (if not null)
    }

    // Generating an action code
    public function generate($action, $instructions, $user = 0)
    {
        // Takes an action, specifies instructions and optionally adds a target user
        //  stores this code in the database and assigns it to $this->code
        // This function should only work if $code is null
    }

    // Execute the procedure for this action code
    public function execute()
    {
        // Looks for the code in the database and executes the procedure
        // This and all functions below should only work if $this->code isn't null for obvious reasons
    }

    // Checking if a code is still valid
    public function validate()
    {
        // Checks if $this->code is still valid
    }

    // Make a code invalid
    public function invalidate()
    {
        // Invalidates the set action code
    }
}

/*
 * Concept
 * =======
 *     Action codes are a thing to have the system or something related generate an
 * md5(?) hashed string that they can enter into a box and have the system respond
 * by doing something.
 *     Said actions are stored in a database table and can be added, removed and
 * changed if needed. These actions will probably be stored using JSON.
 */
