<?php
/*
 * BBcode main
 */

namespace Sakura\BBcode;

class BBcode
{
    // Text
    private $text;
    private $seed;

    // Contructor
    public function __construct($text = "", $seed = '9001')
    {
        $this->setText($text);
        $this->seed = $seed;
    }

    // Set text
    public function setText($text)
    {
        $this->text = $text;
    }

    // Convert to storage format
    public function toStore()
    {
        // Create new Store
        $store = new Store($this->text, $this->seed);

        // Parse
        $store = $store->generate();

        // And return
        return $store;
    }

    // Convert to HTML
    public function toHTML()
    {
        // Create new Parse
        $parse = new Parse($this->text, $this->seed);

        // Parse
        $parse = $parse->parse();

        // And return
        return $parse;
    }

    // Convert to plain text
    public function toEditor()
    {
        // Create new Parse
        $parse = new Parse($this->text, $this->seed);

        // Parse
        $parse = $parse->toEditor();

        // And return
        return $parse;
    }
}
