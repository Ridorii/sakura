<?php
/*
 * Template engine wrapper
 */

namespace Sakura;

use Twig_Environment;
use Twig_Extension_StringLoader;
use Twig_Loader_Filesystem;

/**
 * Class Template
 * @package Sakura
 */
class Template
{
    // Engine container, template folder name, options and template variables
    private $vars = [];
    private $template;
    private $templateName;
    private $templateOptions;

    // Initialise templating engine and data
    public function __construct()
    {
        // Set template to default
        $this->setTemplate(Config::get('site_style'));
    }

    // Set a template name
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

    // Initialise main template engine
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

    // Set variables
    public function setVariables($vars)
    {
        $this->vars = array_merge($this->vars, $vars);
    }

    // Render a template
    public function render($file)
    {
        try {
            return $this->template->render($file, $this->vars);
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
