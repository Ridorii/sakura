<?php
/*
 * Whois in PHP (featuring a very original name), adjusted for Sakura
 * By Flashwave <http://flash.moe>
 * Released under the MIT-License
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Flashwave
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Sakura;

/**
 * WHOIS client.
 * 
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Whois
{
    /**
     * The index of WHOIS servers.
     * 
     * @var array
     */
    public static $servers;

    /**
     * Set the list of WHOIS servers.
     * 
     * @param string $serversFile The file containing the servers json.
     */
    public static function setServers($serversFile)
    {

        // Check if the file exists and if it does get contents.
        if (file_exists($serversFile)) {
            $servers = utf8_encode(file_get_contents($serversFile));
        } else {
            trigger_error('Failed to load whois servers file', E_USER_ERROR);
        }

        // Parse json
        if (($servers = json_decode($servers, true)) != true) {
            trigger_error('Error while parsing whois servers file JSON', E_USER_ERROR);
        }

        // Check for neccesary keys
        if (!array_key_exists('tld', $servers) || !array_key_exists('ip', $servers)) {
            trigger_error(
                'One or more of the required whois lists isn\'t set, please check your whois servers file',
                E_USER_ERROR
            );
        }

        // If everything is gucci set self::$servers
        self::$servers = $servers;
    }

    /**
     * Query a whois server.
     * 
     * @param string $address Hostname/IP address
     * 
     * @return bool|string Whois result.
     */
    public static function query($address)
    {

        // Call validate to use the right whois type
        switch (self::validateAddress($address)) {
            case 1: // validateAddress returns 1 for a domain...
                return self::lookupDomain($address);

            case 2: // ...and 2 for both IPv4 and IPv6 (should be fine)...
                return self::lookupIP($address);

            case 0: // ...and 0 in case the type is invalid in which case...
            default: // ...a false is returned by this function
                return false;
        }
    }

    /**
     * Validate an address.
     * 
     * @param string $address The address.
     * 
     * @return int The address type.
     */
    private static function validateAddress($address)
    {

        // Check if the given address is an IP address
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            return 2;
        }

        // Check if given address is a domain name
        if (preg_match("/^([-a-z0-9]{2,100})\.([a-z\.]{2,8})$/i", $address)) {
            return 1;
        }

        // If unsuccessful return 0
        return 0;
    }

    /**
     * Look up a domain.
     * 
     * @param string $address The address.
     * 
     * @return string The WHOIS result.
     */
    private static function lookupDomain($address)
    {

        // Get list of servers
        $servers = self::$servers['tld'];

        // Break domain up in parts
        $addressParts = explode(".", $address);

        // Get TLD
        $tld = strtolower(array_pop($addressParts));

        // Get proper whois server address
        if (!$server = $servers[$tld]) {
            return 'Error: No appropriate whois server found for the TLD '
            . $tld
            . ', check if the given address is correct.';
        }

        // Get results from whois server
        if (!$result = self::queryWhois($server, $address)) {
            // Return an error if there's no results were retrieved.
            return 'Error: No results retrieved from ' . $server . ' for ' . $address . '.';
        } else {
            // Assign result with heading text to return variable
            $return = $address . " domain lookup results from " . $server . ":\r\n\r\n" . $result;

            // Check if there's a secondary whois server
            while (strpos($result, "Whois Server:") !== false) {
                preg_match("/Whois Server: (.*)/", $return, $matches);

                // If there is call it...
                if (isset($matches[1])) {
                    $result = self::queryWhois(($server = $matches[1]), $address);

                    // ...and append the retrieved values to the return variable
                    $return .= "\r\n-------------\r\n\r\n"
                    . $address
                    . " domain lookup results from "
                    . $server
                    . ":\r\n"
                    . $result;
                }
            }
        }

        // If all is good return the return variable
        return $return;
    }

    /**
     * Look up an IP.
     * 
     * @param string $address The IP.
     * 
     * @return string The WHOIS result.
     */
    private static function lookupIP($address)
    {

        // Get list of servers
        $servers = self::$servers['ip'];

        // Set variable to keep results in
        $results = [];

        // Query servers
        foreach ($servers as $server) {
            // Get results
            $result = self::queryWhois($server, $address);

            // Assign result to results array if not in it yet
            if ($result && !in_array($result, $results)) {
                $results[$server] = $result;
            }
        }

        // Create variable to keep return value
        $return = "RESULTS FOUND: " . count($results);

        // Append results
        foreach ($results as $server => $result) {
            $return .= "\r\n\r\n-------------"
            . "\r\nLookup results for "
            . $address
            . " from "
            . $server
            . " server:\r\n\r\n"
            . $result;
        }

        // Return results
        return $return;
    }

    /**
     * Query a whois server.
     * 
     * @param mixed $server The WHOIS server.
     * @param mixed $address The address that should WHOIS'd.
     * @param mixed $port The WHOIS server port.
     * @param mixed $timeout The request timeout.
     * 
     * @return null|string The WHOIS result.
     */
    private static function queryWhois($server, $address, $port = 43, $timeout = 10)
    {

        // Open socket
        $query = @fsockopen(
            $server,
            $port,
            $errno,
            $errstr,
            $timeout
        ) or trigger_error(
            'Failed to open socket: '
            . $errno
            . ' - '
            . $errstr,
            E_USER_ERROR
        );

        // Send address
        fputs($query, $address . "\r\n");

        // Await output
        $out = null;
        while (!feof($query)) {
            $out .= fgets($query);
        }

        // Close socket
        fclose($query);

        // Return results
        return $out;
    }
}
