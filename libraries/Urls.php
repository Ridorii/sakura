<?php
/**
 * Holds the url generation class.
 *
 * @package Sakura
 */

namespace Sakura;

/**
 * URL generator.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Urls
{
    /**
     * Format a URL.
     *
     * @param string $lid doesn't do anything
     * @param array $args [category, mode]
     * @param bool $rewrite doesn't do anything either
     * @param bool $b hackjob for the settings panel
     *
     * @return null|string url
     */
    public function format($lid, $args = [], $rewrite = null, $b = true)
    {
        if ($b) {
            $a = implode('.', $args);
            $a = str_replace("usertitle", "title", $a);
            return Router::route("settings.{$a}");
        }

        // Format urls
        $formatted = vsprintf('/settings.php?cat=%s&mode=%s', $args);

        // Return the formatted url
        return $formatted;
    }
}
