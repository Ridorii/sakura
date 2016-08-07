<?php
/**
 * Holds the session object.
 * @package Sakura
 */

namespace Sakura;

/**
 * Session object.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Session
{
    /**
     * Session storage id.
     * @var int
     */
    public $id = 0;

    /**
     * User id.
     * @var int
     */
    public $user = 0;

    /**
     * IP address this session was started from.
     * @var string
     */
    public $ip = '';

    /**
     * Country this session was started from.
     * @var string
     */
    public $country = '';

    /**
     * User agent this session was started from.
     * @var string
     */
    public $agent = '';

    /**
     * Session secret key.
     * @var string
     */
    public $key = '';

    /**
     * Timestamp from when this session was created.
     * @var int
     */
    public $start = 0;

    /**
     * Timestamp on which this session will invalidate.
     * @var int
     */
    public $expire = 0;

    /**
     * Whether to extend the session's lifetime.
     * @var bool
     */
    public $remember = false;

    /**
     * Constructor, $id can be a number or the secret key.
     * @param mixed $id
     */
    public function __construct($id)
    {
        $data = DB::table('sessions');

        if (is_numeric($id)) {
            $data->where('session_id', $id);
        } else {
            $data->where('session_key', $id);
        }

        $data = $data->first();

        if ($data) {
            $this->id = intval($data->session_id);
            $this->user = intval($data->user_id);
            $this->ip = Net::ntop($data->user_ip);
            $this->country = $data->session_country;
            $this->agent = $data->user_agent;
            $this->key = $data->session_key;
            $this->start = intval($data->session_start);
            $this->expire = intval($data->session_expire);
            $this->remember = boolval($data->session_remember);
        }
    }

    /**
     * Create a new session
     * @param int $user
     * @param string $ip
     * @param string $country
     * @param string $agent
     * @param bool $remember
     * @param int $length
     * @return Session
     */
    public static function create($user, $ip, $country, $agent = null, $remember = false, $length = 604800)
    {
        $start = time();
        $key = bin2hex(random_bytes(64));

        $id = DB::table('sessions')
            ->insertGetId([
                'user_id' => $user,
                'user_ip' => Net::pton($ip),
                'user_agent' => $agent,
                'session_key' => $key,
                'session_start' => $start,
                'session_expire' => $start + $length,
                'session_remember' => $remember ? 1 : 0,
                'session_country' => $country,
            ]);

        return new Session($id);
    }

    /**
     * Delete this session.
     */
    public function delete()
    {
        DB::table('sessions')
            ->where('session_id', $this->id)
            ->delete();
    }

    /**
     * Validate the session.
     * @param int $user
     * @param string $ip
     * @return bool
     */
    public function validate($user, $ip = null)
    {
        // Get session from database
        $session = DB::table('sessions')
            ->where([
                'session_key' => $this->key,
                'user_id' => $user,
            ])
            ->first();

        // Check if we actually got something in return
        if (!$session) {
            return false;
        }

        // Check if the session expired
        if ($session->session_expire < time()) {
            $this->delete();
            return false;
        }

        /* completely removed the code for ip checking because it only worked with IPv4
        good thing is i can probably do CIDR based checking */

        // If the remember flag is set extend the session time
        if ($session->session_remember) {
            DB::table('sessions')
                ->where('session_id', $session->session_id)
                ->update(['session_expire' => time() + 604800]);
        }

        // Return 2 if the remember flag is set and return 1 if not
        return true;
    }

    /**
     * Get the country.
     * @param bool $long
     * @return string
     */
    public function country($long = false)
    {
        return $long ? get_country_name($this->country) : $this->country;
    }
}
