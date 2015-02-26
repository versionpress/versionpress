<?php

namespace VersionPress\Tests\End2End\Utils;

use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\TestConfig;

class WpCliWorker implements ITestWorker {

    protected $wpAutomation;

    public function __construct(TestConfig $testConfig) {
        $this->wpAutomation = new WpAutomation($testConfig->testSite);
    }
}