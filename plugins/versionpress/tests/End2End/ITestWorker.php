<?php

namespace VersionPress\Tests\End2End;

use VersionPress\Tests\Utils\TestConfig;

interface ITestWorker {
    public function __construct(TestConfig $testConfig);
}