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

        switch ($protocol) {
            case 'http':
            case 'https':
                // youtube, handles .be, -nocookie.com and any possible tld and always uses -nocookie.com for the embedder
                if (preg_match("#(?:www\.)?youtu(?:be\.(?:[a-z]{2,63})|\.be|\be-nocookie\.com)$#si", $host)) {
                    if ($host === 'youtu.be') {
                        $video_id = $path;
                    } else {
                        $split = split_query_string($query);

                        if (!array_key_exists('v', $split)) {
                            break;
                        }

                        $video_id = $split['v'];
                    }

                    $info->URL = "https://www.youtube-nocookie.com/embed/{$video_id}";
                    $info->Type = LinkInfo::TYPES['EMBED'];
                    $info->Width = 320;
                    $info->Height = 240;
                    break;
                }

                $headers = get_headers($url);
                $data = curl_fetch($url);

                if (strstr($headers[0], ' 40') !== false || strstr($headers[0], ' 50') !== false) {
                    $info->Type = LinkInfo::TYPES['PLAIN'];
                    break;
                }

                if (getimagesizefromstring($data) !== false) {
                    $info->Type = LinkInfo::TYPES['IMAGE'];
                    break;
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_buffer($finfo, $data);
                finfo_close($finfo);

                if (strstr($mime, 'audio/') !== false) {
                    $info->Type = LinkInfo::TYPES['AUDIO'];

                    if (strstr($mime, 'mp') !== false) {
                        $info->ContentType = 'audio/mp3';
                    } elseif (strstr($mime, 'og') !== false) {
                        $info->ContentType = 'audio/ogg';
                    } elseif (strstr($mime, 'webm') !== false) {
                        $info->ContentType = 'audio/webm';
                    } else {
                        $info->ContentType = 'audio/wav';
                    }
                    break;
                }

                if (strstr($mime, 'video/') !== false) {
                    $info->Type = LinkInfo::TYPES['VIDEO'];

                    if (strstr($mime, 'og') !== false) {
                        $info->ContentType = 'video/ogg';
                    } elseif (strstr($mime, 'webm') !== false) {
                        $info->ContentType = 'video/webm';
                    } else {
                        // just kind of assume it's mp4
                        $info->ContentType = 'video/mp4';
                    }
                    break;
                }

                $tags = meta_tags($data);

                $info->Image = $tags['og:image'] ?? $tags['twitter:image:src'] ?? null;
                $info->Title = $tags['og:title'] ?? $tags['twitter:title'] ?? $tags['title'] ?? null;
                $info->Description = $tags['og:description'] ?? $tags['twitter:description'] ?? $tags['description'] ?? null;

                if ($info->Title === null && $info->Description === null) {
                    $info->Type = LinkInfo::TYPES['PLAIN'];
                } else {
                    $info->Type = LinkInfo::TYPES['META'];
                }
                break;

            case 'osu':
                // osu!direct
                if ($host === 'dl' || $host === 'b') {
                    $info->Type = LinkInfo::TYPES['META'];
                } else {
                    $info->Type = LinkInfo::TYPES['PLAIN'];
                }
                break;
        }

        return $info;
    }
}
