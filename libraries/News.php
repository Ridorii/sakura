<?php
/**
 * Holds the news handler.
 * 
 * @package Sakura
 */

namespace Sakura;

/**
 * Used to serve news posts.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class News
{
    /**
     * Array containing news posts.
     * 
     * @var array
     */
    public $posts = [];

    /**
     * Constructor
     * 
     * @param mixed $category ID of the category that should be constructed.
     */
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

    /**
     * Get the amount of news posts.
     * 
     * @return int Number of posts.
     */
    public function getCount()
    {
        return count($this->posts);
    }

    /**
     * Check if a post exists in this category.
     * 
     * @param int $pid The ID of the post.
     * 
     * @return int If true the post it gets returns, else 0.
     */
    public function postExists($pid)
    {
        return array_key_exists($pid, $this->posts) ? $pid : 0;
    }
}
