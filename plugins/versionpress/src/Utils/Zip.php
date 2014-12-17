<?php

namespace VersionPress\Utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * Helper class for zipping bug report.
 * Inspired by http://stackoverflow.com/a/1334949/1243495
 */
class Zip {
    public static function zipDirectory($directory, $zipFile) {
        if (!extension_loaded('zip') || !file_exists($directory)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($zipFile, ZipArchive::CREATE)) {
            return false;
        }

        $directory = str_replace('\\', '/', realpath($directory));

        if (is_dir($directory) === true) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                    continue;
                }

                $file = str_replace('\\', '/', realpath($file));

                if (is_dir($file)) {
                    $zip->addEmptyDir(str_replace($directory . '/', '', $file . '/'));
                } else {
                    if (is_file($file)) {
                        $zip->addFromString(str_replace($directory . '/', '', $file), file_get_contents($file));
                    }
                }
            }
        } else {
            if (is_file($directory)) {
                $zip->addFromString(basename($directory), file_get_contents($directory));
            }
        }

        return $zip->close();
    }
}