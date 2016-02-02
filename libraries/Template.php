<?php
namespace Sakura;

use Twig_Environment;
use Twig_Extension_StringLoader;
use Twig_Loader_Filesystem;

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
    private $vars = [];

    /**
     * The templating engine.
     * 
     * @var Twig_Environment
     */
    private $template;

    /**
     * The template name.
     * 
     * @var string
     */
    private $templateName;

    /**
     * The template options.
     * 
     * @var array
     */
    private $templateOptions;

    /**
     * The file extension used by template files
     * 
     * @var string
     */
    protected $templateFileExtension = ".twig";

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Set template to default
        $this->setTemplate(Config::get('site_style'));
    }

    /**
     * Set the template name.
     * 
     * @param string $name The name of the template directory.
     */
    public function setTemplate($name)
    {
        // Assign config path to a variable so we don't have to type it out twice
        $confPath = ROOT . 'templates/' . $name . '/template.ini';

        // Check if the configuration file exists
        if (!file_exists($confPath)) {
            trigger_error('Template configuration does not exist', E_USER_ERROR);
        }

        // Parse and store the configuration
        $this->templateOptions = parse_ini_file($confPath, true);

        // Set variables
        $this->templateName = $name;

        // Reinitialise
        $this->initTemplate();
    }

    /**
     * Initialise the templating engine.
     */
    public function initTemplate()
    {
        // Initialise Twig Filesystem Loader
        $twigLoader = new Twig_Loader_Filesystem(ROOT . 'templates/' . $this->templateName);

        // Environment variable
        $twigEnv = [];

        // Enable caching
        if (Config::get('enable_tpl_cache')) {
            $twigEnv['cache'] = ROOT . 'cache/twig';
        }

        // And now actually initialise the templating engine
        $this->template = new Twig_Environment($twigLoader, $twigEnv);

        // Load String template loader
        $this->template->addExtension(new Twig_Extension_StringLoader());
    }

    /**
     * Merge the parse variables.
     * 
     * @param array $vars The new variables.
     */
    public function setVariables($vars)
    {
        $this->vars = array_merge($this->vars, $vars);
    }

    /**
     * Render a template file.
     * 
     * @param string $file The filename/path
     * 
     * @return bool|string An error or the HTML.
     */
    public function render($file)
    {
        try {
            return $this->template->render($file . $this->templateFileExtension, $this->vars);
        } catch (\Exception $e) {
            return trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
