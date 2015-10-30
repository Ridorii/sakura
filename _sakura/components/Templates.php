<?php
/*
 * Template Engine Wrapper
 */

namespace Sakura;

use Twig_Environment;
use Twig_Extension_StringLoader;
use Twig_Loader_Filesystem;

/**
 * Class Templates
 * @package Sakura
 */
class Templates
{
    // Engine container, template folder name and options
    public static $engine;
    public static $template;
    public static $configuration;

    // Initialise templating engine and data
    public static function init($template)
    {

        // Set template folder name
        self::$template = $template;

        // Assign config path to a variable so we don't have to type it out twice
        $confPath = ROOT . '_sakura/templates/' . self::$template . '/template.ini';

        // Check if the configuration file exists
        if (!file_exists($confPath)) {
            trigger_error('Template configuration does not exist', E_USER_ERROR);
        }

        // Parse and store the configuration
        self::$configuration = parse_ini_file($confPath, true);

        // Make sure we're not using a manage template for the main site or the other way around
        if (defined('SAKURA_MANAGE') && (bool) self::$configuration['manage']['mode'] != (bool) SAKURA_MANAGE) {
            trigger_error('Incorrect template type', E_USER_ERROR);
        }

        // Start Twig
        self::twigLoader();
    }

    // Twig Loader
    private static function twigLoader()
    {

        // Initialise Twig Filesystem Loader
        $twigLoader = new Twig_Loader_Filesystem(ROOT . '_sakura/templates/' . self::$template);

        // Environment variable
        $twigEnv = [];

        // Enable caching
        if (Configuration::getConfig('enable_tpl_cache')) {
            $twigEnv['cache'] = ROOT . 'cache';
        }

        // And now actually initialise the templating engine
        self::$engine = new Twig_Environment($twigLoader, $twigEnv);

        // Load String template loader
        self::$engine->addExtension(new Twig_Extension_StringLoader());
    }

    // Render template
    public static function render($file, $tags)
    {
        try {
            return self::$engine->render($file, $tags);
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
