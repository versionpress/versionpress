<?php

namespace VersionPress\Tests\End2End\Media;

use PHPUnit_Extensions_Selenium2TestCase_Keys;
use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class MediaTestSeleniumWorker extends SeleniumWorker implements IMediaTestWorker {

    private $filePath;

    public function setUploadedFilePath($filePath) {
        $this->filePath = $filePath;
    }

    public function prepare_uploadFile() {
        $this->loginIfNecessary();
    }

    public function uploadFile() {
        // Couldn't find out how to automate the default (and more modern) upload.php so let's go via media-new.php
        // see http://stackoverflow.com/q/27607453/21728
        $this->url('wp-admin/media-new.php');
        if (!$this->byCssSelector('#html-upload')->displayed()) {
            $this->byCssSelector('.upload-flash-bypass a')->click();
        }
        $this->byCssSelector('#async-upload')->value($this->filePath); // separator is actually OS-specific here
        $this->byCssSelector('#html-upload')->click();

        $this->waitForElement('.thumbnail', 3000);}

    public function prepare_editFileName() {
    }

    public function editFileName() {
        $this->byCssSelector('.attachment:first-child .thumbnail')->click(); // click must be on the .thumbnail element
        $this->waitForElement('.edit-attachment-frame', 300);
        $this->setValue('.setting[data-setting=title] input', 'updated image title');
        $this->byCssSelector('.setting[data-setting=caption] textarea')->click(); // focus out, AJAX saves the image
        $this->waitForAjax();
    }

    public function prepare_deleteFile() {
    }

    public function deleteFile() {
        $this->byCssSelector('.delete-attachment')->click();
        $this->acceptAlert();
        $this->waitForAjax();
    }
}