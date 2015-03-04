<?php

namespace VersionPress\Tests\End2End\Media;

use VersionPress\Tests\End2End\Utils\WpCliWorker;

class MediaTestWpCliWorker extends WpCliWorker implements IMediaTestWorker {

    /** @var string */
    private $filePath;
    private $postId;

    public function setUploadedFilePath($filePath) {
        $this->filePath = $this->getRelativePath($this->testConfig->testSite->path, $filePath);
    }

    public function prepare_uploadFile() {
    }

    public function uploadFile() {
        $this->postId = trim($this->wpAutomation->importMedia($this->filePath));
    }

    public function prepare_editFileName() {
    }

    public function editFileName() {
        $this->wpAutomation->editPost($this->postId, array('post_title' => 'Some name'));
    }

    public function prepare_deleteFile() {
    }

    public function deleteFile() {
        $this->wpAutomation->deletePost($this->postId);
    }

    private function getRelativePath($from, $to) {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from     = explode('/', $from);
        $to       = explode('/', $to);
        $relPath  = $to;

        foreach($from as $depth => $dir) {
            // find first non-matching dir
            if($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}