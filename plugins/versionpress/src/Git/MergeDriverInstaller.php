<?php
namespace VersionPress\Git;

use VersionPress\Utils\PathUtils;
use VersionPress\Utils\StringUtils;


class MergeDriverInstaller {
    private static function installGitattributes($directory) {

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

    public static function installMergeDriver($directory) {
        self::installGitattributes($directory);
        self::installGitConfig($directory);
    }

    private static function installGitConfig($directory) {
        $gitconfigFilePath = VP_PROJECT_ROOT . '/.git/config';
        if (strpos(file_get_contents($gitconfigFilePath), '[merge "vp-ini"]') !== false) {
            return;
        }
        $gitconfig = file_get_contents($directory . '/gitconfig.tpl');


        if (DIRECTORY_SEPARATOR == '/') {
            $mergeDriverFile = 'ini-merge.sh';
            $mergeDriverScript = VERSIONPRESS_PLUGIN_DIR . '/src/Git/merge-drivers/' . $mergeDriverFile;
            chmod(str_replace('\\', '/', $mergeDriverScript), 0774);
        } else {
            $mergeDriverFile = 'ini-merge.php';
            $mergeDriverScript = '"' . PHP_BINARY . '" "' . VERSIONPRESS_PLUGIN_DIR . '/src/Git/merge-drivers/' . $mergeDriverFile . '"';
        }
        $gitconfigVariables = array(
            'merge-driver-script' => str_replace('\\', '/', $mergeDriverScript)
        );

        $gitconfig = StringUtils::fillTemplateString($gitconfigVariables, $gitconfig);
        file_put_contents($gitconfigFilePath, $gitconfig, FILE_APPEND);

    }

    public static function uninstallMergeDriver() {
        $gitconfigFilePath = VP_PROJECT_ROOT . '/.git/config';
        $gitattributesPath = VP_PROJECT_ROOT . '/.gitattributes';
        if (file_exists($gitattributesPath)) {
            $gitAttributes = file_get_contents($gitattributesPath);
            $gitAttributes = preg_replace('/(.*)merge=vp-ini/', '', $gitAttributes);
            file_put_contents($gitattributesPath, $gitAttributes);
        }

        $gitConfig = file_get_contents($gitconfigFilePath);
        //https://regex101.com/r/eJ4rJ5/3
        $mergeDriverRegex = "/(\\[merge \\\"vp\\-ini\\\"\\]\\n)([^\\[]*)/";
        $gitConfig = preg_replace($mergeDriverRegex, '', $gitConfig, 1);
        file_put_contents($gitconfigFilePath, $gitConfig);
    }


}
