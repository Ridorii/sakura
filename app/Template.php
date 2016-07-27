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
    private static $engine;

    /**
     * The template name.
     *
     * @var string
     */
    public static $name;

    /**
     * The path to the client side resources
     *
     * @var string
     */
    public static $resources;

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
        self::$name = $name;

        // Set reources path
        self::$resources = '/content/data/' . self::$name;

        // Reinitialise
        self::init();
    }

    /**
     * Initialise the templating engine.
     */
    public static function init()
    {
        // Initialise Twig Filesystem Loader
        $twigLoader = new Twig_Loader_Filesystem(ROOT . 'views/' . self::$name);

        // Environment variable
        $twigEnv = [];

        // Enable caching
        if (config("performance.template_cache")) {
            $twigEnv['cache'] = ROOT . config("performance.cache_dir") . 'twig';
        }

        // And now actually initialise the templating engine
        self::$engine = new Twig_Environment($twigLoader, $twigEnv);

        // Load String template loader
        self::$engine->addExtension(new Twig_Extension_StringLoader());

        // Add route function
        self::$engine->addFunction(new Twig_SimpleFunction('route', function ($name, $args = null) {
            return Router::route($name, $args);
        }));

        // Add config function
        self::$engine->addFunction(new Twig_SimpleFunction('config', 'config'));

        // Add resource function
        self::$engine->addFunction(new Twig_SimpleFunction('resource', function ($path = "") {
            return self::$resources . "/{$path}";
        }));

        // Method of getting the currently active session id
        self::$engine->addFunction(new Twig_SimpleFunction('session_id', 'session_id'));

        // json_decode filter (why doesn't this exist to begin with?)
        self::$engine->addFilter(new Twig_SimpleFilter('json_decode', 'json_decode'));

        // byte_symbol filter
        self::$engine->addFilter(new Twig_SimpleFilter('byte_symbol', 'byte_symbol'));
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
            return self::$engine->render($file . self::FILE_EXT, self::$vars);
        } catch (\Exception $e) {
            return trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
