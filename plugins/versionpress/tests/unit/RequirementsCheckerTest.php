<?php

require_once(__DIR__ . '/../../src/Utils/RequirementsChecker.php');

use VersionPress\Utils\RequirementsChecker;


class RequirementsCheckerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function gitCheckFailsForGit_1_8_0() {
        $this->assertFalse(RequirementsChecker::gitMatchesMinimumRequiredVersion("1.8.0", "1.9"));
    }

    /**
     * @test
     */
    public function gitCheckPassesForGit_1_9_0() {
        $this->assertTrue(RequirementsChecker::gitMatchesMinimumRequiredVersion("1.9.0", "1.9"));
    }

    /**
     * @test
     */
    public function gitCheckPassesForGit_2_2_0() {
        $this->assertTrue(RequirementsChecker::gitMatchesMinimumRequiredVersion("2.2.0", "1.9"));
    }

}
