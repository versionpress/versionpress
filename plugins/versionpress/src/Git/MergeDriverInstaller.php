<?php
namespace VersionPress\Git;

use VersionPress\Utils\PathUtils;
use VersionPress\Utils\StringUtils;


class MergeDriverInstaller {

    const DRIVER_BASH = 'bash';
    const DRIVER_PHP = 'php';
    const DRIVER_AUTO = 'auto';

    /**
     * Installs a merge driver.
     *
     * Merge driver consists of three things:
     *
     * 1. `.gitattributes` definition that tells which files to process with which driver
     * 2. Section in `git/config` that maps the logical merge driver name to a concrete script on a disk
     * 3. The actual script files(s) - PHP or Bash impl in our case
     *
     * It's a bit tricky because:
     *
     * - .gitattributes is committed in the repo. The paths must therefore be relative.
     * - git/config is not committed in the repo so it must be created dynamically on actions
     *   like activating VersionPress or restoring / cloning a site. Furthermore, the paths
     *   must be absolute because Git's cwd can be different.
     * - We need cross-platform scripts so we detect the OS and install the correct driver.
     *   The driver impl can be forced using the $driver parameter.
     *
     * @param string $rootDir Where to install the driver
     * @param string $pluginDir Path to VersionPress (plugin) - used to look up templates and merge drivers
     * @param string $vpdbDir Location of the VPDB dir (where the INI files are)
     * @param string $driver DRIVER_BASH | DRIVER_PHP | DRIVER_AUTO (default; will use PHP driver for Windows, Bash otherwise)
     */
    public static function installMergeDriver($rootDir, $pluginDir, $vpdbDir, $driver = self::DRIVER_AUTO) {
        self::installGitattributes($rootDir, $pluginDir, $vpdbDir);
        self::installGitConfig($rootDir, $pluginDir, $driver);
    }


    /**
     * Installs .gitattributes - creates the file if it doesn't exist or inserts a section if the section
     * didn't exist already.
     *
     * @param string $rootDir
     * @param string $pluginDir
     * @param string $vpdbDir
     */
    private static function installGitattributes($rootDir, $pluginDir, $vpdbDir) {

        $gitattributesPath = $rootDir . '/.gitattributes';
        $gitattributesContents = file_get_contents($pluginDir . '/src/Initialization/.gitattributes.tpl');

        $gitattributesVariables = array(
            'vp-mirroring-dir' => rtrim(ltrim(PathUtils::getRelativePath($rootDir, $vpdbDir), '.'), '/\\')
        );
        $gitattributesContents = "\n" . StringUtils::fillTemplateString($gitattributesVariables, $gitattributesContents);

        $appendFlag = null;
        if (is_file($gitattributesPath)) {
            $appendFlag = FILE_APPEND;
            if (strpos(file_get_contents($gitattributesPath), 'merge=vp-ini') !== false) {
                return;
            }
        }

        file_put_contents($gitattributesPath, $gitattributesContents, $appendFlag);
    }


    /**
     * Installs 'vp-ini' merge driver section into .git/config
     *
     * @param string $rootDir
     * @param string $pluginDir
     * @param string $driver
     */
    private static function installGitConfig($rootDir, $pluginDir, $driver) {

        $gitconfigPath = $rootDir . '/.git/config';
        if (strpos(file_get_contents($gitconfigPath), '[merge "vp-ini"]') !== false) {
            return;
        }
        $gitconfigContents = file_get_contents($pluginDir . '/src/Initialization/gitconfig.tpl');

        $mergeDriverScript = '';
        if ($driver == MergeDriverInstaller::DRIVER_BASH || ($driver == MergeDriverInstaller::DRIVER_AUTO && DIRECTORY_SEPARATOR == '/')) {
            $mergeDriverScript = $pluginDir . '/src/Git/merge-drivers/ini-merge.sh';
            chmod($mergeDriverScript, 0750);
        }

        if ($driver == MergeDriverInstaller::DRIVER_PHP || ($driver == MergeDriverInstaller::DRIVER_AUTO && DIRECTORY_SEPARATOR == '\\')) {
            $mergeDriverScript = '"' . PHP_BINARY . '" "' . $pluginDir . '/src/Git/merge-drivers/ini-merge.php' . '"';
        }

        $gitconfigVariables = array(
            'merge-driver-script' => str_replace('\\', '/', $mergeDriverScript)
        );

        $gitconfigContents = StringUtils::fillTemplateString($gitconfigVariables, $gitconfigContents);
        file_put_contents($gitconfigPath, $gitconfigContents, FILE_APPEND);

    }

    /**
     * Uninstalls a merge driver - removes 'vp-ini' sections from both .gitattributes
     * and .git/config.
     *
     * @param string $rootDir
     */
    public static function uninstallMergeDriver($rootDir) {
        $gitconfigPath = $rootDir . '/.git/config';
        $gitattributesPath = $rootDir . '/.gitattributes';

        if (file_exists($gitattributesPath)) {
            $gitAttributes = file_get_contents($gitattributesPath);
            $gitAttributes = preg_replace('/(.*)merge=vp-ini/', '', $gitAttributes);
            file_put_contents($gitattributesPath, $gitAttributes);
        }

        $gitConfig = file_get_contents($gitconfigPath);
        // https://regex101.com/r/eJ4rJ5/4
        $mergeDriverRegex = "/(\\[merge \\\"vp\\-ini\\\"\\]\\r?\\n)([^\\[]*)/";
        $gitConfig = preg_replace($mergeDriverRegex, '', $gitConfig, 1);
        file_put_contents($gitconfigPath, $gitConfig);
    }

}
