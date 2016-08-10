<?php
/**
 * Holds the url resolver.
 * @package Sakura
 */

namespace Sakura\Chat;

/**
 * Resolves URL data.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class URLResolver
{
    /**
     * Resolves a url.
     * @param string $protocol
     * @param string $slashes
     * @param string $authority
     * @param string $host
     * @param string $port
     * @param string $path
     * @param string $query
     * @param string $hash
     * @return LinkInfo
     */
    public static function resolve($protocol, $slashes, $authority, $host, $port, $path, $query, $hash)
    {
        $url = "{$protocol}:{$slashes}{$authority}{$host}{$port}{$path}{$query}{$hash}";
        $info = new LinkInfo;
        $info->URL = $info->OriginalURL = $url;
        $info->Type = LinkInfo::TYPES['PLAIN'];
        return $info;
    }
}
