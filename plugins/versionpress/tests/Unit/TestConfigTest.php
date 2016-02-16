<?php

namespace VersionPress\Tests\Unit;

use PHPUnit_Framework_TestCase;
use VersionPress\Tests\Utils\TestConfig;

class TestConfigTest extends PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function neonParsingWorks() {

        $config = new TestConfig(__DIR__ . "/../test-config.sample.neon");

        // 'selenium' section
        $this->assertEquals("/Users/johndoe/Path/To/Firefox.app/Contents/MacOS/firefox", $config->seleniumConfig->firefoxBinary);
        $this->assertEquals(500, $config->seleniumConfig->postCommitWaitTime);

        // 'sites' section
        $this->assertArrayHasKey('vp01', $config->sites);
        $this->assertEquals("VP Test @ WordPress", $config->sites["vp01"]->title);
        $this->assertFalse($config->sites["vp01"]->isVagrant);

        // 'vp-config' section
        $this->assertEquals(null, $config->sites["vp01"]->vpConfig["git-binary"]);
        $this->assertEquals("/usr/bin/git", $config->sites["vagrant-php53"]->vpConfig["git-binary"]);

    }
}
