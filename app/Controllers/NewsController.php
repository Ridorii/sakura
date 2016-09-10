<?php
/**
 * Holds the news controller.
 * @package Sakura
 */

namespace Sakura\Controllers;

use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Sakura\Config;
use Sakura\News\Category;
use Sakura\News\Post;

/**
 * News controller.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class NewsController extends Controller
{
    /**
     * Shows all posts in a specific category.
     * @param string $category
     * @return string
     */
    public function category($category = '')
    {
        // Check if the category is set
        if ($category === '') {
            // Fetch the default category from the config
            $category = config('general.news');
        }

        // Create the category object
        $category = new Category($category);

        if (!$category->posts()) {
            throw new HttpRouteNotFoundException();
        }

        return view('news/category', compact('category'));
    }

    /**
     * Returns a news post.
     * @param int $id
     * @return string
     */
    public function post($id = 0)
    {
        // Create the post object
        $post = new Post($id);

        if (!$post->id) {
            throw new HttpRouteNotFoundException();
        }

        return view('news/post', compact('post'));
    }
}
