<?php

namespace VersionPress\Utils;

class PathUtils
{
    /**
     * Calculates relative path from two absolute paths.
     *
     * @param string $from It has to be a directory!
     * @param string $to Directory or file the relative path will lead to.
     * @return string
     */
    public static function getRelativePath($from, $to)
    {
        // Windows FTW!
        $from = self::windowsFix($from);
        $to = self::windowsFix($to);


        $from = preg_replace('~([^/]*)/+([^/]*)~', '$1/$2', $from);
        $to = preg_replace('~([^/]*)/+([^/]*)~', '$1/$2', $to);

        $from = rtrim($from, '/');
        $to = rtrim($to, '/');

        $from = explode('/', $from);
        $to = explode('/', $to);

        $from = self::realpath($from);
        $to = self::realpath($to);

        $depthOfCommonPath = self::countCommonDepth($from, $to);
        $relPath = array_slice($to, $depthOfCommonPath);

        // get number of remaining dirs up to $from
        $remaining = count($from) - $depthOfCommonPath;

        // add .. up to first matching dir
        $totalLengthOfRelativePath = count($relPath) + $remaining;
        $relPath = array_pad($relPath, $totalLengthOfRelativePath * -1, '..');

        return implode('/', $relPath);
    }

    /**
     * Converts '\' to '/' and makes disk drive (e.g., C:) case insensitive
     *
     * @param string $path
     * @return string
     */
    private static function windowsFix($path)
    {
        $path = str_replace('\\', '/', $path);
        // https://regex101.com/r/RRtZKq/2
        if (preg_match('/^(\w)(\:.*)/', $path, $matches)) {
            $path = strtolower($matches[1]) . $matches[2];
        }
        return $path;
    }

    private static function countCommonDepth($from, $to)
    {
        $depth = 0;

        while (isset($from[$depth], $to[$depth]) && $from[$depth] === $to[$depth]) {
            $depth += 1;
        }

        return $depth;
    }

    /**
     * Removes '.' and '..' fragments from path.
     *
     * @param array $pathFragments
     * @return array
     */
    private static function realpath(array $pathFragments)
    {
        $realpath = [];
        foreach ($pathFragments as $fragment) {
            if ($fragment === '.') {
                continue;
            }

            if ($fragment === '..') {
                array_pop($realpath);
                continue;
            }

            $realpath[] = $fragment;
        }

        return $realpath;
    }
}
