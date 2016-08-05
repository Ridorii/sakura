<?php
/**
 * Holds the session handler.
 * @package Sakura
 */

namespace Sakura;

/**
 * User session handler.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Session
{
    /**
     * The ID of the user this session is from.
     * @var int
     */
    public $userId = 0;

    /**
     * The ID of the session.
     * @var string
     */
    public $sessionId = "";

    /**
     * Constructor.
     * @param int $userId
     * @param int $sessionId
     */
    public function __construct($userId, $sessionId = null)
    {
        // Check if a PHP session was already started and if not start one
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Set the supposed session data
        $this->userId = $userId;
        $this->sessionId = $sessionId;
    }

    /**
     * Destroy the active session.
     */
    public function destroy()
    {
        // Invalidate the session key
        DB::table('sessions')
            ->where('session_key', $this->sessionId)
            ->where('user_id', $this->userId)
            ->delete();

        // Unset userId and sessionId
        unset($this->userId);
        unset($this->sessionId);

        // Destroy the session
        session_regenerate_id(true);
        session_destroy();
    }

    /**
     * Destroy all sessions from this user.
     */
    public function destroyAll()
    {
        // Delete all database entries with this user in it
        DB::table('sessions')
            ->where('user_id', $this->userId)
            ->delete();

        // Destroy this session to finish it off
        $this->destroy();
    }

    /**
     * Create a new session.
     * @param boolean $permanent
     * @return string
     */
    public function create($permanent)
    {
        // Generate session key
        $session = hash('sha256', $this->userId . base64_encode('sakura' . mt_rand(0, 99999999)) . time());

        // Insert the session into the database
        DB::table('sessions')
            ->insert([
                'user_id' => $this->userId,
                'user_ip' => Net::pton(Net::ip()),
                'user_agent' => clean_string(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'No user agent header.'),
                'session_key' => $session,
                'session_start' => time(),
                'session_expire' => time() + 604800,
                'session_remember' => $permanent ? '1' : '0',
            ]);

        // Return the session key
        return $session;
    }

    /**
     * Validate the session.
     * 0 = false, 1 = active, 2 = permanent.
     * @return int
     */
    public function validate()
    {
        // Get session from database
        $session = DB::table('sessions')
            ->where('user_id', $this->userId)
            ->where('session_key', $this->sessionId)
            ->get();

        // Check if we actually got something in return
        if (!$session) {
            return 0;
        }

        // Check if the session expired
        if ($session[0]->session_expire < time()) {
            // ...and return false
            return 0;
        }

        /* completely removed the code for ip checking because it only worked with IPv4
        good thing is i can probably do CIDR based checking */

        // If the remember flag is set extend the session time
        if ($session[0]->session_remember) {
            DB::table('sessions')
                ->where('session_id', $session[0]->session_id)
                ->update(['session_expire' => time() + 604800]);
        }

        // Return 2 if the remember flag is set and return 1 if not
        return $session[0]->session_remember ? 2 : 1;
    }
}
