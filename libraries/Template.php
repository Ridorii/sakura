<?php
/**
 * Holds the templating engine class.
 *
 * @package Sakura
 */

namespace Sakura;

use Twig_Environment;
use Twig_Extension_StringLoader;
use Twig_Loader_Filesystem;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * Sakura wrapper for Twig.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Template
{
    /**
     * The variables passed on to the templating engine.
     *
     * @var array
     */
    private static $vars = [];

    /**
     * The templating engine.
     *
     * @var Twig_Environment
     */
    private static $template;

    /**
     * The template name.
     *
     * @var string
     */
    private static $templateName;

    /**
     * The file extension used by template files
     */
    const FILE_EXT = '.twig';

    /**
     * Set the template name.
     *
     * @param string $name The name of the template directory.
     */
    public static function set($name)
    {
        // Set variables
        self::$templateName = $name;

        // Reinitialise
        self::init();
    }

    /**
     * Initialise the templating engine.
     */
    public static function init()
    {
        // Initialise Twig Filesystem Loader
        $twigLoader = new Twig_Loader_Filesystem(ROOT . 'templates/' . self::$templateName);

        // Environment variable
        $twigEnv = [];

        // Enable caching
        if (Config::get('enable_tpl_cache')) {
            $twigEnv['cache'] = ROOT . 'cache/twig';
        }

        // And now actually initialise the templating engine
        self::$template = new Twig_Environment($twigLoader, $twigEnv);

        // Load String template loader
        self::$template->addExtension(new Twig_Extension_StringLoader());

        // Add route function
        self::$template->addFunction(new Twig_SimpleFunction('route', function ($name, $args = null) {
            return Router::route($name, $args);
        }));

        // Add config function
        self::$template->addFunction(new Twig_SimpleFunction('config', function ($name) {
            return Config::get($name);
        }));

        // Method of getting the currently active session id
        self::$template->addFunction(new Twig_SimpleFunction('session_id', 'session_id'));

        // json_decode filter (why doesn't this exist to begin with?)
        self::$template->addFilter(new Twig_SimpleFilter('json_decode', 'json_decode'));
    }

    /**
     * Merge the parse variables.
     *
     * @param array $vars The new variables.
     */
    public static function vars($vars)
    {
        self::$vars = array_merge(self::$vars, $vars);
    }

    /**
     * Render a template file.
     *
     * @param string $file The filename/path
     *
     * @return bool|string An error or the HTML.
     */
    public static function render($file)
    {
        try {
            return self::$template->render($file . self::FILE_EXT, self::$vars);
        } catch (\Exception $e) {
            return trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
