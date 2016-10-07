<?php
/**
 * Holds file system interaction stuff.
 * @package Sakura
 */

namespace Sakura;

/**
 * Used for handling file system interactions.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class FileSystem
{
    private static $rootPath = null;

    public static function getRootPath()
    {
        if (self::$rootPath === null) {
            // assuming we're running from the 'app' subdirectory
            self::$rootPath = realpath(__DIR__ . '/..');
        }

        return self::$rootPath;
    }

    public static function getPath($path)
    {
        return self::getRootPath() . DIRECTORY_SEPARATOR . self::fixSlashes($path);
    }

    private static function fixSlashes($path)
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
