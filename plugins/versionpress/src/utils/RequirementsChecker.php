<?php

class RequirementsChecker {
    private $requirements = array();

    function __construct() {
        $this->requirements[] = array(
            'name' => 'PHP 5.3',
            'fulfilled' => version_compare(PHP_VERSION, '5.3.0', '>=')
        );

        $this->requirements[] = array(
            'name' => 'Enabled proc_open',
            'fulfilled' => $this->tryRunProcess()
        );

        $this->requirements[] = array(
            'name' => 'Installed git',
            'fulfilled' => $this->tryGit()
        );

        $this->requirements[] = array(
            'name' => 'Write access to wp-content dir',
            'fulfilled' => $this->tryWrite()
        );

        $this->requirements[] = array(
            'name' => 'The db.php file is available',
            'fulfilled' => !is_file(WP_CONTENT_DIR . '/db.php')
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
    public function getReport() {
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
            return $process->getErrorOutput() === null;
        } catch (Exception $e) {
            return false;
        }
    }

    private function tryWrite() {
        $filename = ".vp-try-write";
        $filePath = WP_CONTENT_DIR . '/' . $filename;
        @file_put_contents($filePath, "");
        $fileExists = is_file($filePath);
        @unlink($filePath);
        return $fileExists;
    }
}
