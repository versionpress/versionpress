<?php

namespace VersionPress\Utils;
use CURLFile;

/**
 * Helper class for sending bug reports
 */
class BugReporter {

    /** @var string */
    private $endPoint;

    /**
     * @param string $endPoint
     */
    function __construct($endPoint) {
        $this->endPoint = $endPoint;
    }

    public function reportBug($email, $description) {
        $time = date('YmdHis');
        $bugReportDir = VERSIONPRESS_PLUGIN_DIR . '/bug-report-' . $time;
        $zipFile = $bugReportDir . '.zip';

        $this->prepareBugReport($bugReportDir, $zipFile);
        $statusCode = $this->sendBugReport($email, $description, $zipFile);
        $this->clean($bugReportDir, $zipFile);

        return $statusCode === 200;
    }

    private function savePhpinfo($dir, $filename = 'phpinfo.html') {
        $info = $this->getPhpinfo();
        file_put_contents($dir . '/' . $filename, $info);
    }

    private function getPhpinfo() {
        ob_start();
        phpinfo();
        $info = ob_get_contents();
        ob_end_clean();
        return $info;
    }

    private function clean($bugReportDir, $zipFile) {
        FileSystem::remove($bugReportDir);
        FileSystem::remove($zipFile);
    }

    private function sendBugReport($email, $description, $zipFile) {
        $postData = array('email' => $email, 'description' => $description, 'zip' => new CURLFile($zipFile));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endPoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $statusCode;
    }

    private function prepareBugReport($bugReportDir, $zipFile) {
        FileSystem::mkdir($bugReportDir);
        FileSystem::copyDir(VERSIONPRESS_PLUGIN_DIR . '/log', $bugReportDir . '/log');
        $this->savePhpinfo($bugReportDir);
        $this->saveWordPressSpecificInfo($bugReportDir);
        Zip::zipDirectory($bugReportDir, $zipFile);
    }

    private function saveWordPressSpecificInfo($bugReportDir) {
        $info = SystemInfo::getWordPressInfo();
        $serializedInfo = var_export($info, true);
        file_put_contents($bugReportDir . '/info.ini', $serializedInfo);
    }
}
