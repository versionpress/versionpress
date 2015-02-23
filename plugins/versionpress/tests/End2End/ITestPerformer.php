<?php

namespace VersionPress\Tests\End2End;

use VersionPress\Tests\Utils\TestConfig;

interface ITestPerformer {
    public function __construct(TestConfig $testConfig);
}