<?php
/**
 * Holds the templating engine class.
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
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Template
{
    /**
     * The variables passed on to the templating engine.
     * @var array
     */
    private static $vars = [];

    /**
     * The templating engine.
     * @var Twig_Environment
     */
    private static $engine;

    /**
     * The template name.
     * @var string
     */
    public static $name;

    /**
     * The file extension used by template files.
     */
    const FILE_EXT = '.twig';

    /**
     * List of utility functions to add to templating.
     * @var array
     */
    protected static $utilityFunctions = [
        'route',
        'config',
        'session_id',
    ];

    /**
     * List of utility filters to add to templating.
     * @var array
     */
    protected static $utilityFilters = [
        'json_decode',
        'byte_symbol',
    ];

    /**
     * Set the template name.
     * @param string $name
     */
    public static function set($name)
    {
        // Set variables
        self::$name = $name;

        // Reinitialise
        self::init();
    }

    /**
     * Initialise the templating engine.
     */
    public static function init()
    {
        $views_dir = ROOT . 'resources/views/';

        // Initialise Twig Filesystem Loader
        $loader = new Twig_Loader_Filesystem();

        foreach (glob("{$views_dir}*") as $dir) {
            $key = basename($dir);

            if ($key === self::$name) {
                $key = '__main__';
            }

            $loader->addPath($dir, $key);
        }

        // Environment variable
        $env = [
            'cache' => config("performance.template_cache")
            ? realpath(ROOT . config("performance.cache_dir") . 'views')
            : false,
            'auto_reload' => true,
            'debug' => config("dev.twig_debug"),
        ];

        // And now actually initialise the templating engine
        self::$engine = new Twig_Environment($loader, $env);

        // Load String template loader
        self::$engine->addExtension(new Twig_Extension_StringLoader());

        // Add utility functions
        foreach (self::$utilityFunctions as $function) {
            self::$engine->addFunction(new Twig_SimpleFunction($function, $function));
        }

        // Add utility filters
        foreach (self::$utilityFilters as $filter) {
            self::$engine->addFilter(new Twig_SimpleFilter($filter, $filter));
        }
    }

    /**
     * Merge the parse variables.
     * @param array $vars
     */
    public static function vars($vars)
    {
        self::$vars = array_merge(self::$vars, $vars);
    }

    /**
     * Render a template file.
     * @param string $file
     * @return bool|string
     */
    public static function render($file)
    {
        return self::$engine->render($file . self::FILE_EXT, self::$vars);
    }
}
