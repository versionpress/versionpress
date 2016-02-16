<?php
namespace VersionPress\Git;

use VersionPress\Utils\PathUtils;
use VersionPress\Utils\StringUtils;


class MergeDriverConfigurer {
    public static function installGitattributes($directory) {

        $gitattributesPath = VP_PROJECT_ROOT . '/.gitattributes';
        $gitattributes = file_get_contents($directory . '/.gitattributes.tpl');

        $gitattributesVariables = array(
            'wp-content' => rtrim(ltrim(PathUtils::getRelativePath(VP_PROJECT_ROOT, WP_CONTENT_DIR), '.'), '/\\')
        );
        $gitattributes = StringUtils::fillTemplateString($gitattributesVariables,$gitattributes);

        $flag = null;
        if (is_file($gitattributesPath)) {
            $flag = FILE_APPEND;
            if (strpos(file_get_contents($gitattributesPath), "merge=vp-ini") !== false) {
                return;
            }
        }

        file_put_contents($gitattributesPath, $gitattributes, $flag);
    }

    public static function installGitMergeDriver($directory) {
        $gitconfigPath = VP_PROJECT_ROOT . '/.git/config';
        $gitconfig = file_get_contents($directory . '/gitconfig.tpl');
        $mergeDriverScript = WP_PLUGIN_DIR . "/versionpress/ini-merge.php";

        $gitconfigVariables = array(
            'wp-plugins' => rtrim(ltrim(PathUtils::getRelativePath(VP_PROJECT_ROOT, WP_PLUGIN_DIR), '.'), '/\\'),
            'merge-driver-script' => 'ini-merge.php',
            'bin-dir' => PHP_BINDIR . '/php'
        );

        $gitconfig = StringUtils::fillTemplateString($gitconfigVariables,$gitconfig);
        file_put_contents($gitconfigPath, $gitconfig, FILE_APPEND);
        chmod($mergeDriverScript, 0774);

    }
}
