<?php
/*
 * A flexible comment system
 */

namespace Sakura;

class Comments
{
    public $comments = []; // Array containing comments
    private $commenters = []; // Array containing User objects
    public $category; // Comment category
    public $count = 0; // Amount of comments

    // Constructor
    public function __construct($category)
    {
        // Set category
        $this->category = $category;

        // Get the comments and assign them to $comments
        $comments = Database::fetch(
            'comments',
            true,
            [
                'comment_category' => [$this->category, '='],
                'comment_reply_to' => ['0', '='],
            ],
            ['comment_id', true]
        );

        // Feed them into the sorter
        $this->comments = $this->sortComments($comments);

    }

    // Sorting
    public function sortComments($comments)
    {

        // Create storage array
        $layer = [];

        // Sort comments
        foreach ($comments as $comment) {
            // Check if we already have an object for this user
            if (!array_key_exists($comment['comment_poster'], $this->commenters)) {
                // Create new object
                $this->commenters[$comment['comment_poster']] = new User($comment['comment_poster']);
            }

            // Attach the poster
            $comment['comment_poster'] = $this->commenters[$comment['comment_poster']];

            // Add post to posts array
            $layer[$comment['comment_id']] = $comment;

            // Up the comment count
            $this->count += 1;

            // Attempt to get replies from the database
            $replies = Database::fetch('comments', true, [
                'comment_category' => [$this->category, '='],
                'comment_reply_to' => [$comment['comment_id'], '='],
            ]);

            // Check if this was a reply to something
            if ($replies) {
                // Save the replies
                $layer[$comment['comment_id']]['comment_replies'] = $this->sortComments($replies);
            }
        }

        return $layer;

    }
}
