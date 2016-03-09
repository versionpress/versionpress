<?php

namespace VersionPress\Utils;
use FilesystemIterator;
use Nette\Utils\Strings;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Traversable;

/**
 * Helper functions to work with filesystem. Currently, the functions use either bare implementation
 * or {@link http://symfony.com/doc/master/components/filesystem/introduction.html Symfony Filesystem}.
 */
class FileSystem {

    /**
     * Renames (moves) origin to target.
     *
     * @see \Symfony\Component\Filesystem\Filesystem::rename()
     *
     * @param string $origin
     * @param string $target
     * @param bool $overwrite
     */
    public static function rename($origin, $target, $overwrite = false) {

        self::possiblyFixGitPermissions($origin);

        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->rename($origin, $target, $overwrite);
    }

    /**
     * Removes a file / directory. Works recursively.
     *
     * @see \Symfony\Component\Filesystem\Filesystem::remove()
     *
     * @param string|Traversable $path Path to a file or directory.
     */
    public static function remove($path) {

        self::possiblyFixGitPermissions($path);

        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->remove($path);
    }

    /**
     * Removes the content of a directory (not the directory itself). Works recursively.
     *
     * @param string $path Path to a directory.
     */
    public static function removeContent($path) {

        if (!is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $item) {
            if ($item->isDir() && Strings::endsWith($iterator->key(), ".git")) {
                self::possiblyFixGitPermissions($iterator->key());
            }
        }

        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->remove($iterator);
    }

    /**
     * Copies a file. Uses Symfony's copy but actually honors the third parameter.
     *
     * @param string $origin
     * @param string $target
     * @param bool $override
     */
    public static function copy($origin, $target, $override = false) {
        $fs = new \Symfony\Component\Filesystem\Filesystem();

        if (!$override && $fs->exists($target))
            return;

        $fs->copy($origin, $target, $override);
    }

    /**
     * Copies a directory. Uses Symfony's mirror() under the cover.
     *
     * @see \Symfony\Component\Filesystem\Filesystem::mirror()
     *
     * @param string $origin
     * @param string $target
     */
    public static function copyDir($origin, $target) {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->mirror($origin, $target);
    }

    /**
     * Creates a directory
     *
     * @param string $dir
     * @param int $mode
     */
    public static function mkdir($dir, $mode = 0750) {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->mkdir($dir, $mode);
    }

    /**
     * If the path is either a `.git` repository itself or a directory that contains it,
     * this method attempts to set correct permissions on the `.git` folder to avoid issues
     * on Windows.
     *
     * @param $path
     */
    private static function possiblyFixGitPermissions($path) {

        $gitDir = null;
        if (is_dir($path)) {
            if (basename($path) == '.git') {
                $gitDir = $path;
            } else if (is_dir($path . '/.git')) {
                $gitDir = $path . '/.git';
            }
        }

        if ($gitDir) {

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($gitDir));

            foreach ($iterator as $item) {
                if (is_dir($item)) {
                    chmod($item, 0750);
                } else {
                    chmod($item, 0640);
                }
            }

        }

    }

    /**
     * Compares two files and returns true if their contents is equal
     *
     * @param $file1
     * @param $file2
     * @return bool
     */
    public static function filesHaveSameContents($file1, $file2) {
        return sha1_file($file1) == sha1_file($file2);
    }
}
