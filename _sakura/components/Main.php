<?php
/*
 * Main Class
 */
 
namespace Flashii;

class Flashii {

	public $_TPL;
	public $_MD;
    
	// Constructor
	function __construct($config) {
        
		// Stop the execution if the PHP Version is older than 5.4.0
		if(version_compare(phpversion(), '5.4.0', '<'))
			die('<h3>Upgrade your PHP Version to at least PHP 5.4!</h3>');
	
        // Start session
        if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
    
		// Configuration Management and local configuration
		Configuration::init($config);
		
		// Database
		Database::init();
        
        // "Dynamic" Configuration
        Configuration::initDB();
        
        // Templating engine
        $this->initTwig();
        
        // Markdown Parser
        $this->initParsedown();
        
	}
    
	// Alias for Configuration::getConfig(), only exists because I'm lazy
	public static function getConfig($key) {
        
		return Configuration::getConfig($key);
        
	}
    
    // Initialise Twig
    private function initTwig($templateName = null, $templatesFolder = null) {
        
        // Assign default values set in the configuration if $templateName and $templatesFolder are null
        $templateName       = is_null($templateName)    ? Configuration::getLocalConfig('etc', 'design')          : $templateName;
        $templatesFolder    = is_null($templatesFolder) ? Configuration::getLocalConfig('etc', 'templatesPath')   : $templatesFolder;
        
        // Initialise Twig Filesystem Loader
        $twigLoader = new \Twig_Loader_Filesystem($templatesFolder . $templateName);

        // And now actually initialise the templating engine
        $this->_TPL = new \Twig_Environment($twigLoader, array(
           // 'cache' => ROOT_DIRECTORY. $satoko['cacheFolder']
        ));
        
        // Load String template loader
        $this->_TPL->addExtension(new \Twig_Extension_StringLoader());
        
    }
    
    // Initialise Parsedown
    private function initParsedown() {
        
        $this->_MD = new \Parsedown();
        
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