<?php

namespace VersionPress\Tests\End2End;

use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\TestConfig;

class WpCliWorker implements ITestWorker {

    protected $wpAutomation;

    public function __construct(TestConfig $testConfig) {
        $this->wpAutomation = new WpAutomation($testConfig->testSite);
    }
}