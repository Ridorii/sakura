<?php
/*
 * Main Class
 */

namespace Sakura;

use Parsedown;
use PHPMailer;

/**
 * Class Main
 * @package Sakura
 */
class Main
{
    // Parse markdown
    public static function mdParse($text, $escape = false)
    {
        $pd = new Parsedown();

        return $escape ?
        $pd->setMarkupEscaped(true)->text($text) :
        $pd->text($text);
    }

    // Get emoticons
    public static function getEmotes()
    {
        return Database::fetch('emoticons');
    }

    // Parsing emoticons
    public static function parseEmotes($text)
    {

        // Get emoticons from the database
        $emotes = self::getEmotes();

        // Do the replacements
        foreach ($emotes as $emote) {
            $text = str_replace(
                $emote['emote_string'],
                '<img src="' . $emote['emote_path'] . '" class="emoticon" alt="' . $emote['emote_string'] . '" />',
                $text
            );
        }

        // Return the parsed text
        return $text;
    }

    // Verify ReCAPTCHA
    public static function verifyCaptcha($response)
    {

        // Attempt to get the response
        $resp = @file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify?secret='
            . Config::get('recaptcha_private')
            . '&response='
            . $response
        );

        // In the highly unlikely case that it failed to get anything forge a false
        if (!$resp) {
            return false;
        }

        // Decode the response JSON from the servers
        $resp = json_decode($resp, true);

