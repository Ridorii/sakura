<?php
/**
 * Holds the everything networking.
 *
 * @package Sakura
 */

namespace Sakura;

/**
 * Networking methods.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Net
{
    /**
     * Returns the connecting IP.
     *
     * @return string The IP.
     */
    public static function IP()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '::1';
    }

    /**
     * Detect the version of an IP.
     *
     * @param string $ipAddr The IP.
     *
     * @return int Either 0, 4 or 6.
     */
    public static function detectIPVersion($ipAddr)
    {
        // Check if var is IP
        if (filter_var($ipAddr, FILTER_VALIDATE_IP)) {
            // v4
            if (filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return 4;
            }

            // v6
            if (filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return 6;
            }
        }

        // Not an IP or unknown type
        return 0;
    }

    /**
     * Converts a printable IP address into an unpacked binary string.
     *
     * @param string $ip Printable IP string.
     *
     * @throws \Exception Thrown if an invalid IP is supplied.
     *
     * @return string Unpacked IP address.
     */
    public static function pton($ip)
    {
        // Detect the IP version
        $ipv = self::detectIPVersion($ip);

        // Check for IPv4 first since that's most common
        if ($ipv === 4) {
            return current(unpack("A4", inet_pton($ip)));
        }

        // Then attempt IPv6
        if ($ipv === 6) {
            return current(unpack("A16", inet_pton($ip)));
        }

        // Throw an exception if an invalid IP was supplied
        throw new \Exception("Invalid IP address supplied.");
    }

    /**
     * Converts a binary unpacked IP to a printable packed IP.
     *
     * @param string $bin The unpacked IP.
     *
     * @throws \Exception Thrown if the unpacked IP is invalid.
     *
     * @return string The packed IP.
     */
    public static function ntop($bin)
    {
        // Get the length of the binary string
        $len = strlen($bin);

        // Throw an exception if it's not 4 or 16 bytes
        if ($len !== 4 && $len !== 16) {
            throw new \Exception("Could not handle this IP type.");
        }

        // Finally pack the IP
        return inet_ntop(pack("A{$len}", $bin));
    }

    public static function matchCIDR($ip, $range)
    {
        // Break the range up in parts
        list($net, $mask) = explode('/', $range);

        // Check IP version
        $ipv = self::detectIPVersion($ip);
        $rangev = self::detectIPVersion($net);

        // Return false if it's not a valid IP
        if ($ipv !== $rangev && !$ipv) {
            return false;
        }

        // v4
        if ($ipv === 4) {
            return self::matchCIDRv4($ip, $net, $mask);
        }

        // v6
        if ($ipv === 6) {
            return self::matchCIDRv6($ip, $net, $mask);
        }

        // Return false by default
        return false;
    }

    /**
     * Match an IPv4 CIDR
     *
     * @param string $ip The IP address to match.
     * @param string $net The Net address to match.
     * @param string $mask The Mask to match.
     *
     * @return bool Returns true if it matches.
     */
    private static function matchCIDRv4($ip, $net, $mask)
    {
        // Convert IP and Net address to long
        $ip = ip2long($ip);
        $net = ip2long($net);

        // Generate mask
        $mask = -1 << (32 - $mask);

        // Do the check
        return ($ip & $mask) === $net;
    }

    /**
     * Converts an IPv6 mask to a byte array
     *
     * @param int $mask The mask.
     *
     * @return string The byte array.
     */
    private static function maskToByteArray($mask)
    {
        // Generate an address from the mask
        $addr = str_repeat("f", $mask / 4);
        
        // Append uneven bit
        switch ($mask % 4) {
            case 1:
                $addr .= '8';
                break;

            case 2:
                $addr .= 'c';
                break;

            case 3:
                $addr .= 'e';
                break;
        }
        
        // Pad the address with zeroes
        $addr = str_pad($addr, 32, '0');
        
        // Pack the address
        $addr = pack('H*', $addr);

        // Return the packed address
        return $addr;
    }

    /**
     * Match an IPv6 CIDR
     *
     * @param string $ip The IP address to match.
     * @param string $net The Net address to match.
     * @param int $mask The net mask.
     *
     * @return bool Returns true if it's a successful match.
     */
    private static function matchCIDRv6($ip, $net, $mask)
    {
        // Pack the IP and Net addresses
        $ip = inet_pton($ip);
        $net = inet_pton($net);

        // Convert the mask to a byte array
        $mask = self::maskToByteArray($mask);

        // Compare them
        return ($ip & $mask) === $net;
    }

    /**
     * Fetch a remote file
     *
     * @param string $url The location of the file
     *
     * @return mixed The contents of the remote file
     */
    public static function fetch($url)
    {
        // Create a curl instance
        $curl = curl_init();

        // Set options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl, CURLOPT_TIMEOUT, 4);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Sakura/' . SAKURA_VERSION);

        // Execute
        $curl = curl_exec($curl);

        // Return the data
        return $curl;
    }
}
