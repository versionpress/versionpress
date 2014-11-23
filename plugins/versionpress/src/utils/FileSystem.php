<?php

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
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->rename($origin, $target, $overwrite);
    }

    /**
     * Removes a file / directory. Works recursively.
     *
     * @see \Symfony\Component\Filesystem\Filesystem::remove()
     *
     * @param string $path Path to a file or directory.
     */
    public static function remove($path) {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->remove($path);
    }

    /**
     * Sets full privileges on the .git directory and everything under it. Strangely, on Windows,
     * the .git/objects folder cannot be removed before this method runs.
     *
     * @param string $basePath Path where the .git directory is located
     */
    public static function setPermisionsForGitDirectory($basePath) {
        $gitDirectoryPath = $basePath . '/.git';

        if (!is_dir($gitDirectoryPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($gitDirectoryPath));

        foreach ($iterator as $item) {
            chmod($item, 0777);
        }
    }

    public static function copyRecursive($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::copyRecursive($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public static function deleteRecursive($dirPath) {
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }
        rmdir($dirPath);
    }
} 