<?php
/*
 * Main Class
 */
 
namespace Sakura;

class Main {

	public static $_TPL;
	public static $_MD;

	// Constructor
	public static function init($config) {

		// Stop the execution if the PHP Version is older than 5.4.0
		if(version_compare(phpversion(), '5.4.0', '<'))
			die('<h3>Upgrade your PHP Version to at least PHP 5.4!</h3>');

		// Configuration Management and local configuration
		Configuration::init($config);

		// Database
		Database::init();

        // "Dynamic" Configuration
        Configuration::initDB();

        // Create new session
        Session::init();

        // Templating engine
        self::initTwig();

        // Markdown Parser
        self::initParsedown();

	}

    // Initialise Twig
    private static function initTwig() {

        // Initialise Twig Filesystem Loader
        $twigLoader = new \Twig_Loader_Filesystem(Configuration::getLocalConfig('etc', 'templatesPath') .'/'. Configuration::getLocalConfig('etc', 'design'));

        // And now actually initialise the templating engine
        self::$_TPL = new \Twig_Environment($twigLoader, array(

           // 'cache' => SATOKO_ROOT_DIRECTORY. self::getConfig('path', 'cache') // Set cache directory

        ));

        // Load String template loader
        self::$_TPL->addExtension(new \Twig_Extension_StringLoader());

    }

    // Initialise Parsedown
    private static function initParsedown() {

        self::$_MD = new \Parsedown();

    }

    // Verify ReCAPTCHA
    public static function verifyCaptcha($response) {

        // Attempt to get the response
        $resp = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='. Configuration::getConfig('recaptcha_private') .'&response='. $response);

        // In the highly unlikely case that it failed to get anything forge a false
        if(!$resp)
            return array('success' => false, 'error-codes' => array('Could not connect to the ReCAPTCHA server.'));

        // Decode the response JSON from the servers
        $resp = json_decode($resp, true);

        // Return shit
        return $resp;

    }

	// Error Handler
	public static function ErrorHandler($errno, $errstr, $errfile, $errline) {

        // Set some variables to work with including A HUGE fallback hackjob for the templates folder
        $errstr     = str_replace(Configuration::getLocalConfig('etc', 'localPath'), '', $errstr);
        $errfile    = str_replace(Configuration::getLocalConfig('etc', 'localPath'), '', $errfile);
        $templates  = (Configuration::getLocalConfig('etc', 'templatesPath') !== null && !empty(Configuration::getLocalConfig('etc', 'templatesPath'))) ? Configuration::getLocalConfig('etc', 'templatesPath') : '/var/www/flashii.net/_sakuya/templates/';

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

    // Cleaning strings
    public static function cleanString($string, $lower = false) {

		$string = htmlentities($string, ENT_QUOTES | ENT_IGNORE, Configuration::getConfig('charset'));
		$string = stripslashes($string);
		$string = strip_tags($string);
        if($lower)
            $string = strtolower($string);

		return $string;

    }

    // Getting news posts
    public static function getNewsPosts($limit = null) {

        // Get news posts
        $newsPosts = Database::fetch('news', true, null, ['id', true], ($limit ? [$limit] : null));

        // Get user data
        foreach($newsPosts as $newsId => $newsPost) {
            $newsPosts[$newsId]['udata'] = Users::getUser($newsPost['uid']);
            $newsPosts[$newsId]['gdata'] = Users::getGroup($newsPost['udata']['group_main']);
        }

        // Return posts
        return $newsPosts;

    }

}
