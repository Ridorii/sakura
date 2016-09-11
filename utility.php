<?php
/*
 * A set of utility helper functions
 */

use Sakura\Config;
use Sakura\Net;
use Sakura\Routerv1;
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

// Alias for Routerv1::route
function route($name, $args = null, $full = false)
{
    return ($full ? full_domain() : '') . Routerv1::route($name, $args);
}

// Getting the full domain (+protocol) of the current host, only works for http
function full_domain()
{
    return 'http' . ($_SERVER['HTTPS'] ?? false ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
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
