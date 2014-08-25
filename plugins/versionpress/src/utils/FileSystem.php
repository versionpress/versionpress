<?php

/**
 * Helper class to work with WP Filesystem. The main method is `getWpFilesystem()`.
 */
class FileSystem {

    /**
     * Prepares $wp_filesystem and returns it. I don't really understand why it's so complicated
     * to invoke a single filesystem command using WP Filesystem API but until
     * that is cleared up, this helper method was created.
     *
     * @see http://codex.wordpress.org/Filesystem_API
     * @see http://wordpress.stackexchange.com/questions/157629/convenient-way-to-use-wp-filesystem
     *
     * @return WP_Filesystem_Direct
     */
    public static function getWpFilesystem() {
        global $wp_filesystem;

        $url = wp_nonce_url('plugins.php');
        if (false === ($creds = request_filesystem_credentials($url, '', false, false, null) ) ) {
            echo "Could not create filesystem credentials";
            return null;
        }

        if ( ! WP_Filesystem($creds) ) {
            request_filesystem_credentials($url, '', true, false, null);
            echo "Filesystem credentials were not available";
            return null;
        }

        return $wp_filesystem;
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

} 