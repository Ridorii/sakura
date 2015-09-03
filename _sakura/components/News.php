<?php
/*
 * The news page backend
 */

namespace Sakura;

class News {

    // Posts array
    public $posts = [];

    // Initialise the news object
    function __construct($category) {

        // Get the news posts and assign them to $posts
        $this->posts = Database::fetch('news', true, ['category' => [$category, '=']], ['id', true]);

    }

}
