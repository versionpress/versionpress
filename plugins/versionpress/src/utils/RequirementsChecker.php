<?php

class RequirementsChecker {
    private $requirements = array();

    function __construct() {

        // Markdown can be used in the 'help' field

        $this->requirements[] = array(
            'name' => 'PHP 5.3',
            'fulfilled' => version_compare(PHP_VERSION, '5.3.0', '>='),
            'help' => 'PHP 5.3 is currently required. We might support PHP 5.2 (the minimum WordPress-required PHP version) in some future update.'
        );

        $this->requirements[] = array(
            'name' => 'Execute external commands',
            'fulfilled' => $this->tryRunProcess(),
            'help' => 'PHP function `proc_open()` must be enabled as VersionPress uses it to execute Git commands. Please update your php.ini.'
        );

        $this->requirements[] = array(
            'name' => 'Git 1.9+ installed',
            'fulfilled' => $this->tryGit(),
            'help' => 'Git must be installed on the server. The minimal required version is 1.9. Please [download](http://git-scm.com/) and install it.'
        );

        $this->requirements[] = array(
            'name' => 'Write access to wp-content directory',
            'fulfilled' => $this->tryWrite(),
            'help' => 'VersionPress needs write access to the `wp-content` dir as its stores its internal database there. Please change the permissions on your file system.'
        );

        $this->requirements[] = array(
            'name' => 'db.php hook',
            'fulfilled' => !is_file(WP_CONTENT_DIR . '/db.php'),
            'help' => 'For VersionPress to do its magic, it needs to create a `wp-content/db.php` file and put some code there. ' .
                      'However, this file is already occupied, possibly by some other plugin. Debugger plugins often do this so if you can, please disable them ' .
                      'and remove the `db.php` file physically. If you can\'t, we have plans on how to deal with this and will ' .
                      'ship a solution as part of some future VersionPress udpate (it is high on our priority list and should be fixed before the final 1.0 release).'
        );

        $this->everythingFulfilled = array_reduce($this->requirements, function ($carry, $requirement) {
            return $carry && $requirement['fulfilled'];
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
            $process = new \Symfony\Component\Process\Process("echo test");
            $process->run();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function tryGit() {
        try {
            $process = new \Symfony\Component\Process\Process("git --version");
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
        $filePath = WP_CONTENT_DIR . '/' . $filename;
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @file_put_contents($filePath, "");
        $fileExists = is_file($filePath);
        FileSystem::remove($filePath);
        return $fileExists;
    }
}
