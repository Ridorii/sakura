<?php
/**
 * Holds the comment object.
 *
 * @package Sakura
 */

namespace Sakura;

/**
 * Comment object.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Comment
{
    public $id = 0;
    public $category = "";
    public $time = 0;
    public $user = 0;
    public $reply = 0;
    public $text = "";
    public $upvotes = 0;
    public $downvotes = 0;
    private $replyCache = [];
    private $parsedCache = "";

    public function __construct($id = 0)
    {
        // Get comment data from the database
        $data = DB::table('comments')
            ->where('comment_id', $id)
            ->get();

        // Check if anything was returned and assign data
        if ($data) {
            $data = $data[0];

            $this->id = $data->comment_id;
            $this->category = $data->comment_category;
            $this->time = $data->comment_timestamp;
            $this->user = $data->comment_poster;
            $this->reply = $data->comment_reply_to;
            $this->text = $data->comment_text;

            $this->getVotes();
        }
    }

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

    private function getVotes()
    {
        $votes = DB::table('comment_votes')
            ->where('vote_comment', $this->id)
            ->get();

        $this->upvotes = 0;
        $this->downvotes = 0;

        foreach ($votes as $vote) {
            if (intval($vote->vote_state) !== 0) {
                $this->upvotes += 1;
            } else {
                $this->downvotes += 1;
            }
        }
    }

    public function parsed()
    {
        if (!$this->parsedCache) {
            $this->parsedCache = BBcode::parseEmoticons(Utils::cleanString($this->text));
        }

        return $this->parsedCache;
    }

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

    public function userData()
    {
        return User::construct($this->user);
    }

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
