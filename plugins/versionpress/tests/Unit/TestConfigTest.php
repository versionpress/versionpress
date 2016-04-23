<?php

namespace VersionPress\Tests\Unit;

use PHPUnit_Framework_TestCase;
use VersionPress\Tests\Utils\TestConfig;

class TestConfigTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function yamlParsingWorks()
    {

        $config = new TestConfig(__DIR__ . "/../test-config.sample.yml");

        // 'selenium' section
        $this->assertEquals(
            "/Users/johndoe/Path/To/Firefox.app/Contents/MacOS/firefox",
            $config->seleniumConfig->firefoxBinary
        );
        $this->assertEquals(500, $config->seleniumConfig->postCommitWaitTime);

        // 'sites' section
        $this->assertArrayHasKey('vp01', $config->sites);
        $this->assertEquals("VP Test @ localhost", $config->sites["vp01"]->title);

    }
}
