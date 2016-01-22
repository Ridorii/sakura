<?php
/*
 * The news page backend
 */

namespace Sakura;

/**
 * Class News
 * @package Sakura
 */
class News
{
    public $posts = []; // Posts array

    // Initialise the news object
    public function __construct($category)
    {

        // Get the news posts and assign them to $posts
        $posts = Database::fetch('news', true, ['news_category' => [$category, '=']], ['news_id', true]);

        // Attach poster data
        foreach ($posts as $post) {
            // Attach the poster
            $post['news_poster'] = User::construct($post['user_id']);

            // Load comments
            $post['news_comments'] = $this->comments = new Comments('news-' . $category . '-' . $post['news_id']);

            // Add post to posts array
            $this->posts[$post['news_id']] = $post;
        }
    }

    // Get the amount of posts
    public function getCount()
    {
        return count($this->posts);
    }

    // Get the amount of posts
    public function postExists($pid)
    {
        return array_key_exists($pid, $this->posts) ? $pid : 0;
    }
}
