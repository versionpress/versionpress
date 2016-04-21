<?php

namespace VersionPress\Tests\End2End\Utils;

use VersionPress\Tests\Utils\TestConfig;

interface ITestWorker
{
    public function __construct(TestConfig $testConfig);
}
