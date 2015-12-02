<?php
/*
 * BBcode Wrapper
 */

namespace Sakura;

use JBBCode\Parser;
use JBBCode\DefaultCodeDefinitionSet;
use JBBCode\CodeDefinitionBuilder;

/**
 * Class BBcode
 * @package Sakura
 */
class BBcode
{
    // Parser container
    private $bbcode;

    // Constructor
    public function __construct($text = null) {
        // Create new parser class
        $this->bbcode = new Parser();

        // Add the standard definitions
        $this->loadStandardCodes();

        // Immediately parse the text if set
        if ($text != null) {
            $this->bbcode->parse($text);
        }
    }

    // Add basic bbcodes
    public function loadStandardCodes() {
        // Add the standard definitions
        $this->bbcode->addCodeDefinitionSet(new DefaultCodeDefinitionSet());

        // Header tag
        $builder = new CodeDefinitionBuilder('header', '<h2>{param}</h2>');
        $this->bbcode->addCodeDefinition($builder->build());

        // Strike tag
        $builder = new CodeDefinitionBuilder('s', '<del>{param}</del>');
        $this->bbcode->addCodeDefinition($builder->build());

        // Spoiler tag
        $builder = new CodeDefinitionBuilder('spoiler', '<div class="spoiler">{param}</div>');
        $this->bbcode->addCodeDefinition($builder->build());

        // Box tag
        $builder = new CodeDefinitionBuilder('box', '<div class="spoiler-box-container"><div class="spoiler-box-title">Click to open.</div><div class="spoiler-box-content">{param}</div></div>');
        $this->bbcode->addCodeDefinition($builder->build());

        // Box tag
        $builder = new CodeDefinitionBuilder('box', '<div class="spoiler-box-container"><div class="spoiler-box-title">{option}</div><div class="spoiler-box-content">{param}</div></div>');
        $builder->setUseOption(true);
        $this->bbcode->addCodeDefinition($builder->build());

        // Quote tag
        $builder = new CodeDefinitionBuilder('quote', '<blockquote>{param}</blockquote>');
        $this->bbcode->addCodeDefinition($builder->build());

        // Quote tag
        $builder = new CodeDefinitionBuilder('quote', '<h4>{option} wrote:</h4><blockquote>{param}</blockquote>');
        $builder->setUseOption(true);
        $this->bbcode->addCodeDefinition($builder->build());

        // Add special definitions (PHP files MUST have the same name as the definition class
        foreach (glob(ROOT . '_sakura/components/BBcodeDefinitions/*.php') as $ext) {
            // Include the class
            require_once $ext;
            
            // Clean the file path
            $ext = str_replace(ROOT . '_sakura/components/', '', $ext);
            $ext = str_replace('.php', '', $ext);
            $ext = str_replace('/', '\\', $ext);

            // Build the classname
            $className = __NAMESPACE__ . '\\' . $ext;

            // Add the BBcode definition
            $this->bbcode->addCodeDefinition(new $className);
        }
    }

    // Set text
    public function text($text) {
        $this->bbcode->parse($text);
    }

    // Get as HTML
    public function toHTML() {
        return nl2br($this->bbcode->getAsHtml());
    }

    // Get as BBmarkup
    public function toEditor() {
        return $this->bbcode->getAsBBCode();
    }

    // Get as plaintext
    public function toPlain() {
        return $this->bbcode->getAsText();
    }
}
