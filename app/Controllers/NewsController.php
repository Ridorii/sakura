<?php
/**
 * Holds the news controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

use Sakura\Config;
use Sakura\News\Category;
use Sakura\News\Post;
use Sakura\Template;

/**
 * News controller.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class NewsController extends Controller
{
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
            $message = "This news category doesn't exist!";

            Template::vars(compact('message'));

            return Template::render('global/information');
        }

        Template::vars(compact('category'));

        return Template::render('news/category');
    }

    public function post($id = 0)
    {
        // Create the post object
        $post = new Post($id);

        if (!$post->id) {
            $message = "This news post doesn't exist!";

            Template::vars(compact('message'));

            return Template::render('global/information');
        }

        Template::vars(compact('post'));

        return Template::render('news/post');
    }
}
