<?php
/*
 * A set of utility helper functions
 */

use Sakura\Config;
use Sakura\Net;
use Sakura\Router;
use Sakura\Template;

// Sort of alias for Config::get
function config($value)
{
    $split = explode('.', $value);
    $key = array_pop($split);
    $section = implode('.', $split);

    try {
        return Config::get($section, $key);
    } catch (Exception $e) {
        return Config::get($value);
    }
}

// Alias for Router::route
function route($name, $args = null)
{
    return Router::route($name, $args);
}

// Checking if a parameter is equal to session_id()
function session_check($param = 'session')
{
    return isset($_REQUEST[$param]) && $_REQUEST[$param] === session_id();
}

// Alias for Template::vars and Template::render
function view($name, $vars = [])
{
    Template::vars($vars);
    return Template::render($name);
}

function clean_string($string, $lower = false, $noSpecial = false, $replaceSpecial = '')
{
    // Run common sanitisation function over string
    $string = htmlentities($string, ENT_NOQUOTES | ENT_HTML401, 'utf-8');
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

function check_mx_record($email)
{
    // Get the domain from the e-mail address
    $domain = substr(strstr($email, '@'), 1);

    // Check the MX record
    $record = checkdnsrr($domain, 'MX');

    // Return the record data
    return $record;
}

function get_country_code()
{
    // Attempt to get country code using PHP's built in geo thing
    if (function_exists("geoip_country_code_by_name")) {
        try {
            $code = geoip_country_code_by_name(Net::ip());

            // Check if $code is anything
            if ($code) {
                return $code;
            }
        } catch (\Exception $e) {
        }
    }

    // Check if the required header is set and return it
    if (isset($_SERVER['HTTP_CF_IPCOUNTRY']) && strlen($_SERVER['HTTP_CF_IPCOUNTRY']) === 2) {
        return $_SERVER['HTTP_CF_IPCOUNTRY'];
    }

    // Return XX as a fallback
    return 'XX';
}

function get_country_name($code)
{
    switch (strtolower($code)) {
        case "xx":
            return "Unknown";

        case "a1":
            return "Anonymous Proxy";

        case "a2":
            return "Satellite Provider";

        default:
            return locale_get_display_region("-{$code}", 'en');
    }
}

function password_entropy($password)
{
    $password = utf8_decode($password);

    // Count the amount of unique characters in the password string and calculate the entropy
    return count(count_chars($password, 1)) * log(256, 2);
}

function byte_symbol($bytes)
{
    // Return nothing if the input was 0
    if (!$bytes) {
        return "0 B";
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

// turn this function into a wrapped class!
function send_mail($to, $subject, $body)
{
    $transport = Swift_SmtpTransport::newInstance()
        ->setHost(config('mail.smtp.server'))
        ->setPort(config('mail.smtp.port'));

    if (config('mail.smtp.secure')) {
        $transport->setEncryption(config('mail.smtp.secure'));
    }

    if (config('mail.smtp.auth')) {
        $transport
            ->setUsername(config('mail.smtp.username'))
            ->setPassword(config('mail.smtp.password'));
    }

    $mailer = Swift_Mailer::newInstance($transport);

    $message = Swift_Message::newInstance($subject)
        ->setFrom([config('mail.smtp.from') => config('mail.smtp.name')])
        ->setBcc($to)
        ->setBody($body);

    return $mailer->send($message);
}

function error_handler($errno, $errstr, $errfile, $errline)
{
    // Remove ROOT path from the error string and file location
    $errstr = str_replace(ROOT, '', $errstr);
    $errfile = str_replace(ROOT, '', $errfile);

    switch ($errno) {
        case E_ERROR:
        case E_USER_ERROR:
            $error = "<b>FATAL ERROR</b>";
            break;

        case E_WARNING:
        case E_USER_WARNING:
            $error = "<b>WARNING</b>";
            break;

        case E_NOTICE:
        case E_USER_NOTICE:
            $error = "<b>NOTICE</b>";
            break;

        default:
            $error = "<b>Unknown error type</b> [{$errno}]";
    }

    $error .= ": {$errstr} on line {$errline} in {$errfile}";

    // Truncate all previous outputs
    ob_clean();
    ob_end_clean();

    // Check for dev mode
    $detailed = config('dev.show_errors');

    // Build page (not even going to bother cleaning this one up)
    $errorPage = '<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
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

        foreach (debug_backtrace() as $num => $trace) {
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
