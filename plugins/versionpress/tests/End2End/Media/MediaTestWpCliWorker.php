<?php

namespace VersionPress\Tests\End2End\Media;

use VersionPress\Tests\End2End\Utils\WpCliWorker;

class MediaTestWpCliWorker extends WpCliWorker implements IMediaTestWorker
{

    /** @var string */
    private $filePath;
    private $postId;

    public function setUploadedFilePath($filePath)
    {
        $this->filePath = $this->getRelativePath($filePath);
    }

    public function prepare_uploadFile()
    {
    }

    public function uploadFile()
    {
        $this->postId = trim($this->wpAutomation->importMedia($this->filePath));
    }

    public function prepare_editFileName()
    {
    }

    public function editFileName()
    {
        $this->wpAutomation->editPost($this->postId, ['post_title' => 'Some name']);
    }

    public function prepare_deleteFile()
    {
    }

    public function deleteFile()
    {
        $this->wpAutomation->deletePost($this->postId);
    }

    public function prepare_editFile()
    {
        throw new \PHPUnit_Framework_SkippedTestError("File cannot be changed using WP-CLI.");
    }

    public function editFile()
    {
    }
}
