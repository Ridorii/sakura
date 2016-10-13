<?php
/**
 * Holds the request container.
 * @package Sakura
 */

namespace Sakura\Router;

/**
 * Request container.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Request
{
    public $path;
    public $method;
    public $query;
    public $data;
    public $body;
    public $ip;
    public $accept;
    public $charset;
    public $language;
    public $host;
    public $referrer;
    public $agent;
    public $secure;
    public $username;
    public $password;

    public static function fromServer($server, $get, $post)
    {
        $instance = new static;

        $instance->path = $server['REQUEST_URI'];
        $instance->method = $server['REQUEST_METHOD'];
        $instance->query = $get ?? [];
        $instance->data = $post ?? [];
        $instance->body = file_get_contents('php://input');
        $instance->ip = $server['REMOTE_ADDR'];
        $instance->accept = self::parseAcceptHeader($server['HTTP_ACCEPT'] ?? '');
        $instance->charset = $server['HTTP_ACCEPT_CHARSET'] ?? 'utf-8';
        $instance->language = self::parseAcceptHeader($server['HTTP_ACCEPT_LANGUAGE'] ?? 'en');
        $instance->host = $server['HTTP_HOST'];
        $instance->referrer = $server['HTTP_REFERER'] ?? null;
        $instance->agent = $server['HTTP_USER_AGENT'] ?? '';
        $instance->secure = $server['HTTPS'] ?? null === '1';
        $instance->username = $server['PHP_AUTH_USER'] ?? null;
        $instance->password = $server['PHP_AUTH_PW'] ?? null;

        return $instance;
    }

    private static function parseAcceptHeader($header)
    {
        $accepted = [];
        $header = explode(',', strtolower($header));

        foreach ($header as $accepts) {
            $quality = 1;

            if (strpos($accepts, ';q=')) {
                list($accepts, $quality) = explode(';q=', $accepts);
            }

            // if the quality is 0 its not supported
            if ($quality === 0) {
                continue;
            }

            $accepted[$accepts] = $quality;
        }

        return $accepted;
    }
}
