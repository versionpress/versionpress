<?php

namespace VersionPress\Tests\End2End\Media;

use VersionPress\Tests\End2End\Utils\ITestWorker;

interface IMediaTestWorker extends ITestWorker {

    public function setUploadedFilePath($filePath);

    public function prepare_uploadFile();
    public function uploadFile();

    public function prepare_editFileName();
    public function editFileName();

    public function prepare_deleteFile();
    public function deleteFile();
}