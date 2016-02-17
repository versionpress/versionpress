<?php
namespace VersionPress\Git;

use VersionPress\Utils\PathUtils;
use VersionPress\Utils\StringUtils;


class MergeDriverInstaller {
    public static function installGitattributes($directory) {

        $gitattributesPath = VP_PROJECT_ROOT . '/.gitattributes';
        $gitattributes = file_get_contents($directory . '/.gitattributes.tpl');

        $gitattributesVariables = array(
            'vp-mirroring-dir' => rtrim(ltrim(PathUtils::getRelativePath(VP_PROJECT_ROOT, VERSIONPRESS_MIRRORING_DIR), '.'), '/\\')
        );
        $gitattributes = "\n" . StringUtils::fillTemplateString($gitattributesVariables, $gitattributes);

        $flag = null;
        if (is_file($gitattributesPath)) {
            $flag = FILE_APPEND;
            if (strpos(file_get_contents($gitattributesPath), 'merge=vp-ini') !== false) {
                return;
            }
        }

        file_put_contents($gitattributesPath, $gitattributes, $flag);
    }

    public static function installGitMergeDriver($directory) {
        $gitconfigFilePath = VP_PROJECT_ROOT . '/.git/config';
        if (strpos(file_get_contents($gitconfigFilePath), '[merge "vp-ini"]') !== false) {
            return;
        }
        $gitconfig = file_get_contents($directory . '/gitconfig.tpl');
        $mergeDriverScript = VERSIONPRESS_PLUGIN_DIR . '/src/Git/MergeDrivers/ini-merge.php';

        $gitconfigVariables = array(
            'merge-driver-script' => $mergeDriverScript,
            'php-binary-path' => PHP_BINDIR . '/php'
        );

        $gitconfig = StringUtils::fillTemplateString($gitconfigVariables, $gitconfig);
        file_put_contents($gitconfigFilePath, $gitconfig, FILE_APPEND);
        chmod($mergeDriverScript, 0774);

    }


}
