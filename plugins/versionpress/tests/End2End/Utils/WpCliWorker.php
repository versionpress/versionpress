<?php

namespace VersionPress\Tests\End2End\Utils;

use VersionPress\Tests\Automation\WpAutomation;
use VersionPress\Tests\Utils\TestConfig;

class WpCliWorker implements ITestWorker {

    protected $wpAutomation;
    /** @var TestConfig */
    protected $testConfig;

    public function __construct(TestConfig $testConfig) {
        $this->wpAutomation = new WpAutomation($testConfig->testSite, $testConfig->wpCliVersion);
        $this->testConfig = $testConfig;
    }

    /**
     * Returns relative path of given path to the WP site.
     *
     * @param $absolutePath
     * @return string
     */
    protected function getRelativePath($absolutePath) {
        return PathUtils::getRelativePath($this->testConfig->testSite->path, $absolutePath);
    }
}