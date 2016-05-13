<?php
namespace VersionPress\Git;

use Nette\Utils\Strings;
use VersionPress\Utils\PathUtils;
use VersionPress\Utils\StringUtils;

class MergeDriverInstaller
{

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
     * @param string $driver DRIVER_BASH | DRIVER_PHP | DRIVER_AUTO (default; will use PHP driver for Windows,
     *                       Bash otherwise)
     */
    public static function installMergeDriver($rootDir, $pluginDir, $vpdbDir, $driver = self::DRIVER_AUTO)
    {
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
    private static function installGitConfig($rootDir, $pluginDir, $driver)
    {

        $gitconfigPath = $rootDir . '/.git/config';
        if (strpos(file_get_contents($gitconfigPath), '[merge "vp-ini"]') !== false) {
            return;
        }
        $gitconfigContents = file_get_contents($pluginDir . '/src/Initialization/gitconfig.tpl');

        $mergeDriverScript = '';
        if ($driver == MergeDriverInstaller::DRIVER_BASH
            || ($driver == MergeDriverInstaller::DRIVER_AUTO && DIRECTORY_SEPARATOR == '/')) {
            $mergeDriverScript = $pluginDir . '/src/Git/merge-drivers/ini-merge.sh';
            chmod($mergeDriverScript, 0750);
        }

        if ($driver == MergeDriverInstaller::DRIVER_PHP
            || ($driver == MergeDriverInstaller::DRIVER_AUTO && DIRECTORY_SEPARATOR == '\\')) {
            // Finding the PHP binary is a bit tricky because web server requests don't use the main PHP binary at all
            // (they either use `mod_php` or call `php-cgi`). PHP_BINARY is only correct when the process
            // is initiated from the command line, e.g., via WP-CLI when cloning.
            //
            // We'll only fix the path for Windows because on Linux and Mac OS, Bash driver is used most of time
            // and the tests are started from command line so PHP_BINARY is fine there too.
            $phpBinary = PHP_BINARY;
            if (DIRECTORY_SEPARATOR == '\\' && !Strings::endsWith($phpBinary, 'php.exe')) {
                $phpBinary = realpath(ini_get('extension_dir') . '/..') . '/php.exe';
            }

            $mergeDriverScript = '"' . $phpBinary . '" "' . $pluginDir . '/src/Git/merge-drivers/ini-merge.php' . '"';
        }

        $gitconfigVariables = [
            'merge-driver-script' => str_replace('\\', '/', $mergeDriverScript)
        ];

        $gitconfigContents = StringUtils::fillTemplateString($gitconfigVariables, $gitconfigContents);
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
}
