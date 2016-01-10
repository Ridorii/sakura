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
    private $posters = []; // Posters array (so we don't create a new user object every time)

    // Initialise the news object
    public function __construct($category)
    {

        // Get the news posts and assign them to $posts
        $posts = Database::fetch('news', true, ['news_category' => [$category, '=']], ['news_id', true]);

        // Attach poster data
        foreach ($posts as $post) {
            // Check if we already have an object for this user
            if (!array_key_exists($post['user_id'], $this->posters)) {
                // Create new object
                $this->posters[$post['user_id']] = User::construct($post['user_id']);
            }

            // Parse the news post
            $post['news_content_parsed'] = Main::mdParse($post['news_content']);

            // Attach the poster
            $post['news_poster'] = $this->posters[$post['user_id']];

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
