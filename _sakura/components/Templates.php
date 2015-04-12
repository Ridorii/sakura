<?php
/*
 * Template Engine Wrapper
 */

namespace Sakura;

class Templates {

    // Engine container, template folder name and options
    public static $_ENG;
    public static $_TPL;
    public static $_CFG;

    // Initialise templating engine and data
    public static function init($template) {

        // Set template folder name
        self::$_TPL = $template;

        // Assign config path to a variable so we don't have to type it out twice
        $confPath = Configuration::getLocalConfig('etc', 'templatesPath') .'/'. self::$_TPL .'/template.cfg';

        // Check if the configuration file exists
        if(!file_exists($confPath))
            trigger_error('Template configuration does not exist', E_USER_ERROR);

        // Parse and store the configuration
        self::$_CFG = self::parseCfg(file_get_contents($confPath));

        // Make sure we're not using a manage template for the main site or the other way around
        if((self::$_CFG['MANAGE'] && !Main::$_IN_MANAGE) || (!self::$_CFG['MANAGE'] && Main::$_IN_MANAGE))
            trigger_error('Incorrect template type', E_USER_ERROR);

        // Start Twig
        self::twigLoader();

    }

    // Twig Loader
    private static function twigLoader() {

        // Initialise Twig Filesystem Loader
        $twigLoader = new \Twig_Loader_Filesystem(Configuration::getLocalConfig('etc', 'templatesPath') .'/'. Configuration::getLocalConfig('etc', 'design'));

        // And now actually initialise the templating engine
        self::$_ENG = new \Twig_Environment($twigLoader, array(

           // 'cache' => SATOKO_ROOT_DIRECTORY. self::getConfig('path', 'cache') // Set cache directory

        ));

        // Load String template loader
        self::$_ENG->addExtension(new \Twig_Extension_StringLoader());

    }

    // Parse .cfg files
    public static function parseCfg($data) {

        // Create storage variable
        $out = array();

        // Remove comments and empty lines
        $data = preg_replace('/#.*?\r\n/im',    null, $data);
        $data = preg_replace('/^\r\n/im',       null, $data);

        // Break line breaks up into array values
        $data = str_replace("\r\n", "\n", $data);
        $data = explode("\n", $data);

        foreach($data as $var) {

            // Make sure no whitespaces escaped the check
            if(empty($var))
                continue;

            // Remove whitespace between key, equals sign and value
            $var = preg_replace('/[\s+]=[\s+]/i', '=', $var);

            // Then break this up
            $var = explode('=', $var);

            // And assign the value with the key to the output variable
            $out[$var[0]] = $var[1];

        }

        // Return the output variable
        return $out;

    }

    // Render template
    public static function render($file, $tags) {

        return self::$_ENG->render($file, $tags);

    }

}
