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
    private static $bbcode = null;

    // Constructor
    public static function init()
    {
        // Create new parser class
        self::$bbcode = new Parser();

        // Add the standard definitions
        self::loadStandardCodes();
    }

    // Add basic bbcodes
    public static function loadStandardCodes()
    {
        // Add the standard definitions
        self::$bbcode->addCodeDefinitionSet(new DefaultCodeDefinitionSet());

        // Header tag
        $builder = new CodeDefinitionBuilder('header', '<h1>{param}</h1>');
        self::$bbcode->addCodeDefinition($builder->build());

        // Strike tag
        $builder = new CodeDefinitionBuilder('s', '<del>{param}</del>');
        self::$bbcode->addCodeDefinition($builder->build());

        // Spoiler tag
        $builder = new CodeDefinitionBuilder('spoiler', '<span class="spoiler">{param}</span>');
        self::$bbcode->addCodeDefinition($builder->build());

        // Box tag
        $builder = new CodeDefinitionBuilder('box', '<div class="spoiler-box-container"><div class="spoiler-box-title" onclick="toggleClass(this.parentNode.children[1], \'hidden\');">Click to open</div><div class="spoiler-box-content hidden">{param}</div></div>');
        self::$bbcode->addCodeDefinition($builder->build());

        // Box tag
        $builder = new CodeDefinitionBuilder('box', '<div class="spoiler-box-container"><div class="spoiler-box-title" onclick="toggleClass(this.parentNode.children[1], \'hidden\');">{option}</div><div class="spoiler-box-content hidden">{param}</div></div>');
        $builder->setUseOption(true);
        self::$bbcode->addCodeDefinition($builder->build());

        // Quote tag
        $builder = new CodeDefinitionBuilder('quote', '<blockquote><div class="quotee">Quote</div><div class="quote">{param}</div></blockquote>');
        self::$bbcode->addCodeDefinition($builder->build());

        // Quote tag
        $builder = new CodeDefinitionBuilder('quote', '<blockquote><div class="quotee">{option} wrote</div><div class="quote">{param}</div></blockquote>');
        $builder->setUseOption(true);
        self::$bbcode->addCodeDefinition($builder->build());

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
            self::$bbcode->addCodeDefinition(new $className);
        }
    }

    // Set text
    public static function text($text)
    {
        // Check if $bbcode is still null
        if (self::$bbcode == null) {
            self::init();
        }

        self::$bbcode->parse($text);
    }

    // Get as HTML
    public static function toHTML($text = null)
    {
        // Check if text isn't null
        if ($text !== null) {
            self::text($text);
        }

        $parsed = nl2br(self::$bbcode->getAsHtml());

        $parsed = Main::fixCodeTags($parsed);
        $parsed = Main::parseEmotes($parsed);

        return $parsed;
    }

    // Get as BBmarkup
    public static function toEditor($text = null)
    {
        // Check if text isn't null
        if ($text !== null) {
            self::text($text);
        }

        return self::$bbcode->getAsBBCode();
    }

    // Get as plaintext
    public static function toPlain($text = null)
    {
        // Check if text isn't null
        if ($text !== null) {
            self::text($text);
        }

        return self::$bbcode->getAsText();
    }
}
