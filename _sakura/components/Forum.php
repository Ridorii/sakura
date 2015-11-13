<?php
/*
 * Forum class
 */

namespace Sakura;

/**
 * Class Forum
 * @package Sakura
 */
class Forum
{
    // Variables
    public $id = 0;
    public $name = "Forum";
    public $description = "";
    public $link = "";
    public $category = 0;
    public $type = 0;
    public $icon = "";
    public $firstReply = [];
    public $lastReply = [];

    // Constructor
    public function __construct($forumId)
    {
        // Get the row from the database
        $forumRow = Database::fetch('forums', false, ['forum_id' => [$forumId, '=']]);

        // Populate the variables
        if (!$forumRow) {
            $this->id = $forumRow['forum_id'];
            $this->name = $forumRow['forum_name'];
            $this->description = $forumRow['forum_desc'];
            $this->link = $forumRow['forum_link'];
            $this->category = $forumRow['forum_category'];
            $this->type = $forumRow['forum_type'];
            $this->icon = $forumRow['forum_icon'];
        } else {
            // Else just set the ID to $forumId and imitate an blank forum
            $this->id = $forumId;
        }
    }

    // Subforums
    public function forums()
    {
        // Get all rows with the category id set to the forum id
        $forumRows = Database::fetch('forums', true, ['forum_category' => [$this->id, '=']]);

        // Get a storage array
        $forums = [];

        // Create new objects for each forum
        foreach ($forumRows as $forum) {
            $forums[$forum['forum_id']] = new Forum($forum['forum_id']);
        }

        // Return the forum objects
        return $forums;
    }

    // Last post
    public function lastPost()
    {
        // Return a post
        $postRow = Database::fetch('posts', false, ['forum_id' => [$this->id, '=']], ['post_id', true]);

    }

    // Thread count
    public function threadCount()
    {
        return Database::count('topics', ['forum_id', [$this->id, '=']])[0];
    }

    // Post count
    public function postCount()
    {
        return Database::count('posts', ['forum_id', [$this->id, '=']])[0];
    }
}
