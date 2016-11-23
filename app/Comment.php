<?php
/**
 * Holds the comment object.
 * @package Sakura
 */

namespace Sakura;

/**
 * Comment object.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Comment
{
    /**
     * The comment identifier.
     * @var int
     */
    public $id = 0;

    /**
     * The category this comment belongs to.
     * @var string
     */
    public $category = "";

    /**
     * The timestamp this comment was posted at.
     * @var int
     */
    public $time = 0;

    /**
     * The Id of the user that posted this comment.
     * @var int
     */
    public $user = 0;

    /**
     * The Id of the comment this comment is replying to.
     * @var int
     */
    public $reply = 0;

    /**
     * The content of this comment.
     * @var string
     */
    public $text = "";

    /**
     * The upvotes this comment has received.
     * @var int
     */
    public $upvotes = 0;

    /**
     * The downvotes this comment has received.
     * @var int
     */
    public $downvotes = 0;

    /**
     * A cache of reply objects.
     * @var array
     */
    private $replyCache = [];

    /**
     * A cache of the parsed text content.
     * @var string
     */
    private $parsedCache = "";

    /**
     * Constructor.
     * @var int $id
     */
    public function __construct($id = 0)
    {
        // Get comment data from the database
        $data = DB::table('comments')
            ->where('comment_id', $id)
            ->first();

        // Check if anything was returned and assign data
        if ($data) {
            $this->id = $data->comment_id;
            $this->category = $data->comment_category;
            $this->time = $data->comment_timestamp;
            $this->user = $data->comment_poster;
            $this->reply = $data->comment_reply_to;
            $this->text = $data->comment_text;

            $this->getVotes();
        }
    }

    /**
     * Saving changes made to this comment.
     */
    public function save()
    {
        // Create submission data, insert and update take the same format
        $data = [
            'comment_category' => $this->category,
            'comment_timestamp' => $this->time,
            'comment_poster' => $this->user,
            'comment_reply_to' => $this->reply,
            'comment_text' => $this->text,
        ];

        // Update if id isn't 0
        if ($this->id) {
            DB::table('comments')
                ->where('comment_id', $this->id)
                ->update($data);
        } else {
            $this->id = DB::table('comments')
                ->insertGetId($data);
        }
    }

    /**
     * Delete this comment.
     */
    public function delete()
    {
        foreach ($this->replies() as $reply) {
            $reply->delete();
        }

        DB::table('comments')
            ->where('comment_id', $this->id)
            ->delete();

        $this->id = 0;
    }

    /**
     * Gets and caches the upvotes.
     */
    private function getVotes()
    {
        $this->upvotes = intval(DB::table('comment_votes')
            ->where('vote_comment', $this->id)
            ->where('vote_state', 1)
            ->count());
        $this->downvotes = intval(DB::table('comment_votes')
            ->where('vote_comment', $this->id)
            ->where('vote_state', 0)
            ->count());
    }

    /**
     * Gets the parsed comment text
     * @return string
     */
    public function parsed()
    {
        if (!$this->parsedCache) {
            $this->parsedCache = BBCode\Parser::parseEmoticons(clean_string($this->text));
        }

        return $this->parsedCache;
    }

    /**
     * Get the replies to this comment.
     * @return array
     */
    public function replies()
    {
        if (!$this->replyCache) {
            $commentIds = DB::table('comments')
                ->where('comment_reply_to', $this->id)
                ->orderBy('comment_id', 'desc')
                ->get(['comment_id']);
            $commentIds = array_column($commentIds, 'comment_id');

            foreach ($commentIds as $comment) {
                $this->replyCache[$comment] = new Comment($comment);
            }
        }

        return $this->replyCache;
    }

    /**
     * Gets the user object of the poster.
     * @return User
     */
    public function userData()
    {
        return User::construct($this->user);
    }

    /**
     * Casts a vote on a comment.
     * @param int $user
     * @param bool $vote
     */
    public function vote($user, $vote)
    {
        $vote = $vote ? '1' : '0';

        // Attempt to get previous vote
        $previous = DB::table('comment_votes')
            ->where('vote_user', $user)
            ->where('vote_comment', $this->id)
            ->get();

        // Check if anything was returned
        if ($previous) {
            // Check if the vote that's being casted is the same
            if ($previous[0]->vote_state == $vote) {
                // Delete the vote
                DB::table('comment_votes')
                    ->where('vote_user', $user)
                    ->where('vote_comment', $this->id)
                    ->delete();
            } else {
                // Otherwise update the vote
                DB::table('comment_votes')
                    ->where('vote_user', $user)
                    ->where('vote_comment', $this->id)
                    ->update([
                        'vote_state' => $vote,
                    ]);
            }
        } else {
            // Create a vote
            DB::table('comment_votes')
                ->insert([
                    'vote_user' => $user,
                    'vote_comment' => $this->id,
                    'vote_state' => $vote,
                ]);
        }

        $this->getVotes();
    }
}
