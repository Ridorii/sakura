<?php
/*
 * Main Class
 */
 
namespace Sakura;

class Main {

	public static $_TPL;
	public static $_MD;
    public static $_IN_MANAGE = false;

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
        Templates::init(Configuration::getLocalConfig('etc', 'design'));

        // Markdown Parser
        self::initMD();

	}

    // Initialise Parsedown
    private static function initMD() {

        self::$_MD = new \Parsedown();

    }

    // Parse markdown
    public static function mdParse($text) {

        return self::$_MD->text($text);

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
    public static function getNewsPosts($limit = null, $pid = false) {

        // Get news posts
        $newsPosts = Database::fetch('news', true, ($pid ? ['id' => [$limit, '=']] : null), ['id', true], ($limit && !$pid ? [$limit] : null));

        // Get user data
        foreach($newsPosts as $newsId => $newsPost) {
            $newsPosts[$newsId]['parsed']   = self::mdParse($newsPost['content']);
            $newsPosts[$newsId]['udata']    = Users::getUser($newsPost['uid']);
            $newsPosts[$newsId]['rdata']    = Users::getRank($newsPosts[$newsId]['udata']['rank_main']);
        }

        // Return posts
        return $newsPosts;

    }

    // Loading info pages
    public static function loadInfoPage($id) {

        // Get contents from the database
        $infopage = Database::fetch('infopages', false, ['shorthand' => [$id, '=']]);

        // Return the data if there is any else just return false
        return count($infopage) ? $infopage : false;

    }

    // Validate MX records
    public static function checkMXRecord($email) {

        // Split up the address in two parts (user and domain)
        list($user, $domain) = split('@', $email);

        // Check the MX record
        $record = checkdnsrr($domain, 'MX');

        // Return the record data
        return $record;

    }

    // Check IP version
    public static function ipVersion($ip) {

        // Check if var is IP
        if(filter_var($ip, FILTER_VALIDATE_IP)) {

            // IPv4
            if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
                return 4;

            // IPv6
            if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
                return 6;

        }

        // Not an IP or unknown type
        return 0;

    }

    // Convert inet_pton to string with bits
    public static function inetToBits($inet) {

        // Unpack string
        $unpacked = unpack('A16', $inet);

        // Split the string
        $unpacked = str_split($unpacked[1]);

        // Define variable
        $binaryIP = null;

        // "Build" binary IP
        foreach($unpacked as $char)
            $binaryIP .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);

        // Return IP
        return $binaryIP;

    }

    // Match IP subnets
    public static function matchSubnet($ip, $range) {

        // Use the proper IP type
        switch(self::ipVersion($ip)) {

            case 4:

                // Break the range up in parts
                list($subnet, $bits) = explode('/', $range);

                // Convert IP and Subnet to long
                $ip     = ip2long($ip);
                $subnet = ip2long($subnet);
                $mask   = -1 << (32 - $bits);

                // In case the supplied subnet wasn't correctly aligned
                $subnet &= $mask;

                // Return true if IP is in subnet
                return ($ip & $mask) == $subnet;

            case 6:

                // Break the range up in parts
                list($subnet, $bits) = explode('/', $range);

                // Convert subnet to packed address and convert it to binary
                $subnet         = inet_pton($subnet);
                $binarySubnet   = self::inetToBits($subnet);

                // Convert IPv6 to packed address and convert it to binary as well
                $ip         = inet_pton($ip);
                $binaryIP   = self::inetToBits($ip);

                // Return bits of the strings according to the bits
                $ipBits     = substr($binaryIP,     0, $bits);
                $subnetBits = substr($binarySubnet, 0, $bits);

                return ($ipBits === $subnetBits);

            default:
                return 0;

        }

    }

    // Check if IP is a CloudFlare IP
    public static function checkCFIP($ip) {

        // Get CloudFlare Subnet list
        $cfhosts = file_get_contents(Configuration::getLocalConfig('etc', 'cfhosts'));

        // Replace \r\n with \n
        $cfhosts = str_replace("\r\n", "\n", $cfhosts);

        // Explode the file into an array
        $cfhosts = explode("\n", $cfhosts);

        // Check if IP is in a CloudFlare subnet
        foreach($cfhosts as $subnet) {

            // Return true if found
            if(self::matchSubnet($ip, $subnet))
                return true;

        }

        // Return false if fails
        return false;

    }

    // Gets IP of current visitor
    public static function getRemoteIP() {

        // Assign REMOTE_ADDR to a variables
        $ip = $_SERVER['REMOTE_ADDR'];

        // Check if the IP is a CloudFlare IP
        if(self::checkCFIP($ip)) {

            // If it is check if the CloudFlare IP header is set and if it is assign it to the ip variable
            if(isset($_SERVER['HTTP_CF_CONNECTING_IP']))
                $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];

        }

        // Return the correct IP
        return $ip;

    }

}
