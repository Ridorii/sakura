<?php
/*
 * Main Class
 */
 
namespace Sakura;

use Parsedown;
use PHPMailer;

class Main {

    public static $_MD; // Markdown class container
    public static $_MANAGE_MODE = false; // Management mode

    // Constructor
    public static function init($config) {

        // Stop the execution if the PHP Version is older than 5.4.0
        if(version_compare(phpversion(), '5.4.0', '<'))
            trigger_error('Sakura requires at least PHP 5.4.0, please upgrade to a newer PHP version.');

        // Configuration Management and local configuration
        Configuration::init($config);

        // Database
        Database::init(Configuration::getLocalConfig('database', 'driver'));

        // "Dynamic" Configuration
        Configuration::initDB();

        // Create new session
        Session::init();

        // Check if management mode was requested
        self::$_MANAGE_MODE = defined('SAKURA_MANAGE');

        // Templating engine
        if(!defined('SAKURA_NO_TPL')) {

            Templates::init(self::$_MANAGE_MODE ? Configuration::getConfig('manage_style') : Configuration::getConfig('site_style'));

        }

        // Assign servers file to whois class
        Whois::setServers(ROOT .'_sakura/'. Configuration::getLocalConfig('data', 'whoisservers'));

        // Markdown Parser
        self::initMD();

    }

    // Initialise Parsedown
    private static function initMD() {

        self::$_MD = new Parsedown();

    }

    // Parse markdown
    public static function mdParse($text) {

        return self::$_MD->text($text);

    }

    // Get bbcodes
    public static function getBBcodes() {

        return Database::fetch('bbcodes');

    }

    // Parse bbcodes
    public static function bbParse($text) {

        // Get bbcode regex from the database
        $bbcodes = Database::fetch('bbcodes');

        // Split the regex
        $regex = array_map(function($arr) {
            return $arr['regex'];
        }, $bbcodes);

        // Split the replacement
        $replace = array_map(function($arr) {
            return $arr['replace'];
        }, $bbcodes);

        // Do the replacement
        $text = preg_replace($regex, $replace, $text);

        // Return the parsed text
        return $text;

    }

    // Get emoticons
    public static function getEmotes() {

        return Database::fetch('emoticons');

    }

    // Parsing emoticons
    public static function parseEmotes($text) {

        // Get emoticons from the database
        $emotes = Database::fetch('emoticons');

        // Do the replacements
        foreach($emotes as $emote)
            $text = str_replace($emote['emote_string'], '<img src="//'. Configuration::getLocalConfig('urls', 'content') .'/'. $emote['emote_path'] .'" class="emoticon" alt="'. $emote['emote_string'] .'" />', $text);

        // Return the parsed text
        return $text;

    }

    // Verify ReCAPTCHA
    public static function verifyCaptcha($response) {

        // Attempt to get the response
        $resp = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='. Configuration::getConfig('recaptcha_private') .'&response='. $response);

        // In the highly unlikely case that it failed to get anything forge a false
        if(!$resp)
            return false;

        // Decode the response JSON from the servers
        $resp = json_decode($resp, true);

        // Return shit
        return $resp;

    }

    // Error Handler
    public static function errorHandler($errno, $errstr, $errfile, $errline) {

        // Set some variables to work with including A HUGE fallback hackjob for the templates folder
        $errstr     = str_replace(ROOT, '', $errstr);
        $errfile    = str_replace(ROOT, '', $errfile);
        $templates  = ROOT .'_sakura/templates/';

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
        ob_end_clean();

        // Die and display error message
        die($error);

    }

    // Send emails
    public static function sendMail($to, $subject, $body) {

        // Initialise PHPMailer
        $mail = new PHPMailer();

        // Set to SMTP
        $mail->IsSMTP();

        // Set the SMTP server host
        $mail->Host = Configuration::getConfig('smtp_server');

        // Do we require authentication?
        $mail->SMTPAuth = Configuration::getConfig('smtp_auth');

        // Do we encrypt as well?
        $mail->SMTPSecure = Configuration::getConfig('smtp_secure');

        // Set the port to the SMTP server
        $mail->Port = Configuration::getConfig('smtp_port');

        // If authentication is required log in as well
        if(Configuration::getConfig('smtp_auth')) {

            $mail->Username = Configuration::getConfig('smtp_username');
            $mail->Password = base64_decode(Configuration::getConfig('smtp_password'));

        }

        // Add a reply-to header
        $mail->AddReplyTo(Configuration::getConfig('smtp_replyto_mail'), Configuration::getConfig('smtp_replyto_name'));

        // Set a from address as well
        $mail->SetFrom(Configuration::getConfig('smtp_from_email'), Configuration::getConfig('smtp_from_name'));

        // Set the addressee
        foreach($to as $email => $name)
            $mail->AddBCC($email, $name);

        // Subject line
        $mail->Subject = $subject;

        // Set the mail type to HTML
        $mail->isHTML(true);

        // Set email contents
        $htmlMail = file_get_contents(ROOT .'_sakura/templates/htmlEmail.tpl');

        // Replace template tags
        $htmlMail = str_replace('{{ sitename }}',   Configuration::getConfig('sitename'),                   $htmlMail);
        $htmlMail = str_replace('{{ siteurl }}',    '//'. Configuration::getLocalConfig('urls', 'main'),    $htmlMail);
        $htmlMail = str_replace('{{ contents }}',   self::mdParse($body),                                   $htmlMail);

        // Set HTML body
        $mail->Body = $htmlMail;

        // Set fallback body
        $mail->AltBody = $body;

        // Send the message
        $send = $mail->Send();

        // Clear the addressee list
        $mail->ClearAddresses();

        // If we got an error return the error
        if(!$send)
            return $mail->ErrorInfo;

        // Else just return whatever
        return $send;

    }

