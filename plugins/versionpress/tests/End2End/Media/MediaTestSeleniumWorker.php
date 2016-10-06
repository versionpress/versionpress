<?php

namespace VersionPress\Tests\End2End\Media;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class MediaTestSeleniumWorker extends SeleniumWorker implements IMediaTestWorker
{

    private $filePath;

    public function setUploadedFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    public function prepare_uploadFile()
    {
    }

    public function uploadFile()
    {
        // Couldn't find out how to automate the default (and more modern) upload.php so let's go via media-new.php
        // see http://stackoverflow.com/q/27607453/21728
        $this->url(self::$wpAdminPath . '/media-new.php');
        if (!$this->byCssSelector('#html-upload')->displayed()) {
            $this->byCssSelector('.upload-flash-bypass a')->click();
        }
        $this->byCssSelector('#async-upload')->value($this->filePath); // separator is actually OS-specific here
        $this->byCssSelector('#html-upload')->click();

        $this->waitForElement('.thumbnail', 3000);
    }

    public function prepare_editFileName()
    {
        throw new \PHPUnit_Framework_SkippedTestError("It's impossible to submit the form from Selenium. I tried everything. Maybe some future me will be more lucky.");
    }

    public function editFileName()
    {
    }

    public function prepare_deleteFile()
    {
    }

    public function deleteFile()
    {
        $this->byCssSelector('.delete-attachment')->click();
        $this->acceptAlert();
        $this->waitForAjax();
    }

    public function prepare_editFile()
    {
        $this->uploadFile();
    }

    public function editFile()
    {
        $this->byCssSelector('.attachment:first-child .thumbnail')->click(); // click must be on the .thumbnail element
        $this->waitForElement('.edit-attachment-frame', 300);

        $this->byCssSelector('.edit-attachment')->click();
        $this->waitForElement('.imgedit-rleft', 500);
        $this->byCssSelector('.imgedit-rleft')->click();
        sleep(1); // wait for javascript in this case to rotate the image
        $this->byCssSelector(".imgedit-submit-btn:not([disabled=disabled])")->click();

        $this->waitForAjax();
    }
}
