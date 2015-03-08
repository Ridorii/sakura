<?php
/*
 * Main Class
 */
 
namespace Flashii;

class Flashii {

	public static $_CONF;
	public static $_DB;
    public $twig;

	// Constructor
	function __construct($config) {
		// Stop the execution if the PHP Version is older than 5.4.0
		if(version_compare(phpversion(), '5.4.0', '<'))
			die('<h3>Upgrade your PHP Version to at least PHP 5.4!</h3>');
	
		// Assign $config values to $_CONF
		self::$_CONF = $config;
		
		// Initialise database
		self::$_DB = new Database();
	}
    
	// Get values from the configuration
	public static function getConfig($key, $subkey = null) {
		if(array_key_exists($key, self::$_CONF)) {
			if($subkey)
				return self::$_CONF[$key][$subkey];
			else
				return self::$_CONF[$key];
		} else {
			return false;
        }
	}
    
    // Initialise Twig
    public function initTwig($templateName = null, $templatesFolder = null) {
        // Assign default values set in the configuration if $templateName and $templatesFolder are null
        $templateName       = is_null($templateName)    ? self::getConfig('etc', 'design')          : $templateName;
        $templatesFolder    = is_null($templatesFolder) ? self::getConfig('etc', 'templatesPath')   : $templatesFolder;
        
        // Initialise Twig Filesystem Loader
        $twigLoader = new \Twig_Loader_Filesystem($templatesFolder . $templateName);

        // And now actually initialise the templating engine
        $this->twig = new \Twig_Environment($twigLoader, array(
           // 'cache' => $satoko['cacheFolder']
        ));
    }
	
	// Error Handler
	public static function ErrorHandler($errno, $errstr, $errfile, $errline) {
        // Set some variables to work with including A HUGE fallback hackjob for the templates folder
        $errstr     = str_replace(self::getConfig('etc', 'localPath'), '', $errstr);
        $errfile    = str_replace(self::getConfig('etc', 'localPath'), '', $errfile);
        $templates  = (self::getConfig('etc', 'templatesPath') !== null && !empty(self::getConfig('etc', 'templatesPath'))) ? self::getConfig('etc', 'templatesPath') : '/var/www/flashii.net/_sakuya/templates/';
        
		switch ($errno) {
			case E_ERROR:
			case E_USER_ERROR:
				$error = '<b>FATAL ERROR</b>: ' . $errstr . ' on line ' . $errline . ' in ' . $errfile;
			break;

			case E_WARNING:
			case E_USER_WARNING:
				$error = '<b>WARNING</b>: ' . $errstr . ' on line ' . $errline . ' in ' . $errfile;
			break;

			case E_NOTICE:
			case E_USER_NOTICE:
				$error = '<b>NOTICE</b>: ' . $errstr . ' on line ' . $errline . ' in ' . $errfile;
			break;

			default:
				$error = '<b>Unknown error type</b> [' . $errno . ']: ' . $errstr . ' on line ' . $errline . ' in ' . $errfile;
			break;
		}
        
        // Use file_get_contents instead of Twig in case the problem is related to twig
        $errorPage = file_get_contents($templates. 'errorPage.tpl');
        
        // str_replace {{ error }} on the error page with the error data
        $error = str_replace('{{ error }}', $error, $errorPage);
        
		// Truncate all previous outputs
		ob_clean();

		// Die and display error message
		die($error);
	}
    
    // Legacy password hashing to be able to validate passwords from users on the old backend.
    public static function legacyPasswordHash($data) {
        return hash('sha512', strrev(hash('sha512', $data)));
    }
    
}