    // Legacy password hashing to be able to validate passwords from users on the old backend.
    public static function legacyPasswordHash($data) {

        return hash('sha512', strrev(hash('sha512', $data)));

    }

    // Cleaning strings
    public static function cleanString($string, $lower = false, $nospecial = false) {

        // Run common sanitisation function over string
        $string = htmlentities($string, ENT_NOQUOTES | ENT_HTML401, Configuration::getConfig('charset'));
        $string = stripslashes($string);
        $string = strip_tags($string);

        // If set also make the string lowercase
        if($lower)
            $string = strtolower($string);

        // If set remove all characters that aren't a-z or 0-9
        if($nospecial)
            $string = preg_replace('/[^a-z0-9]/', '', $string);

        // Return clean string
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

            // Check if a custom name colour is set and if so overwrite the rank colour
            if($newsPosts[$newsId]['udata']['name_colour'] != null)
                $newsPosts[$newsId]['rdata']['colour'] = $newsPosts[$newsId]['udata']['name_colour'];

        }

        // Return posts
        return $newsPosts;

    }

    // Generate disqus hmac (https://github.com/disqus/DISQUS-API-Recipes/blob/master/sso/php/sso.php)
    public static function dsqHmacSha1($data, $key) {

        $blocksize = 64;

        if(strlen($key) > $blocksize) {

            $key = pack('H*', sha1($key));

        }

        $key    = str_pad($key, $blocksize, chr(0x00));
        $ipad   = str_repeat(chr(0x36), $blocksize);
        $opad   = str_repeat(chr(0x5c), $blocksize);
        $hmac   = pack(
            'H*', sha1(
                ($key ^ $opad) . pack(
                    'H*', sha1(
                        ($key ^ $ipad) . $data
                    )
                )
            )
        );

        return bin2hex($hmac);

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

        // Get the domain from the e-mail address
        $domain = substr(strstr($email, '@'), 1);

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
        $cfhosts = file_get_contents(ROOT .'_sakura/'. Configuration::getLocalConfig('data', 'cfipv'. (self::ipVersion($ip))));

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

    // Get country code from CloudFlare header (which just returns EU if not found)
    public static function getCountryCode() {

        // Check if the required header is set and return it
        if(isset($_SERVER['HTTP_CF_IPCOUNTRY']))
            return $_SERVER['HTTP_CF_IPCOUNTRY'];

        // Return EU as a fallback
        return 'EU';

    }

    // Create a new action code
    public static function newActionCode($action, $userid, $instruct) {

        // Make sure the user we're working with exists
        if(Users::getUser($userid)['id'] == 0)
            return false;

        // Convert the instruction array to a JSON
        $instruct = json_encode($instruct);

        // Generate a key
        $key = sha1(date("r") . time() . $userid . $action . rand(0, 9999));

        // Insert the key into the database
        Database::insert('actioncodes', [
            'action'        => $action,
            'userid'        => $userid,
            'actkey'        => $key,
            'instruction'   => $instruct
        ]);

        // Return the key
        return $key;

    }

    // Use an action code
    public static function useActionCode($action, $key, $uid = 0) {

        // Retrieve the row from the database
        $keyRow = Database::fetch('actioncodes', false, [
            'actkey' => [$key,      '='],
            'action' => [$action,   '=']
        ]);

        // Check if the code exists
        if(count($keyRow) <= 1)
            return [0, 'INVALID_CODE'];

        // Check if the code was intended for the user that's using this code
        if($keyRow['userid'] != 0) {

            if($keyRow['userid'] != $uid)
                return [0, 'INVALID_USER'];

        }

        // Remove the key from the database
        Database::delete('actioncodes', [
            'id' => [$keyRow['id'], '=']
        ]);

        // Return success
        return [1, 'SUCCESS', $keyRow['instruction']];

    }

    // Calculate password entropy
    public static function pwdEntropy($pw) {

        // Decode utf-8 chars
        $pw = utf8_decode($pw);

        // Count the amount of unique characters in the password string and calculate the entropy
        return count(count_chars($pw, 1)) * log(256, 2);

    }

    // Get country name from ISO 3166 code
    public static function getCountryName($code) {

        // Parse JSON file
        $iso3166 = json_decode(utf8_encode(file_get_contents(ROOT .'_sakura/'. Configuration::getLocalConfig('data', 'iso3166'))), true);

        // Check if key exists
        if(array_key_exists($code, $iso3166))
            return $iso3166[$code]; // If entry found return the full name
        else
            return 'Unknown'; // Else return unknown

    }

    // Get FAQ data
    public static function getFaqData() {

        // Do database call
        $faq = Database::fetch('faq', true, null, ['id']);

        // Return FAQ data
        return $faq;

    }

    // Get log type string
    public static function getLogStringFromType($type) {

        // Query the database
        $return = Database::fetch('logtypes', false, ['id' => [$type, '=']]);

        // Check if type exists and else return a unformattable string
        if(count($return) < 2)
            return 'Unknown action.';

        // Return the string
        return $return['string'];

    }

    // Get formatted logs
    public static function getUserLogs($uid = 0) {

        // Check if a user is specified
        $conditions = ($uid ? ['uid' => [$uid, '=']] : null);

        // Get data from database
        $logsDB = Database::fetch('logs', true, $conditions, ['id', true]);

        // Storage array
        $logs = array();

        // Iterate over entries
        foreach($logsDB as $log) {

            // Store usable data
            $logs[$log['id']] = [
                'user'      => $_USER = Users::getUser($log['uid']),
                'rank'      => Users::getRank($_USER['rank_main']),
                'string'    => vsprintf(self::getLogStringFromType($log['action']), json_decode($log['attribs'], true))
            ];

        }

        // Return new logs
        return $logs;

    }

    // Indent JSON
    public static function jsonPretty($json) {

        // Defines
        $tab = '    ';
        $out = '';
        $lvl = 0;
        $str = false;
        $obj = json_decode($json);

        // Validate the object
        if($obj === false)
            return false;

        // Re-encode the json and get the length
        $json = json_encode($obj);
        $len = strlen($json);

        // Go over the entries
        for($c = 0; $c < $len; $c++) {

            // Get the current character
            $char = $json[$c];

            switch($char) {

                case '[':
                case '{':
                    if($str) {

                        $out .= $char;

                    } else {

                        $out .= $char ."\r\n". str_repeat($tab, $lvl + 1);
                        $lvl++;

                    }
                    break;

                case ']':
                case '}':
                    if($str) {

                        $out .= $char;

                    } else {

                        $lvl--;
                        $out .= "\r\n". str_repeat($tab, $lvl) . $char;

                    }
                    break;

                case ',':
                    if($str) {

                        $out .= $char;

                    } else {

                        $out .= ",\r\n". str_repeat($tab, $lvl);

                    }
                    break;

                case ':':
                    if($str) {

                        $out .= $char;

                    } else {

                        $out .= ": ";

                    }
                    break;

                default:
                    $out .= $char;
                    break;

            }

        }

        // Return the indented JSON
        return $out;

    }

    // Time elapsed
    public static function timeElapsed($timestamp) {

        // Subtract the entered timestamp from the current timestamp
        $time = time() - $timestamp;

        // If the new timestamp is below 1 return a standard string
        if($time < 1)
            return 'Just now';

        // Array containing time "types"
        $times = [
            365 * 24 * 60 * 60 => 'year',
             30 * 24 * 60 * 60 => 'month',
                  24 * 60 * 60 => 'day',
                       60 * 60 => 'hour',
                            60 => 'minute',
                             1 => 'second'
        ];

        foreach($times as $secs => $str) {

            // Do a devision to check if the given timestamp fits in the current "type"
            $calc = $time / $secs;

            if($calc >= 1) {

                // Round the number
                $round = round($calc);

                // Return the string
                return $round .' '. $times[$secs] . ($round == 1 ? '' : 's') .' ago';

            } 

        }

    }

    // Get the byte symbol from a value
    public static function getByteSymbol($bytes) {

        // Return nothing if the input was 0
        if(!$bytes)
            return;

        // Array with byte symbols
        $symbols = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];

        // Calculate byte entity
        $exp = floor(log($bytes) / log(1024));

        // Format the things
        $bytes = sprintf("%.2f ". $symbols[$exp], ($bytes / pow(1024, floor($exp))));

        // Return the formatted string
        return $bytes;

    }

}
