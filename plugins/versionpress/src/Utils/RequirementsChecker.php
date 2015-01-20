<?php

namespace VersionPress\Utils;

use Exception;
use NStrings;
use Symfony\Component\Process\Process;

class RequirementsChecker {
    private $requirements = array();

    function __construct() {

        // VersionPress\Utils\Markdown can be used in the 'help' field

        $this->requirements[] = array(
            'name' => 'PHP 5.3',
            'level' => 'critical',
            'fulfilled' => version_compare(PHP_VERSION, '5.3.0', '>='),
            'help' => 'PHP 5.3 is currently required. We might support PHP 5.2 (the minimum WordPress-required PHP version) in some future update.'
        );

        $this->requirements[] = array(
            'name' => 'Execute external commands',
            'level' => 'critical',
            'fulfilled' => $this->tryRunProcess(),
            'help' => 'PHP function `proc_open()` must be enabled as VersionPress uses it to execute Git commands. Please update your php.ini.'
        );

        $this->requirements[] = array(
            'name' => 'Git 1.9+ installed',
            'level' => 'critical',
            'fulfilled' => $this->tryGit(),
            'help' => 'Git must be installed on the server. The minimal required version is 1.9. Please [download](http://git-scm.com/) and install it.'
        );

        $this->requirements[] = array(
            'name' => 'Write access on the filesystem',
            'level' => 'critical',
            'fulfilled' => $this->tryWrite(),
            'help' => 'VersionPress needs write access in the site root and all nested directories. Please update the permissions.'
        );

        $this->requirements[] = array(
            'name' => 'db.php hook',
            'level' => 'critical',
            'fulfilled' => !is_file(WP_CONTENT_DIR . '/db.php'),
            'help' => 'For VersionPress to do its magic, it needs to create a `wp-content/db.php` file and put some code there. ' .
                'However, this file is already occupied, possibly by some other plugin. Debugger plugins often do this so if you can, please disable them ' .
                'and remove the `db.php` file physically. If you can\'t, we have plans on how to deal with this and will ' .
                'ship a solution as part of some future VersionPress udpate (it is high on our priority list and should be fixed before the final 1.0 release).'
        );

        $this->requirements[] = array(
            'name' => 'Not multisite',
            'level' => 'critical',
            'fulfilled' => !is_multisite(),
            'help' => 'Currently VersionPress does not support multisites. Stay tuned!'
        );

        $this->requirements[] = array(
            'name' => 'Standard directory layout',
            'level' => 'critical',
            'fulfilled' => $this->testDirectoryLayout(),
            'help' => 'It\'s necessary to use standard WordPress directory layout with the current version of VersionPress.'
        );


        $this->requirements[] = array(
            'name' => '.gitignore',
            'level' => 'critical',
            'fulfilled' => $this->testGitignore(),
            'help' => 'It seems you have already created .gitignore file in the site root. It\'s necessary to add some rules for VersionPress. Please add those from `wp-content/plugins/versionpress/src/Initialization/.gitignore.tpl`.'
        );

        $this->requirements[] = array(
            'name' => '.htaccess or web.config support',
            'level' => 'warning',
            'fulfilled' => $this->tryAccessControlFiles(),
            'help' => 'It\'s highly recommended to secure `wp-content/plugins/log` and `wp-content/vpdb` directories from access via browser after activation!'
        );

        $this->everythingFulfilled = array_reduce($this->requirements, function ($carry, $requirement) {
            return $carry && ($requirement['fulfilled'] || $requirement['level'] === 'warning');
        }, true);
    }

    /**
     * Returns list of requirements and their fulfillment
     *
     * @return array
     */
    public function getRequirements() {
        return $this->requirements;
    }

    public function isEverythingFulfilled() {
        return $this->everythingFulfilled;
    }

    private function tryRunProcess() {
        try {
            $process = new Process("echo test");
            $process->run();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function tryGit() {
        try {
            $process = new Process("git --version");
            $process->run();
            if ($process->getErrorOutput() !== null) return false; // there is no git
            $output = trim($process->getOutput());
            $match = NStrings::match($output, "~git version (\\d[\\d\\.]+\\d).*~");
            $version = $match[1];
            return version_compare("1.9", $version, "<=");
        } catch (Exception $e) {
            return false;
        }
    }

    private function tryWrite() {
        $filename = ".vp-try-write";
        $testPaths = array(
            ABSPATH,
            WP_CONTENT_DIR,
        );

        $writable = true;

        foreach ($testPaths as $directory) {
            $filePath = $directory . '/' . $filename;
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @file_put_contents($filePath, "");
            $writable &= is_file($filePath);
            FileSystem::remove($filePath);
        }

        return $writable;
    }

    private function tryAccessControlFiles() {
        $securedUrl = site_url() . '/wp-content/plugins/versionpress/temp/security-check.txt';
        return file_get_contents($securedUrl) === false;
    }

    private function testGitignore() {
        $gitignorePath = ABSPATH . '.gitignore';
        $gitignoreExists = is_file($gitignorePath);
        if (!$gitignoreExists) {
            return true;
        }

        $gitignoreContainsVersionPressRules = NStrings::contains(file_get_contents($gitignorePath), 'plugins/versionpress');
        return $gitignoreContainsVersionPressRules;
    }

    private function testDirectoryLayout() {
        $uploadDirInfo = wp_upload_dir();

        $isStandardLayout = true;
        $isStandardLayout &= ABSPATH . 'wp-content' === WP_CONTENT_DIR;
        $isStandardLayout &= WP_CONTENT_DIR . '/plugins' === WP_PLUGIN_DIR;
        $isStandardLayout &= WP_CONTENT_DIR . '/themes' === get_theme_root();
        $isStandardLayout &= WP_CONTENT_DIR . '/uploads' === $uploadDirInfo['basedir'];

        return $isStandardLayout;
    }
}
