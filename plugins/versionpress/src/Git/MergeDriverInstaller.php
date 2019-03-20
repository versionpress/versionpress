<?php
namespace VersionPress\Git;

use VersionPress\Utils\PathUtils;
use VersionPress\Utils\StringUtils;

class MergeDriverInstaller
{
    /**
     * Installs a merge driver.
     *
     * Merge driver consists of three things:
     *
     * 1. `.gitattributes` definition that tells which files to process with which driver
     * 2. Section in `git/config` that maps the logical merge driver name to a concrete script on a disk
     * 3. The actual driver - compiled for a specific platform
     *
     * It's a bit tricky because:
     *
     * - .gitattributes is committed in the repo. The paths must therefore be relative.
     * - git/config is not committed in the repo so it must be created dynamically on actions
     *   like activating VersionPress or restoring / cloning a site. Furthermore, the paths
     *   must be absolute because Git's cwd can be different.
     *
     * @param string $rootDir Where to install the driver
     * @param string $pluginDir Path to VersionPress (plugin) - used to look up templates and merge drivers
     * @param string $vpdbDir Location of the VPDB dir (where the INI files are)
     * @param string $os darwin | linux | windows
     * @param string $arch 386 | amd64
     */
    public static function installMergeDriver($rootDir, $pluginDir, $vpdbDir, $os, $arch)
    {
        self::installGitattributes($rootDir, $pluginDir, $vpdbDir);
        self::installGitConfig($rootDir, $pluginDir, $os, $arch);
    }


    /**
     * Installs .gitattributes - creates the file if it doesn't exist or inserts a section if the section
     * didn't exist already.
     *
     * @param string $rootDir
     * @param string $pluginDir
     * @param string $vpdbDir
     */
    private static function installGitattributes($rootDir, $pluginDir, $vpdbDir)
    {

        $gitattributesPath = $rootDir . '/.gitattributes';
        $gitattributesContents = file_get_contents($pluginDir . '/src/Initialization/.gitattributes.tpl');

        $gitattributesVariables = [
            'vpdb-dir' => PathUtils::getRelativePath($rootDir, $vpdbDir),
        ];
        $gitattributesContents = StringUtils::fillTemplateString($gitattributesVariables, $gitattributesContents);

        if (is_file($gitattributesPath)) {
            $gitAttributesFileContents = file_get_contents($gitattributesPath);
            if (strpos($gitAttributesFileContents, $gitattributesContents) !== false) {
                return;
            }
            $gitattributesContents = $gitattributesContents . $gitAttributesFileContents;
        }

        file_put_contents($gitattributesPath, $gitattributesContents);
    }


    /**
     * Installs 'vp-ini' merge driver section into .git/config
     *
     * @param string $rootDir
     * @param string $pluginDir
     * @param string $driver
     */
    private static function installGitConfig($rootDir, $pluginDir, $os, $arch)
    {

        $gitconfigPath = $rootDir . '/.git/config';
        if (strpos(file_get_contents($gitconfigPath), '[merge "vp-ini"]') !== false) {
            return;
        }

        $gitconfigVariables = [
            'merge-driver-script' => self::getMergeDriverBinary($pluginDir, $os, $arch),
        ];

        $gitconfigTemplate = file_get_contents($pluginDir . '/src/Initialization/gitconfig.tpl');
        $gitconfigContents = StringUtils::fillTemplateString($gitconfigVariables, $gitconfigTemplate);
        file_put_contents($gitconfigPath, $gitconfigContents, FILE_APPEND);
    }

    /**
     * Uninstalls a merge driver - removes 'vp-ini' sections from both .gitattributes
     * and .git/config.
     *
     * @param string $rootDir
     * @param string $pluginDir
     * @param string $vpdbDir
     */
    public static function uninstallMergeDriver($rootDir, $pluginDir, $vpdbDir)
    {
        $gitconfigPath = $rootDir . '/.git/config';
        $gitattributesPath = $rootDir . '/.gitattributes';

        $gitattributesContents = file_get_contents($pluginDir . '/src/Initialization/.gitattributes.tpl');

        $gitattributesVariables = [
            'vpdb-dir' => PathUtils::getRelativePath($rootDir, $vpdbDir),
        ];
        $gitattributesContents = StringUtils::fillTemplateString($gitattributesVariables, $gitattributesContents);

        if (file_exists($gitattributesPath)) {
            $gitAttributes = file_get_contents($gitattributesPath);
            $gitAttributes = str_replace($gitattributesContents, '', $gitAttributes);
            if (trim($gitAttributes) === '') {
                unlink($gitattributesPath);
            } else {
                file_put_contents($gitattributesPath, $gitAttributes);
            }
        }

        if (file_exists($gitconfigPath)) {
            $gitConfig = file_get_contents($gitconfigPath);
            // https://regex101.com/r/eJ4rJ5/4
            $mergeDriverRegex = "/(\\[merge \\\"vp\\-ini\\\"\\]\\r?\\n)([^\\[]*)/";
            $gitConfig = preg_replace($mergeDriverRegex, '', $gitConfig, 1);
            file_put_contents($gitconfigPath, $gitConfig);
        }
    }

    private static function getMergeDriverBinary($pluginDir, $os, $arch)
    {
        return str_replace('\\', '/', "${pluginDir}/src/Git/merge-drivers/merge-driver-${os}-${arch}");
    }
}