        // Return shit
        return $resp;
    }

    // Error Handler
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {

        // Remove ROOT path from the error string and file location
        $errstr = str_replace(ROOT, '', $errstr);
        $errfile = str_replace(ROOT, '', $errfile);

        // Attempt to log the error to the database
        if (Database::$database !== null) {
            // Encode backtrace data
            $backtrace = base64_encode(json_encode(debug_backtrace()));

            // Check if this error has already been logged in the past
            if ($past = Database::fetch(
                'error_log',
                false,
                [
                    'error_backtrace' => [$backtrace, '=', true],
                    'error_string' => [$errstr, '='],
                    'error_line' => [$errline, '='],
                ]
            )) {
                // If so assign the errid
                $errid = $past['error_id'];
            } else {
                // Create an error ID
                $errid = substr(md5(microtime()), rand(0, 22), 10);

                // Log the error
                Database::insert('error_log', [
                    'error_id' => $errid,
                    'error_timestamp' => date("r"),
                    'error_revision' => SAKURA_VERSION,
                    'error_type' => $errno,
                    'error_line' => $errline,
                    'error_string' => $errstr,
                    'error_file' => $errfile,
                    'error_backtrace' => $backtrace,
                ]);
            }
        }

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
                $error = '<b>Unknown error type</b> [' . $errno . ']: ' . $errstr . ' on line ' . $errline
                . ' in ' . $errfile;
        }

        // Truncate all previous outputs
        ob_clean();
        ob_end_clean();

        // Check if this request was made through the ajax thing
        if (isset($_REQUEST['ajax'])) {
            die('An error occurred while executing the script.|1|javascript:alert("' . (isset($errid) ? 'Error Log ID: '. $errid : 'Failed to log.') . '");');
        }

        // Check for dev mode
        $detailed = Config::local('dev', 'show_errors');

        // Build page
        $errorPage = '<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Sakura Internal Error</title>
        <style type="text/css">
            body { margin: 0; padding: 0; background: #EEE; color: #000;
                font: 12px/20px Verdana, Arial, Helvetica, sans-serif; }
            h1, h2 { font-weight: 100; background: #CAA; padding: 8px 5px 10px;
                margin: 0; font-style: italic; font-family: serif; }
            h1 { border-radius: 8px 8px 0 0; }
            h2 { margin: 0 -10px; }
            .container { border: 1px solid #CAA; margin: 10px auto; background: #FFF;
                box-shadow: 2px 2px 1em #888; max-width: 1024px; border-radius: 10px; }
            .container .inner { padding: 0 10px; }
            .container .inner .error { background: #555; color: #EEE; border-left: 5px solid #C22;
                padding: 4px 6px; text-shadow: 0 1px 1px #888; white-space: pre-wrap;
                word-wrap: break-word; margin: 12px 0; border-radius: 5px; box-shadow: inset 0 0 1em #333; }
            .container .footer { border-top: 1px solid #CAA; font-size: x-small; padding: 0 5px 1px; }
            a { color: #77E; text-decoration: none; }
            a:hover { text-decoration: underline; }
            a:active { color: #E77; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>An error occurred while executing the script.</h1>
            <div class="inner">
                <p>To prevent potential security risks or data loss Sakura has stopped execution of the script.</p>';

        if (isset($errid)) {
            $errorPage .= '<p>The error and surrounding data has been logged.</p>
    <h2>' . (!$detailed ? 'Report the following text to a staff member' : 'Logged as') . '</h2>
    <pre class="error">' . $errid . '</pre>';
        } else {
            $errorPage .= '<p>Sakura was not able to log this error which could mean that there was an error
             with the database connection. If you\'re the system administrator check the database credentials
              and make sure the server is running and if you\'re not please let the system administrator
               know about this error if it occurs again.</p>';
        }

        if ($detailed) {
            $errorPage .= '                <h2>Summary</h2>
                <pre class="error">' . $error . '</pre>
                <h2>Backtraces</h2>';

            foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $num => $trace) {
                $errorPage .= '<h3>#' . $num . '</h3><pre class="error">';

                foreach ($trace as $key => $val) {
                    $errorPage .=
                    str_pad(
                        '[' . $key . ']',
                        12
                    ) . '=> ' . (
                        is_array($val) || is_object($val) ?
                        json_encode($val) :
                        $val
                    ) . "\r\n";
                }

                $errorPage .= '</pre>';
            }
        }

        $errorPage .= '</div>
            <div class="footer">
                Sakura r' . SAKURA_VERSION . '.
            </div>
        </div>
    </body>
</html>';

        // Die and display error message
        die($errorPage);
    }

    // Send emails
    public static function sendMail($to, $subject, $body)
    {

        // Initialise PHPMailer
        $mail = new PHPMailer();

        // Set to SMTP
        $mail->isSMTP();

        // Set the SMTP server host
        $mail->Host = Config::get('smtp_server');

        // Do we require authentication?
        $mail->SMTPAuth = Config::get('smtp_auth');

        // Do we encrypt as well?
        $mail->SMTPSecure = Config::get('smtp_secure');

        // Set the port to the SMTP server
        $mail->Port = Config::get('smtp_port');

        // If authentication is required log in as well
        if (Config::get('smtp_auth')) {
            $mail->Username = Config::get('smtp_username');
            $mail->Password = base64_decode(Config::get('smtp_password'));
        }

        // Add a reply-to header
        $mail->addReplyTo(Config::get('smtp_replyto_mail'), Config::get('smtp_replyto_name'));

        // Set a from address as well
        $mail->setFrom(Config::get('smtp_from_email'), Config::get('smtp_from_name'));

        // Set the addressee
        foreach ($to as $email => $name) {
            $mail->addBCC($email, $name);
        }

        // Subject line
        $mail->Subject = $subject;

        // Set the mail type to HTML
        $mail->isHTML(true);

        // Set email contents
        $htmlMail = file_get_contents(ROOT . 'templates/htmlEmail.html');

        // Replace template tags
        $htmlMail = str_replace('{{ sitename }}', Config::get('sitename'), $htmlMail);
        $htmlMail = str_replace('{{ siteurl }}', '//' . Config::get('url_main'), $htmlMail);
        $htmlMail = str_replace('{{ contents }}', self::mdParse($body), $htmlMail);

        // Set HTML body
        $mail->Body = $htmlMail;

        // Set fallback body
        $mail->AltBody = $body;

        // Send the message
        $send = $mail->send();

        // Clear the addressee list
        $mail->clearAddresses();

        // If we got an error return the error
        if (!$send) {
            return $mail->ErrorInfo;
        }

        // Else just return whatever
        return $send;
    }

    // Cleaning strings
    public static function cleanString($string, $lower = false, $noSpecial = false, $replaceSpecial = '')
    {

        // Run common sanitisation function over string
        $string = htmlentities($string, ENT_NOQUOTES | ENT_HTML401, Config::get('charset'));
        $string = stripslashes($string);
        $string = strip_tags($string);

        // If set also make the string lowercase
        if ($lower) {
            $string = strtolower($string);
        }

        // If set remove all characters that aren't a-z or 0-9
        if ($noSpecial) {
            $string = preg_replace('/[^a-z0-9]/', $replaceSpecial, $string);
        }

        // Return clean string
        return $string;
    }

    // Loading info pages
    public static function loadInfoPage($id)
    {

        // Get contents from the database
        $infopage = Database::fetch('infopages', false, ['page_shorthand' => [$id, '=']]);

        // Return the data if there is any else just return false
        return count($infopage) ? $infopage : false;
    }

    // Validate MX records
    public static function checkMXRecord($email)
    {

        // Get the domain from the e-mail address
        $domain = substr(strstr($email, '@'), 1);

        // Check the MX record
        $record = checkdnsrr($domain, 'MX');

        // Return the record data
        return $record;
    }

    // Check IP version
    public static function ipVersion($ip)
    {

        // Check if var is IP
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            // IPv4
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return 4;
            }

            // IPv6
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return 6;
            }
        }

        // Not an IP or unknown type
        return 0;
    }

    // Convert inet_pton to string with bits
    public static function inetToBits($inet)
    {

        // Unpack string
        $unpacked = unpack('A16', $inet);

        // Split the string
        $unpacked = str_split($unpacked[1]);

        // Define variable
        $binaryIP = null;

        // "Build" binary IP
        foreach ($unpacked as $char) {
            $binaryIP .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        // Return IP
        return $binaryIP;
    }

    // Match IP subnets
    public static function matchSubnet($ip, $range)
    {

        // Use the proper IP type
        switch (self::ipVersion($ip)) {
            case 4:
                // Break the range up in parts
                list($subnet, $bits) = explode('/', $range);

                // Convert IP and Subnet to long
                $ip = ip2long($ip);
                $subnet = ip2long($subnet);
                $mask = -1 << (32 - $bits);

                // In case the supplied subnet wasn't correctly aligned
                $subnet &= $mask;

                // Return true if IP is in subnet
                return ($ip & $mask) == $subnet;

            case 6:
                // Break the range up in parts
                list($subnet, $bits) = explode('/', $range);

                // Convert subnet to packed address and convert it to binary
                $subnet = inet_pton($subnet);
                $binarySubnet = self::inetToBits($subnet);

                // Convert IPv6 to packed address and convert it to binary as well
                $ip = inet_pton($ip);
                $binaryIP = self::inetToBits($ip);

                // Return bits of the strings according to the bits
                $ipBits = substr($binaryIP, 0, $bits);
                $subnetBits = substr($binarySubnet, 0, $bits);

                return ($ipBits === $subnetBits);

            default:
                return 0;

        }
    }

    // Check if IP is a CloudFlare IP
    public static function checkCFIP($ip)
    {

        // Get CloudFlare Subnet list
        $cfhosts = file_get_contents(
            ROOT . Config::local('data', 'cfipv' . (self::ipVersion($ip)))
        );

        // Replace \r\n with \n
        $cfhosts = str_replace("\r\n", "\n", $cfhosts);

        // Explode the file into an array
        $cfhosts = explode("\n", $cfhosts);

        // Check if IP is in a CloudFlare subnet
        foreach ($cfhosts as $subnet) {
            // Check if the subnet isn't empty (git newline prevention)
            if (strlen($subnet) < 1) {
                continue;
            }

            // Return true if found
            if (self::matchSubnet($ip, $subnet)) {
                return true;
            }
        }

        // Return false if fails
        return false;
    }

    // Gets IP of current visitor
    public static function getRemoteIP()
    {

        // Assign REMOTE_ADDR to a variables
        $ip = $_SERVER['REMOTE_ADDR'];

        // Check if the IP is a CloudFlare IP
        if (self::checkCFIP($ip)) {
            // If it is check if the CloudFlare IP header is set and if it is assign it to the ip variable
            if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
            }
        }

        // Return the correct IP
        return $ip;
    }

    // Get country code from CloudFlare header (which just returns XX if not found)
    public static function getCountryCode()
    {
        // Attempt to get country code using PHP's built in geo thing
        if (function_exists("geoip_country_code_by_name")) {
            $code = geoip_country_code_by_name(self::getRemoteIP());
            // Check if $code is anything
            if ($code) {
                return $code;
            }
        }

        // Check if the required header is set and return it
        if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            return $_SERVER['HTTP_CF_IPCOUNTRY'];
        }

        // Return XX as a fallback
        return 'XX';
    }

    // Create a new action code
    public static function newActionCode($action, $userid, $instruct)
    {

        // Make sure the user we're working with exists
        if (Users::getUser($userid)['id'] == 0) {
            return false;
        }

        // Convert the instruction array to a JSON
        $instruct = json_encode($instruct);

        // Generate a key
        $key = sha1(date("r") . time() . $userid . $action . rand(0, 9999));

        // Insert the key into the database
        Database::insert('actioncodes', [
            'action' => $action,
            'userid' => $userid,
            'actkey' => $key,
            'instruction' => $instruct,
        ]);

        // Return the key
        return $key;
    }

    // Use an action code
    public static function useActionCode($action, $key, $uid = 0)
    {

        // Retrieve the row from the database
        $keyRow = Database::fetch('actioncodes', false, [
            'actkey' => [$key, '='],
            'action' => [$action, '='],
        ]);

        // Check if the code exists
        if (count($keyRow) <= 1) {
            return [0, 'INVALID_CODE'];
        }

        // Check if the code was intended for the user that's using this code
        if ($keyRow['userid'] != 0) {
            if ($keyRow['userid'] != $uid) {
                return [0, 'INVALID_USER'];
            }
        }

        // Remove the key from the database
        Database::delete('actioncodes', [
            'id' => [$keyRow['id'], '='],
        ]);

        // Return success
        return [1, 'SUCCESS', $keyRow['instruction']];
    }

    // Calculate password entropy
    public static function pwdEntropy($pw)
    {

        // Decode utf-8 chars
        $pw = utf8_decode($pw);

        // Count the amount of unique characters in the password string and calculate the entropy
        return count(count_chars($pw, 1)) * log(256, 2);
    }

    // Get country name from ISO 3166 code
    public static function getCountryName($code)
    {

        // Parse JSON file
        $iso3166 = json_decode(
            utf8_encode(
                file_get_contents(
                    ROOT . Config::local('data', 'iso3166')
                )
            ),
            true
        );

        // Check if key exists
        if (array_key_exists($code, $iso3166)) {
            return $iso3166[$code]; // If entry found return the full name
        }

        // Else return unknown
        return 'Unknown';
    }

    // Get FAQ data
    public static function getFaqData()
    {

        // Do database call
        $faq = Database::fetch('faq', true, null, ['faq_id']);

        // Return FAQ data
        return $faq;
    }

    // Get log type string
    public static function getLogStringFromType($type)
    {

        // Query the database
        $return = Database::fetch('logtypes', false, ['id' => [$type, '=']]);

        // Check if type exists and else return a unformattable string
        if (count($return) < 2) {
            return 'Unknown action.';
        }

        // Return the string
        return $return['string'];
    }

    // Get formatted logs
    public static function getUserLogs($uid = 0)
    {

        // Check if a user is specified
        $conditions = ($uid ? ['uid' => [$uid, '=']] : null);

        // Get data from database
        $logsDB = Database::fetch('logs', true, $conditions, ['id', true]);

        // Storage array
        $logs = [];

        // Iterate over entries
        foreach ($logsDB as $log) {
            // Store usable data
            $logs[$log['id']] = [
                'user' => $_USER = Users::getUser($log['uid']),
                'rank' => Users::getRank($_USER['rank_main']),
                'string' => vsprintf(self::getLogStringFromType($log['action']), json_decode($log['attribs'], true)),
            ];
        }

        // Return new logs
        return $logs;
    }

    // Time elapsed
    public static function timeElapsed($timestamp, $append = ' ago', $none = 'Just now')
    {

        // Subtract the entered timestamp from the current timestamp
        $time = time() - $timestamp;

        // If the new timestamp is below 1 return a standard string
        if ($time < 1) {
            return $none;
        }

        // Array containing time "types"
        $times = [
            365 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60 => 'month',
            24 * 60 * 60 => 'day',
            60 * 60 => 'hour',
            60 => 'minute',
            1 => 'second',
        ];

        foreach ($times as $secs => $str) {
            // Do a devision to check if the given timestamp fits in the current "type"
            $calc = $time / $secs;

            if ($calc >= 1) {
                // Round the number
                $round = floor($calc);

                // Return the string
                return $round . ' ' . $times[$secs] . ($round == 1 ? '' : 's') . $append;
            }
        }
    }

    // Get the byte symbol from a value
    public static function getByteSymbol($bytes)
    {

        // Return nothing if the input was 0
        if (!$bytes) {
            return;
        }

        // Array with byte symbols
        $symbols = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];

        // Calculate byte entity
        $exp = floor(log($bytes) / log(1024));

        // Format the things
        $bytes = sprintf("%.2f " . $symbols[$exp], ($bytes / pow(1024, floor($exp))));

        // Return the formatted string
        return $bytes;
    }

    // Get Premium tracker data
    public static function getPremiumTrackerData()
    {

        // Create data array
        $data = [];

        // Get database stuff
        $table = Database::fetch('premium_log', true, null, ['transaction_id', true]);

        // Add raw table data to data array
        $data['table'] = $table;

        // Create balance entry
        $data['balance'] = 0.0;

        // Create users entry
        $data['users'] = [];

        // Calculate the thing
        foreach ($table as $row) {
            // Calculate balance
            $data['balance'] = $data['balance'] + $row['transaction_amount'];

            // Add userdata to table
            if (!array_key_exists($row['user_id'], $data['users'])) {
                $data['users'][$row['user_id']] = User::construct($row['user_id']);
            }
        }

        // Return the data
        return $data;
    }

    // Update donation tracker
    public static function updatePremiumTracker($id, $amount, $comment)
    {
        Database::insert('premium_log', [

            'user_id' => $id,
            'transaction_amount' => $amount,
            'transaction_date' => time(),
            'transaction_comment' => $comment,

        ]);
    }

    // Cleaning up the contents of code tags
    public static function fixCodeTags($text)
    {
        $parts = explode('<code>', $text);
        $newStr = '';

        if (count($parts) > 1) {
            foreach ($parts as $p) {
                $parts2 = explode('</code>', $p);
                if (count($parts2) > 1) {
                    $code = str_replace('<br />', '', $parts2[0]);
                    $code = str_replace('<br/>', '', $code);
                    $code = str_replace('<br>', '', $code);
                    $code = str_replace('<', '&lt;', $code);
                    $newStr .= '<code>'.$code.'</code>';
                    $newStr .= $parts2[1];
                } else {
                    $newStr .= $p;
                }
            }
        } else {
            $newStr = $text;
        }
        
        return $newStr;
    }
}
