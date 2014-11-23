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

        self::possiblyFixGitPermissions($origin);

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

        self::possiblyFixGitPermissions($path);

        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->remove($path);
    }

    public static function copyDir($origin, $target) {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->mirror($origin, $target);
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

    /**
     * If the path is a `.git` repository, it attempts to set 0777 permissions
     * on everything under it because the remove / move operation on it would otherwise fail,
     * on Windows.
     *
     * @param $path
     */
    private static function possiblyFixGitPermissions($path) {
        if (is_dir($path) && basename($path) == '.git') {

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

            foreach ($iterator as $item) {
                chmod($item, 0777);
            }

        }

    }
} 