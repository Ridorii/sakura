<?php
/**
 * Holds the news category object.
 *
 * @package Sakura
 */

namespace Sakura\News;

use Sakura\DB;

/**
 * News category object.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Category
{
    public $name = "";

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function posts($limit = 0)
    {
        $postIds = DB::table('news')
            ->where('news_category', $this->name)
            ->orderBy('news_id', 'desc');
        if ($limit) {
            $postIds->limit($limit);
        }
        $postIds = $postIds->get(['news_id']);
        $postIds = array_column($postIds, 'news_id');

        $posts = [];

        foreach ($postIds as $post) {
            $posts[$post] = new Post($post);
        }

        return $posts;
    }
}
