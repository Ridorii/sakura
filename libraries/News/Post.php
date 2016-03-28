<?php
/**
 * Holds the news post object.
 *
 * @package Sakura
 */

namespace Sakura\News;

use Sakura\Comment;
use Sakura\DB;
use Sakura\User;

/**
 * News post object.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Post
{
    const COMMENT_CATEGORY_FORMAT = "news-%s-%u";

    public $id = 0;
    public $category = "";
    public $user = 0;
    public $time = 0;
    public $title = "";
    public $text = "";
    private $commentCountCache = 0;
    private $commentsCache = [];

    public function __construct($id = 0)
    {
        // Get comment data from the database
        $data = DB::table('news')
            ->where('news_id', $id)
            ->get();

        // Check if anything was returned and assign data
        if ($data) {
            $data = $data[0];

            $this->id = $data->news_id;
            $this->category = $data->news_category;
            $this->user = $data->user_id;
            $this->time = $data->news_timestamp;
            $this->title = $data->news_title;
            $this->text = $data->news_content;
        }
    }

    public function save()
    {
        // Create submission data, insert and update take the same format
        $data = [
            'news_category' => $this->category,
            'user_id' => $this->user,
            'news_timestamp' => $this->time,
            'news_title' => $this->title,
            'news_content' => $this->text,
        ];

        // Update if id isn't 0
        if ($this->id) {
            DB::table('news')
                ->where('news_id', $this->id)
                ->update($data);
        } else {
            $this->id = DB::table('news')
                ->insertGetId($data);
        }
    }

    public function delete()
    {
        DB::table('news')
            ->where('news_id', $this->id)
            ->delete();

        $this->id = 0;
    }

    public function userData()
    {
        return User::construct($this->user);
    }

    public function commentCount()
    {
        if (!$this->commentCountCache) {
            $this->commentCountCache = DB::table('comments')
                ->where('comment_category', sprintf(self::COMMENT_CATEGORY_FORMAT, $this->category, $this->id))
                ->count();
        }

        return $this->commentCountCache;
    }

    public function comments()
    {
        if (!$this->commentsCache) {
            $commentIds = DB::table('comments')
                ->where('comment_category', sprintf(self::COMMENT_CATEGORY_FORMAT, $this->category, $this->id))
                ->orderBy('comment_id', 'desc')
                ->where('comment_reply_to', 0)
                ->get(['comment_id']);
            $commentIds = array_column($commentIds, 'comment_id');

            foreach ($commentIds as $comment) {
                $this->commentsCache[$comment] = new Comment($comment);
            }
        }

        return $this->commentsCache;
    }
}
