<?php
/*
 * Sakura PBKDF2 Password Hashing
 *
 * Based on Password Hashing With PBKDF2 (https://defuse.ca/php-pbkdf2.htm).
 * Copyright (c) 2013, Taylor Hornby
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Sakura;

class Hashing
{
    // These variables can be changed without break the existing hashes
    private static $_PBKDF2_HASH_ALGORITHM = 'sha256';
    private static $_PBKDF2_ITERATIONS = 1000;
    private static $_PBKDF2_SALT_BYTES = 24;
    private static $_PBKDF2_HASH_BYTES = 24;

    // Changing these will break them though
    private static $_HASH_ALGORITHM_INDEX = 0;
    private static $_HASH_ITERATION_INDEX = 1;
    private static $_HASH_SALT_INDEX = 2;
    private static $_HASH_PBKDF2_INDEX = 3;
    private static $_HASH_SECTIONS = 4;

    // Returns an array formatted like: [algorithm, iterations, salt, hash]
    public static function create_hash($pass)
    {

        $salt = base64_encode(
            \mcrypt_create_iv(
                self::$_PBKDF2_SALT_BYTES,
                MCRYPT_DEV_URANDOM
            )
        );

        $hash = base64_encode(
            self::pbkdf2(
                self::$_PBKDF2_HASH_ALGORITHM,
                $pass,
                $salt,
                self::$_PBKDF2_ITERATIONS,
                self::$_PBKDF2_HASH_BYTES,
                true
            )
        );

        $passwordData = array(
            self::$_PBKDF2_HASH_ALGORITHM,
            self::$_PBKDF2_ITERATIONS,
            $salt,
            $hash,
        );

        return $passwordData;

    }

    // Validates hashed password
    public static function validate_password($password, $params)
    {

        if (count($params) < self::$_HASH_SECTIONS) {
            return false;
        }

        $pbkdf2 = base64_decode($params[self::$_HASH_PBKDF2_INDEX]);

        $validate = self::slow_equals(
            $pbkdf2,
            $dick = self::pbkdf2(
                $params[self::$_HASH_ALGORITHM_INDEX],
                $password,
                $params[self::$_HASH_SALT_INDEX],
                (int) $params[self::$_HASH_ITERATION_INDEX],
                strlen($pbkdf2),
                true
            )
        );

        return $validate;

    }

    // Compares two strings $a and $b in length-constant time.
    public static function slow_equals($a, $b)
    {

        $diff = strlen($a) ^ strlen($b);

        for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }

        return $diff === 0;

    }

    /*
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     * $algorithm - The hash algorithm to use. Recommended: SHA256
     * $password - The password.
     * $salt - A salt that is unique to the password.
     * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
     * $key_length - The length of the derived key in bytes.
     * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
     * Returns: A $key_length-byte key derived from the password and salt.
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     *
     * This implementation of PBKDF2 was originally created by https://defuse.ca
     * With improvements by http://www.variations-of-shadow.com
     */

    private static function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
    {

        $algorithm = strtolower($algorithm);

        if (!in_array($algorithm, hash_algos(), true)) {
            trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);
        }

        if ($count <= 0 || $key_length <= 0) {
            trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);
        }

        if (function_exists('hash_pbkdf2')) {
            // The output length is in NIBBLES (4-bits) if $raw_output is false!
            if (!$raw_output) {
                $key_length = $key_length * 2;
            }

            return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
        }

        $hash_length = strlen(hash($algorithm, '', true));
        $block_count = ceil($key_length / $hash_length);

        $output = '';

        for ($i = 1; $i <= $block_count; $i++) {
            // $i encoded as 4 bytes, big endian.
            $last = $salt . pack('N', $i);

            // First iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);

            // Perform the other $count - 1 interations
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }

            $output .= $xorsum;

            if ($raw_output) {
                return substr($output, 0, $key_length);
            }

            return bin2hex(substr($output, 0, $key_length));
        }

    }
}
