<?php
/**
 * Holds the holds the comment handler class.
 * 
 * @package Sakura
 */

namespace Sakura;

/**
 * Handles and serves comments on pages.
 * Needs a reimplementation.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Comments
{
    /**
     * The array containing the comments.
     * 
     * @var array
     */
    public $comments = [];

    /**
     * The comment category.
     * 
     * @var string
     */
    public $category;

    /**
     * The amount of comments.
     * @var int
     */
    public $count = 0;

    /**
     * Constructor.
     * 
     * @param mixed $category The category that comments should be fetched from.
     */
    public function __construct($category)
    {
        // Set category
        $this->category = $category;

        // Get the comments and assign them to $comments
        $comments = DB::prepare('SELECT * FROM `{prefix}comments` WHERE `comment_category` = :category AND `comment_reply_to` = 0 ORDER BY `comment_id` DESC');
        $comments->execute([
            'category' => $this->category,
        ]);
        $comments = $comments->fetchAll(\PDO::FETCH_ASSOC);

        // Feed them into the sorter
        $this->comments = $this->sortComments($comments);
    }

    /**
     * Sort the comments.
     * 
     * @param array $comments Array containing comments.
     * 
     * @return array Array containing the sorted comments.
     */
    public function sortComments($comments)
    {
        // Create storage array
        $layer = [];

        // Sort comments
        foreach ($comments as $comment) {
            // Attach the poster
            $comment['comment_poster'] = User::construct($comment['comment_poster']);
            $comment['comment_text'] = BBcode::parseEmoticons(Utils::cleanString($comment['comment_text']));

            // Get likes and dislikes
            $votes = $this->getVotes($comment['comment_id']);
            $comment['comment_likes'] = 0;
            $comment['comment_dislikes'] = 0;

            // Store amount in their respective variables
            foreach ($votes as $vote) {
                if ($vote['vote_state']) {
                    $comment['comment_likes'] += 1;
                } else {
                    $comment['comment_dislikes'] += 1;
                }
            }

            // Add post to posts array
            $layer[$comment['comment_id']] = $comment;

            // Up the comment count
            $this->count += 1;

            // Attempt to get replies from the database
            $replies = DB::prepare('SELECT * FROM `{prefix}comments` WHERE `comment_category` = :category AND `comment_reply_to` = :thread');
            $replies->execute([
                'category' => $this->category,
                'thread' => $comment['comment_id'],
            ]);
            $replies = $replies->fetchAll(\PDO::FETCH_ASSOC);

            // Check if this was a reply to something
            if ($replies) {
                // Save the replies
                $layer[$comment['comment_id']]['comment_replies'] = $this->sortComments($replies);
            }
        }

        return $layer;
    }

    /**
     * Get a single comment.
     * 
     * @param int $cid ID of the comment.
     * 
     * @return array The comment.
     */
    public function getComment($cid)
    {
        // Get from database
        $comment = DB::prepare('SELECT * FROM `{prefix}comments` WHERE `comment_id` = :id');
        $comment->execute([
            'id' => $cid,
        ]);
        return $comment->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get the votes for a comment.
     * 
     * @param int $cid ID of the comment.
     * 
     * @return array The votes.
     */
    public function getVotes($cid)
    {
        // Get from database
        $comment = DB::prepare('SELECT * FROM `{prefix}comment_votes` WHERE `vote_comment` = :id');
        $comment->execute([
            'id' => $cid,
        ]);
        return $comment->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Creating a new comment.
     * 
     * @param int $uid ID of the user creating the comment.
     * @param int $reply ID of the comment that is being replied to.
     * @param string $content Contents of the comment.
     * 
     * @return array Response identifier.
     */
    public function makeComment($uid, $reply, $content)
    {
        // Check if the comment is long enough
        if (strlen($content) < Config::get('comment_min_length')) {
            return [0, 'TOO_SHORT'];
        }

        // Check if the comment isn't too long
        if (strlen($content) > Config::get('comment_max_length')) {
            return [0, 'TOO_LONG'];
        }

        // Insert into database
        DB::prepare('INSERT INTO `{prefix}comments` (`comment_category`, `comment_timestamp`, `comment_poster`, `comment_reply_to`, `comment_text`) VALUES (:cat, :time, :user, :thread, :text)')
            ->execute([
            'cat' => $this->category,
            'time' => time(),
            'user' => $uid,
            'thread' => (int) $reply,
            'text' => $content,
        ]);

        // Return success
        return [1, 'SUCCESS'];
    }

    /**
     * Making a vote.
     * 
     * @param int $uid User making this vote.
     * @param int $cid ID of the comment that is being voted on.
     * @param int $mode Positive or negative vote.
     * 
     * @return bool Always returns true.
     */
    public function makeVote($uid, $cid, $mode)
    {
        // Attempt to get previous vote
        $vote = DB::prepare('SELECT * FROM `{prefix}comment_votes` WHERE `vote_user` = :user AND `vote_comment` = :comment');
        $vote->execute([
            'user' => $uid,
            'comment' => $cid,
        ]);
        $vote = $vote->fetch(\PDO::FETCH_ASSOC);

        // Check if anything was returned
        if ($vote) {
            // Check if the vote that's being casted is the same
            if ($vote['vote_state'] == $mode) {
                // Delete the vote
                DB::prepare('DELETE FROM `{prefix}comment_votes` WHERE `vote_user` = :user AND `vote_comment` = :comment')
                    ->execute([
                    'user' => $uid,
                    'comment' => $cid,
                ]);
            } else {
                // Otherwise update the vote
                DB::prepare('UPDATE `{prefix}comment_votes` SET `vote_state` = :state WHERE `vote_user` = :user AND `vote_comment` = :comment')
                    ->execute([
                    'state' => $mode,
                    'user' => $uid,
                    'comment' => $cid,
                ]);
            }
        } else {
            // Create a vote
            DB::prepare('INSERT INTO `{prefix}comment_votes` (`vote_user`, `vote_comment`, `vote_state`) VALUES (:user, :comment, :state)')
                ->execute([
                'user' => $uid,
                'comment' => $cid,
                'state' => $mode,
            ]);
        }

        return true;
    }

    /**
     * Remove a comment
     * 
     * @param int $cid ID of the comment to remove.
     */
    public function removeComment($cid)
    {
        // Remove from database
        DB::prepare('DELETE FROM `{prefix}comments` WHERE `comment_id` = :id')
            ->execute([
            'id' => $cid,
        ]);
    }
}
