<?php
/**
 * Holds various utility functions.
 * 
 * @package Sakura
 */

namespace Sakura;

use PHPMailer;

/**
 * Meta utility functions.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Utils
{
    /**
     * Verify a ReCaptcha
     * 
     * @param string $response The user response.
     * 
     * @return array The response from the ReCaptcha API.
     */
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

    /**
     * The error handler.
     * 
     * @param int $errno The error ID.
     * @param string $errstr Quick description of the event.
     * @param string $errfile File the error occurred in.
     * @param int $errline Line the error occurred on.
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        // Remove ROOT path from the error string and file location
        $errstr = str_replace(ROOT, '', $errstr);
        $errfile = str_replace(ROOT, '', $errfile);

        // Attempt to log the error to the database
        if (DBv2::$db !== null) {
            // Encode backtrace data
            $backtrace = base64_encode(json_encode(debug_backtrace()));

            // Check if this error has already been logged in the past
            $past = DBv2::prepare('SELECT * FROM `{prefix}error_log` WHERE `error_backtrace` = :bc OR (`error_string` = :str AND `error_line` = :li)');
            $past->execute([
                'bc' => $backtrace,
                'str' => $errstr,
                'li' => $errline,
            ]);
            $past = $past->fetch();

            if ($past) {
                // If so assign the errid
                $errid = $past->error_id;
            } else {
                // Create an error ID
                $errid = substr(md5(microtime()), rand(0, 22), 10);

                // Log the error
                DBv2::prepare('INSERT INTO `{prefix}error_log` (`error_id`, `error_timestamp`, `error_revision`, `error_type`, `error_line`, `error_string`, `error_file`, `error_backtrace`) VALUES (:id, :time, :rev, :type, :line, :string, :file, :bc)')
                    ->execute([
                    'id' => $errid,
                    'time' => date("r"),
                    'rev' => SAKURA_VERSION,
                    'type' => $errno,
                    'line' => $errline,
                    'string' => $errstr,
                    'file' => $errfile,
                    'bc' => $backtrace,
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

    /**
     * Send an e-mail.
     * 
     * @param string $to Destination e-mail.
     * @param string $subject E-mail subject.
     * @param string $body Contents of the message.
     * @return bool|string Return whatever PHPMailer returns.
     */
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

        // Set body
        $mail->Body = $body;

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

    /**
     * Clean a string
     * 
     * @param string $string Dirty string.
     * @param bool $lower Make the string lowercase.
     * @param bool $noSpecial String all special characters.
     * @param bool $replaceSpecial Thing to replace special characters with.
     * 
     * @return string Clean string.
     */
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

    /**
     * Validate MX records.
     * 
     * @param string $email E-mail address.
     * 
     * @return bool Success.
     */
    public static function checkMXRecord($email)
    {
        // Get the domain from the e-mail address
        $domain = substr(strstr($email, '@'), 1);

        // Check the MX record
        $record = checkdnsrr($domain, 'MX');

        // Return the record data
        return $record;
    }

    /**
     * Get the country code of a visitor.
     * 
     * @return string 2 character country code.
     */
    public static function getCountryCode()
    {
        // Attempt to get country code using PHP's built in geo thing
        if (function_exists("geoip_country_code_by_name")) {
            $code = geoip_country_code_by_name(Net::IP());

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

    /**
     * Check the entropy of a password.
     * 
     * @param string $pw Password.
     * 
     * @return double|int Entropy.
     */
    public static function pwdEntropy($pw)
    {
        // Decode utf-8 chars
        $pw = utf8_decode($pw);

        // Count the amount of unique characters in the password string and calculate the entropy
        return count(count_chars($pw, 1)) * log(256, 2);
    }

    /**
     * Get the country name from a 2 character code.
     * 
     * @param string $code The country code.
     * 
     * @return string The country name.
     */
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

    /**
     * Get the byte symbol for a unit from bytes.
     * 
     * @param int $bytes The amount of bytes.
     * 
     * @return string The converted amount with the symbol.
     */
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

    /**
     * Get the premium tracker data.
     * 
     * @return array The premium tracker data.
     */
    public static function getPremiumTrackerData()
    {
        // Create data array
        $data = [];

        // Get database stuff
        $table = DBv2::prepare('SELECT * FROM `{prefix}premium_log` ORDER BY `transaction_id` DESC');
        $table->execute();
        $table = $table->fetchAll(\PDO::FETCH_ASSOC);

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

    /**
     * Add a new entry to the tracker.
     * 
     * @param int $id The user ID.
     * @param float $amount The amount of money.
     * @param string $comment A little information.
     */
    public static function updatePremiumTracker($id, $amount, $comment)
    {
        DBv2::prepare('INSERT INTO `{prefix}premium_log` (`user_id`, `transaction_amount`, `transaction_date`, `transaction_comment`) VALUES (:user, :amount, :date, :comment)')
            ->execute([
            'user' => $id,
            'amount' => $amount,
            'date' => time(),
            'comment' => $comment,
        ]);
    }

    /**
     * Clean up the contents of <code> tags.
     * 
     * @param string $text Dirty
     * 
     * @return string Clean
     */
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
