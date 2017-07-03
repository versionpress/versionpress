<?php

namespace VersionPress\Tests\Unit;

use PHPUnit_Framework_TestCase;
use VersionPress\Tests\Utils\TestConfig;

class TestConfigTest extends PHPUnit_Framework_TestCase
{

    /**
     * This is just a quick smoke test.
     *
     * @test
     */
    public function yamlParsingWorks()
    {
        $config = new TestConfig(__DIR__ . "/../test-config.yml");
        $this->assertEquals("latest-stable", $config->wpCliVersion);
    }
}
